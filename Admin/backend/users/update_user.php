<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$user_id   = $_POST['user_id']    ?? '';
$firstname = $_POST['first_name'] ?? '';
$lastname  = $_POST['last_name']  ?? '';
$email     = $_POST['email']      ?? '';
$phone     = $_POST['phone']      ?? '';
$role      = $_POST['role']       ?? '';
$password  = $_POST['password']   ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
    exit;
}

if (!empty($password)) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("
        UPDATE users
        SET firstname = ?, lastname = ?, email = ?, contact_number = ?, role = ?, password_hash = ?
        WHERE user_id = ?
    ");
    $stmt->bind_param("ssssssi", $firstname, $lastname, $email, $phone, $role, $password_hash, $user_id);
} else {
    $stmt = $conn->prepare("
        UPDATE users
        SET firstname = ?, lastname = ?, email = ?, contact_number = ?, role = ?
        WHERE user_id = ?
    ");
    $stmt->bind_param("sssssi", $firstname, $lastname, $email, $phone, $role, $user_id);
}

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>