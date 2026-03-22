<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("
    SELECT
        u.user_id,
        u.user_code,
        u.firstname,
        u.lastname,
        u.email,
        u.contact_number,
        u.role,
        u.status,
        u.last_login,
        u.created_at,
        COUNT(v.vehicle_id) AS vehicle_count
    FROM users u
    LEFT JOIN vehicle v ON v.user_id = u.user_id
    GROUP BY u.user_id
    ORDER BY u.created_at DESC
");

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($users);
?>