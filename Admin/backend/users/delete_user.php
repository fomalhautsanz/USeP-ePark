<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$user_id = $_POST['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>