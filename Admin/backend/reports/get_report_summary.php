<?php
include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Use view for current month summary
$result = $conn->query("SELECT * FROM view_current_month_summary");
if (!$result) {
    error_log("Query failed (summary): " . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}
$row         = $result->fetch_assoc();
$totalEntries = (int)   $row['total_entries'];
$revenue      = (float) $row['revenue'];
$avgDuration  = (float) $row['avg_duration'];

// Occupancy from existing view
$result = $conn->query("
    SELECT
        COALESCE(SUM(occupied_slots), 0) AS occupied,
        COALESCE(SUM(total_slots),    0) AS total
    FROM view_slot_availability
");
if (!$result) {
    error_log("Query failed (occupancy): " . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}
$occ       = $result->fetch_assoc();
$occupancy = $occ['total'] > 0
    ? round(($occ['occupied'] / $occ['total']) * 100)
    : 0;

$conn->close();

echo json_encode([
    'entries'      => $totalEntries,
    'revenue'      => $revenue,
    'avg_duration' => $avgDuration,
    'occupancy'    => $occupancy,
    'month_label'  => date('F Y'),
]);
?>