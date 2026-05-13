<?php
include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$result = $conn->query("
    SELECT
        SUM(available_slots) AS available,
        SUM(occupied_slots)  AS occupied,
        SUM(reserved_slots)  AS reserved,
        SUM(total_slots)     AS total
    FROM view_slot_availability
");

if (!$result) {
    error_log("Query failed: " . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

$stats = $result->fetch_assoc();

$stats = array_map('intval', $stats);

$conn->close();

echo json_encode($stats);
?>