<?php
include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stats = [];

$result = $conn->query("SELECT COUNT(*) AS count FROM parking_slots WHERE status = 'available'");
$stats['available'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) AS count FROM parking_slots WHERE status = 'occupied'");
$stats['occupied'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) AS count FROM parking_slots WHERE status = 'reserved'");
$stats['reserved'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) AS count FROM parking_slots WHERE status = 'maintenance'");
$stats['maintenance'] = $result->fetch_assoc()['count'];

$conn->close();

echo json_encode($stats);
?>