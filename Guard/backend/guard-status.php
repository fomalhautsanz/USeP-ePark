<?php
// Guard/backend/guard-status.php
// Returns vehicles currently parked (open entry_exit_logs rows)
// Matches the same join pattern as Admin/backend/vehicles/get_vehicles.php

include '../config/database.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("
    SELECT
        v.plate_number,
        v.vehicle_type,
        CONCAT(u.firstname, ' ', u.lastname)   AS owner_name,
        e.time_in,
        DATE_FORMAT(e.time_in, '%h:%i %p')     AS entry_time_formatted,
        s.slot_number
    FROM entry_exit_logs e
    JOIN vehicle v       ON v.vehicle_id = e.vehicle_id
    JOIN users u         ON u.user_id    = v.user_id
    LEFT JOIN parking_slots s ON s.slot_id = e.slot_id
    WHERE e.time_out IS NULL
    ORDER BY e.time_in DESC
");

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
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
