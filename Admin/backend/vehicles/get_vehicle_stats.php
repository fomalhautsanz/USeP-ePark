<?php
include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stats = [];

// Total registered vehicles
$result = $conn->query("SELECT COUNT(*) AS count FROM vehicle");
$stats['total'] = $result->fetch_assoc()['count'];

// Currently inside (active session)
$result = $conn->query("SELECT COUNT(*) AS count FROM entry_exit_logs WHERE time_out IS NULL");
$stats['inside'] = $result->fetch_assoc()['count'];

// Cars
$result = $conn->query("SELECT COUNT(*) AS count FROM vehicle WHERE vehicle_type = 'car'");
$stats['cars'] = $result->fetch_assoc()['count'];

// Motorcycles
$result = $conn->query("SELECT COUNT(*) AS count FROM vehicle WHERE vehicle_type = 'motorcycle'");
$stats['motorcycles'] = $result->fetch_assoc()['count'];

$conn->close();

echo json_encode($stats);
?>