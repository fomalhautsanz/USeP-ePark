<?php
include '../../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Stats file — query the view
$queries = [
    'total'     => "SELECT COUNT(*) AS count FROM view_users",
    'active'    => "SELECT COUNT(*) AS count FROM view_users WHERE status = 'active'",
    'suspended' => "SELECT COUNT(*) AS count FROM view_users WHERE status = 'suspended'",
    'admins'    => "SELECT COUNT(*) AS count FROM view_users WHERE role = 'admin'",
];

$stats = [];
foreach ($queries as $key => $sql) {
    $result = $conn->query($sql);
    $stats[$key] = $result->fetch_assoc()['count'];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($stats);
?>