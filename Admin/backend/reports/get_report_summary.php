<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get current month label
$month_label = date('M Y');

// Query this month's data from entry_exit_logs
$stmt = $conn->prepare("
    SELECT
        COUNT(*)                                           AS total_entries,
        COUNT(DISTINCT vehicle_id)                        AS unique_vehicles,
        COALESCE(SUM(parking_fee), 0)                     AS revenue,
        COALESCE(AVG(total_duration), 0)                  AS avg_duration,
        COUNT(CASE WHEN log_status = 'occupied' THEN 1 END) AS currently_occupied
    FROM entry_exit_logs
    WHERE YEAR(time_in)  = YEAR(CURDATE())
      AND MONTH(time_in) = MONTH(CURDATE())
      AND log_status IN ('in', 'out')
");

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

$result = $stmt->get_result();
$row    = $result->fetch_assoc();
$stmt->close();

// Get total slots for occupancy calculation
$slotResult = $conn->query("SELECT COUNT(*) AS total FROM parking_slots");
$slotRow    = $slotResult->fetch_assoc();
$totalSlots = (int)$slotRow['total'];

// Calculate occupancy percentage
$occupancy = $totalSlots > 0 
    ? round(((int)$row['currently_occupied'] / $totalSlots) * 100, 1)
    : 0;

$conn->close();

echo json_encode([
    'month_label'   => $month_label,
    'entries'       => (int)$row['total_entries'],
    'revenue'       => (float)$row['revenue'],
    'occupancy'     => $occupancy,
    'avg_duration'  => (float)$row['avg_duration'],
]);
?>