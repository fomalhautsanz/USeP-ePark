<?php
// Guard/backend/guard-slots.php
// Returns slot counts — matches get_slot_stats.php logic exactly

include '../config/database.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$r        = $conn->query("SELECT COUNT(*) AS count FROM parking_slots WHERE status = 'available'");
$available = (int)$r->fetch_assoc()['count'];

$r        = $conn->query("SELECT COUNT(*) AS count FROM parking_slots WHERE status = 'occupied'");
$occupied = (int)$r->fetch_assoc()['count'];

$r     = $conn->query("SELECT COUNT(*) AS count FROM parking_slots");
$total = (int)$r->fetch_assoc()['count'];

$conn->close();

echo json_encode([
    'available' => $available,
    'occupied'  => $occupied,
    'total'     => $total,
]);
