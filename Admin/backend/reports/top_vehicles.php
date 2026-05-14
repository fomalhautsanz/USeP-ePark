<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// ── Input validation ──────────────────────────────────────────────────────

// Filter by log_status — map frontend labels to DB values
$hasFrom = isset($_GET['from']) && $_GET['from'] !== '';
$hasTo   = isset($_GET['to'])   && $_GET['to']   !== '';
$limit   = intval($_GET['limit'] ?? 5);

// Validate limit
if ($limit < 1 || $limit > 50) {
    $limit = 5;
}

function isValidDate(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// ============================================================================
// Case 1: No date range — use current month view
// ============================================================================

if (!$hasFrom && !$hasTo) {
    $result = $conn->query("SELECT * FROM view_current_month_top_vehicles");
    
    if (!$result) {
        error_log("Query failed: " . $conn->error);
        echo json_encode(['error' => 'System error']);
        exit;
    }
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'plate_number'   => $row['plate_number'],
            'owner'          => $row['owner'],
            'vehicle_type'   => $row['vehicle_type'],
            'total_entries'  => (int)$row['total_entries'],
            'total_hours'    => (float)$row['total_hours'],
            'total_fee'      => (float)$row['total_fee'],
            'avg_duration'   => (float)$row['avg_duration'],
        ];
    }
    
    $conn->close();
    echo json_encode($data);
    exit;
}

// ============================================================================
// Case 2: Date range provided — validate and use query
// ============================================================================

if ($hasFrom && $hasTo) {
    $dateFrom = trim($_GET['from']);
    $dateTo   = trim($_GET['to']);
    
    if (!isValidDate($dateFrom) || !isValidDate($dateTo)) {
        echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD']);
        exit;
    }
    
    if ($dateFrom > $dateTo) {
        echo json_encode(['error' => 'Date "from" must be before or equal to "to"']);
        exit;
    }
    
    $stmt = $conn->prepare("
        SELECT
            v.plate_number,
            CONCAT(u.firstname, ' ', u.lastname)  AS owner,
            v.vehicle_type,
            COUNT(eel.log_id)                     AS total_entries,
            COALESCE(SUM(eel.total_duration), 0)  AS total_hours,
            COALESCE(SUM(eel.parking_fee), 0)     AS total_fee,
            COALESCE(AVG(eel.total_duration), 0)  AS avg_duration
        FROM entry_exit_logs eel
        JOIN vehicle v ON eel.vehicle_id = v.vehicle_id
        JOIN users u   ON v.user_id      = u.user_id
        WHERE eel.log_status = 'out'
          AND DATE(eel.time_in) BETWEEN ? AND ?
        GROUP BY v.vehicle_id, v.plate_number, u.firstname, u.lastname, v.vehicle_type
        ORDER BY total_entries DESC
        LIMIT ?
    ");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['error' => 'System error']);
        exit;
    }
    
    $stmt->bind_param("ssi", $dateFrom, $dateTo, $limit);
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(['error' => 'System error']);
        exit;
    }
    
    $result = $stmt->get_result();
    $data   = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'plate_number'   => $row['plate_number'],
            'owner'          => $row['owner'],
            'vehicle_type'   => $row['vehicle_type'],
            'total_entries'  => (int)$row['total_entries'],
            'total_hours'    => (float)$row['total_hours'],
            'total_fee'      => (float)$row['total_fee'],
            'avg_duration'   => (float)$row['avg_duration'],
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode($data);
    exit;
}

// ============================================================================
// Error: Partial date range (only from or only to)
// ============================================================================

echo json_encode(['error' => 'Must provide both "from" and "to" parameters, or neither for current month']);
$conn->close();
?>