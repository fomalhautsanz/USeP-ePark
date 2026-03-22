<?php
include '../../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) AS count FROM users");
$stats['total'] = $result->fetch_assoc()['count'];

// Active users
$result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE status = 'active'");
$stats['active'] = $result->fetch_assoc()['count'];

// Suspended users
$result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE status = 'suspended'");
$stats['suspended'] = $result->fetch_assoc()['count'];

// Admins
$result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'");
$stats['admins'] = $result->fetch_assoc()['count'];

$conn->close();

header('Content-Type: application/json');
echo json_encode($stats);
?>