<?php
include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// ── Input validation ─────────────────────────────────────────────────────────

// Filter by log_status — valid DB values: 'in', 'out', 'denied'
$allowedStatuses = ['in', 'out', 'denied'];
$statusFilter    = $_GET['status'] ?? '';
if ($statusFilter && !in_array($statusFilter, $allowedStatuses, true)) {
    echo json_encode(['error' => 'Invalid status filter']);
    exit;
}

// Date filter — must be valid Y-m-d
$dateFilter = $_GET['date'] ?? '';
if ($dateFilter) {
    $d = DateTime::createFromFormat('Y-m-d', $dateFilter);
    if (!$d || $d->format('Y-m-d') !== $dateFilter) {
        echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD']);
        exit;
    }
}

// Search — sanitize to prevent accidental injection (view uses LIKE)
$search = trim($_GET['search'] ?? '');

// Pagination
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

// ── Build WHERE clause dynamically ───────────────────────────────────────────
$conditions = [];
$params     = [];
$types      = '';

if ($statusFilter) {
    $conditions[] = 'log_status = ?';
    $params[]     = $statusFilter;
    $types       .= 's';
}

if ($dateFilter) {
    $conditions[] = 'DATE(time_in) = ?';
    $params[]     = $dateFilter;
    $types       .= 's';
}

if ($search) {
    $conditions[] = '(plate_number LIKE ? OR owner_name LIKE ? OR slot_number LIKE ? OR user_code LIKE ?)';
    $wildcard     = '%' . $search . '%';
    $params[]     = $wildcard;
    $params[]     = $wildcard;
    $params[]     = $wildcard;
    $params[]     = $wildcard;
    $types       .= 'ssss';
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// ── Count total rows for pagination ──────────────────────────────────────────
$countSql  = "SELECT COUNT(*) AS total FROM view_logs_list $where";
$countStmt = $conn->prepare($countSql);

if (!$countStmt) {
    error_log("Count prepare failed: " . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

if ($types) {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();
$countStmt->bind_result($totalRows);
$countStmt->fetch();
$countStmt->close();

$totalPages = (int) ceil($totalRows / $perPage);

// ── Fetch paginated rows ──────────────────────────────────────────────────────
// Only select columns that actually exist in view_logs_list
$dataSql  = "
    SELECT
        log_id,
        log_status,
        plate_number,
        vehicle_type,
        owner_name,
        user_code,
        slot_number,
        location_area,
        time_in,
        time_out,
        total_duration,
        parking_fee,
        payment_id,
        payment_amount,
        payment_date,
        receipt_number,
        reservation_id
    FROM view_logs_list
    $where
    ORDER BY time_in DESC
    LIMIT ? OFFSET ?
";

$dataParams = $params;
$dataTypes  = $types . 'ii';
$dataParams[] = $perPage;
$dataParams[] = $offset;

$dataStmt = $conn->prepare($dataSql);

if (!$dataStmt) {
    error_log("Data prepare failed: " . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

$dataStmt->bind_param($dataTypes, ...$dataParams);

if (!$dataStmt->execute()) {
    error_log("Data execute failed: " . $dataStmt->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

$result = $dataStmt->get_result();
$logs   = [];

while ($row = $result->fetch_assoc()) {
    // Cast numeric types so JSON returns numbers not strings
    $row['log_id']         = (int)   $row['log_id'];
    $row['total_duration'] = $row['total_duration'] !== null ? (float) $row['total_duration'] : null;
    $row['parking_fee']    = $row['parking_fee']    !== null ? (float) $row['parking_fee']    : null;
    $row['payment_amount'] = $row['payment_amount'] !== null ? (float) $row['payment_amount'] : null;
    $logs[] = $row;
}

$dataStmt->close();
$conn->close();

echo json_encode([
    'data'        => $logs,
    'total'       => (int) $totalRows,
    'page'        => $page,
    'per_page'    => $perPage,
    'total_pages' => $totalPages,
]);
?>