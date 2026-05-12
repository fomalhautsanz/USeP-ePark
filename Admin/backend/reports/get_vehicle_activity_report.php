<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// ============================================================================
// Validate Dates
// ============================================================================

function isValidDate(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

$dateFrom = trim($_GET['from'] ?? date('Y-m-01'));
$dateTo   = trim($_GET['to']   ?? date('Y-m-t'));

if (!isValidDate($dateFrom) || !isValidDate($dateTo)) {
    echo json_encode(['success' => false, 'error' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

if ($dateFrom > $dateTo) {
    echo json_encode(['success' => false, 'error' => 'Date "from" must be before or equal to "to"']);
    exit;
}

// ============================================================================
// Call Stored Procedure
// ============================================================================

$proc = $conn->prepare("CALL sp_get_vehicle_activity_report(?, ?)");

if (!$proc) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'System error']);
    exit;
}

$proc->bind_param("ss", $dateFrom, $dateTo);

if (!$proc->execute()) {
    error_log("Execute failed: " . $proc->error);
    echo json_encode(['success' => false, 'error' => 'System error']);
    exit;
}

$result = $proc->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        // Vehicle Info
        'vehicle_id' => (int)$row['vehicle_id'],
        'plate_number' => $row['plate_number'],
        'vehicle_type' => $row['vehicle_type'],
        
        // Owner Info
        'user_id' => (int)$row['user_id'],
        'user_code' => $row['user_code'],
        'owner_name' => $row['owner_name'],
        'email' => $row['email'],
        'contact_number' => $row['contact_number'],
        'user_role' => $row['user_role'],
        
        // Activity Metrics
        'activity' => [
            'total_entries' => (int)$row['total_entries'],
            'total_exits' => (int)$row['total_exits'],
            'currently_inside' => (int)$row['currently_inside'],
            'days_active' => (int)$row['days_active'],
        ],
        
        // Duration Statistics (in hours)
        'duration_stats' => [
            'total_hours' => (float)$row['total_hours'],
            'avg_duration_hours' => (float)$row['avg_duration_hours'],
            'min_duration_hours' => (float)$row['min_duration_hours'],
            'max_duration_hours' => (float)$row['max_duration_hours'],
        ],
        
        // Financial Statistics (in currency)
        'financial_stats' => [
            'total_fees_paid' => (float)$row['total_fees_paid'],
            'avg_fee_per_session' => (float)$row['avg_fee_per_session'],
            'min_fee_per_session' => (float)$row['min_fee_per_session'],
            'max_fee_per_session' => (float)$row['max_fee_per_session'],
        ],
        
        // Reservation Statistics
        'reservations' => [
            'total_reservations' => (int)$row['total_reservations'],
            'active_reservations' => (int)$row['active_reservations'],
            'completed_reservations' => (int)$row['completed_reservations'],
            'cancelled_reservations' => (int)$row['cancelled_reservations'],
        ],
        
        // Temporal Info
        'timeline' => [
            'first_entry' => $row['first_entry'],
            'last_entry' => $row['last_entry'],
            'last_exit' => $row['last_exit'],
        ],
    ];
}

$proc->close();
$conn->close();

// ============================================================================
// Return Response
// ============================================================================

echo json_encode([
    'success' => true,
    'report_type' => 'vehicle_activity',
    'period' => "from_$dateFrom to_$dateTo",
    'count' => count($data),
    'data' => $data
]);
?>