<?php


ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

include '../../config/database.php';

// ----------------------------------------------------------------------------
// Helpers
// ----------------------------------------------------------------------------

function json_fail(string $message, string $internal = ''): never
{
    if ($internal) error_log('[get_reservations] ' . $internal);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// ----------------------------------------------------------------------------
// 0. DB check
// ----------------------------------------------------------------------------

if (!$conn) {
    json_fail('Service unavailable.', 'DB connection failed');
}

// ----------------------------------------------------------------------------
// 1. Collect & validate query parameters
// ----------------------------------------------------------------------------

$allowed_statuses = ['active', 'completed', 'cancelled', 'expired'];

$filter_status = trim($_GET['status'] ?? '');
$filter_date   = trim($_GET['date']   ?? '');
$search        = trim($_GET['search'] ?? '');

// Validate status against allowlist
if ($filter_status !== '' && !in_array($filter_status, $allowed_statuses, true)) {
    json_fail('Invalid status filter.');
}

// Validate date format
if ($filter_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date)) {
    json_fail('Invalid date format. Use YYYY-MM-DD.');
}

// Sanitize search — limit length, strip tags
$search = mb_substr(strip_tags($search), 0, 100);

// ----------------------------------------------------------------------------
// 2. Fetch stat cards from view_reservation_stats_today
// ----------------------------------------------------------------------------

$stat_result = $conn->query("SELECT * FROM view_reservation_stats_today LIMIT 1");

if (!$stat_result) {
    json_fail('Could not load stats.', 'stats query failed: ' . $conn->error);
}

$stats = $stat_result->fetch_assoc() ?? [
    'total_today' => 0,
    'pending'     => 0,
    'approved'    => 0,
    'cancelled'   => 0,
];

// ----------------------------------------------------------------------------
// 3. Build reservations query from view_reservations_full
// ----------------------------------------------------------------------------
// We read only from the view — never from raw tables.
// Dynamic WHERE clauses are built with prepared-statement placeholders.
// ----------------------------------------------------------------------------

$where  = [];
$params = [];
$types  = '';

if ($filter_status !== '') {
    $where[]  = 'status = ?';
    $params[] = $filter_status;
    $types   .= 's';
}

if ($filter_date !== '') {
    $where[]  = 'DATE(time_reserved) = ?';
    $params[] = $filter_date;
    $types   .= 's';
}

if ($search !== '') {
    $like     = '%' . $search . '%';
    $where[]  = '(full_name LIKE ? OR plate_number LIKE ? OR slot_number LIKE ? OR ref_number LIKE ?)';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ssss';
}

$sql = 'SELECT * FROM view_reservations_full';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$stmt = $conn->prepare($sql);

if (!$stmt) {
    json_fail('Could not load reservations.', 'prepare failed: ' . $conn->error);
}

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result       = $stmt->get_result();
$reservations = [];

while ($row = $result->fetch_assoc()) {
    $reservations[] = [
        'reservation_id'   => (int)$row['reservation_id'],
        'ref_number'       => $row['ref_number'],
        'user_id'          => (int)$row['user_id'],
        'user_code'        => $row['user_code'],
        'full_name'        => $row['full_name'],
        'user_role'        => $row['user_role'],
        'profile_picture'  => $row['profile_picture'],
        'plate_number'     => $row['plate_number'],
        'vehicle_type'     => $row['vehicle_type'],
        'slot_number'      => $row['slot_number'],
        'location_area'    => $row['location_area'],
        'time_reserved'    => $row['time_reserved'],
        'reservation_expiry' => $row['reservation_expiry'],
        'duration_minutes' => (int)$row['duration_minutes'],
        'minutes_until_expiry' => (int)$row['minutes_until_expiry'],
        'status'           => $row['status'],
        'date_label'       => $row['date_label'],
        'time_label'       => $row['time_label'],
    ];
}

$stmt->close();
$conn->close();

// ----------------------------------------------------------------------------
// 4. Return JSON
// ----------------------------------------------------------------------------

echo json_encode([
    'success'      => true,
    'stats'        => [
        'total_today' => (int)$stats['total_today'],
        'pending'     => (int)$stats['pending'],
        'approved'    => (int)$stats['approved'],
        'cancelled'   => (int)$stats['cancelled'],
    ],
    'reservations' => $reservations,
    'count'        => count($reservations),
]);
