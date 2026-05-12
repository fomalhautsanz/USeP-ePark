<?php
include '../../config/database.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$result = $conn->query("SELECT * FROM view_vehicle_stats");

if (!$result) {
    error_log("Query failed: " . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

$stats = array_map('intval', $result->fetch_assoc());

$conn->close();
echo json_encode($stats);
?>