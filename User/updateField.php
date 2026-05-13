<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

header('Content-Type: application/json');
require_once '../Login/config/database.php';

$data    = json_decode(file_get_contents('php://input'), true);
$field   = $data['field'] ?? '';
$value   = $data['value'] ?? '';
$user_id = $_SESSION['user_id'];

// only allow safe fields to prevent SQL injection
$allowed = ['gender', 'birthdate'];
if (!in_array($field, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid field.']);
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE users SET $field = ? WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'si', $value, $user_id);

if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save.']);
    exit;
}
mysqli_stmt_close($stmt);

$_SESSION[$field] = $value;
mysqli_close($conn);

echo json_encode(['success' => true]);
?>