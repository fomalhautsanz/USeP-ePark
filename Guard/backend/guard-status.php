<?php
// Guard/backend/guard-status.php
// Returns all vehicles currently parked (open entry_exit_logs rows).
// Uses view_guard_vehicles_inside (SELECT via view) instead of raw table joins.

include '../config/database.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// SELECT from view — no raw table access
$stmt = $conn->prepare("SELECT * FROM view_guard_vehicles_inside");
if (!$stmt) {
    error_log('[guard-status] prepare failed: ' . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

$stmt->execute();
$result   = $stmt->get_result();
$vehicles = [];

while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['vehicles' => $vehicles]);
