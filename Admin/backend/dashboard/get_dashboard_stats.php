<?php
include '../../config/database.php';
header('Content-Type: application/json');

// Slot stats from view_all_slots_status
$slotResult = $conn->query("
    SELECT 
        COUNT(*) AS total,
        SUM(status = 'available') AS available,
        SUM(status = 'occupied')  AS occupied,
        SUM(status = 'reserved')  AS reserved,
        SUM(status = 'maintenance') AS maintenance
    FROM parking_slots
");
$slots = $slotResult->fetch_assoc();

// Today's entries from view_logs_today_stats
$logsResult = $conn->query("SELECT * FROM view_logs_today_stats");
$logs = $logsResult->fetch_assoc();

// Occupancy percentage
$occupancy = $slots['total'] > 0
    ? round(($slots['occupied'] / $slots['total']) * 100, 1)
    : 0;

echo json_encode([
    'total_slots'    => (int) $slots['total'],
    'available'      => (int) $slots['available'],
    'occupied'       => (int) $slots['occupied'],
    'reserved'       => (int) $slots['reserved'],
    'maintenance'   => (int) $slots['maintenance'],
    'entries_today'  => (int) $logs['today_entries'],
    'revenue_today'  => (float) $logs['today_revenue'],
    'occupancy_pct'  => $occupancy,
]);

$conn->close();
?>