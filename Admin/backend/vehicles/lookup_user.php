<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$user_code = trim($_GET['user_code'] ?? '');

if (!$user_code) {
    echo json_encode(['error' => 'No user code provided']);
    exit;
}

$stmt = $conn->prepare("
    SELECT user_id, firstname, lastname, role, status
    FROM users
    WHERE user_code = ?
    LIMIT 1
");
$stmt->bind_param("s", $user_code);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

if ($user['status'] === 'suspended') {
    echo json_encode(['error' => 'This account is suspended']);
    exit;
}

echo json_encode([
    'user_id'   => $user['user_id'],
    'fullname'  => $user['firstname'] . ' ' . $user['lastname'],
    'role'      => $user['role'],
]);
?>