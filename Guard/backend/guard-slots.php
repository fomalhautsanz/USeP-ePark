<?php
// Guard/backend/guard-slots.php
// Returns slot counts for the home screen availability widget.
// Uses the existing view_slot_availability (SELECT via view)
// instead of raw COUNT(*) queries on parking_slots.

include '../config/database.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Aggregate across all areas from the view
$result = $conn->query("
    SELECT
        COALESCE(SUM(available_slots), 0) AS available,
        COALESCE(SUM(occupied_slots),  0) AS occupied,
        COALESCE(SUM(reserved_slots),  0) AS reserved,
        COALESCE(SUM(total_slots),     0) AS total
    FROM view_slot_availability
");

if (!$result) {
    error_log('[guard-slots] query failed: ' . $conn->error);
    echo json_encode(['error' => 'System error']);
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
$conn->close();

echo json_encode([
    'available' => (int)$row['available'],
    'occupied'  => (int)$row['occupied'],
    'reserved'  => (int)$row['reserved'],
    'total'     => (int)$row['total'],
]);
