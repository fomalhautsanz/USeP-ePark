<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: http://localhost/USeP-ePark-main/Login/login.html');
    exit;
}
if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff') {
    header('Location: http://localhost/USeP-ePark-main/Admin/dashboard.php');
    exit;
}

header('Content-Type: application/json');
require_once __DIR__ . '/../Login/backend/config/database.php';

$user_id        = $_SESSION['user_id'];
$current_pw     = $_POST['current_password'] ?? '';
$new_pw         = $_POST['new_password'] ?? '';
$confirm_pw     = $_POST['confirm_password'] ?? '';

// ── Validate fields ──
if (!$current_pw || !$new_pw || !$confirm_pw) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}
if (strlen($new_pw) < 8) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters.']);
    exit;
}
if ($new_pw !== $confirm_pw) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

// ── Get current password hash from DB ──
$stmt = mysqli_prepare($conn, "SELECT password_hash FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $password_hash);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// ── Check if current password is correct ──
if (!password_verify($current_pw, $password_hash)) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    exit;
}

// ── Hash new password and update ──
$new_hash = password_hash($new_pw, PASSWORD_BCRYPT);
$stmt2 = mysqli_prepare($conn, "UPDATE users SET password_hash = ? WHERE user_id = ?");
mysqli_stmt_bind_param($stmt2, 'si', $new_hash, $user_id);

if (!mysqli_stmt_execute($stmt2)) {
    echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
    exit;
}
mysqli_stmt_close($stmt2);
mysqli_close($conn);

echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);
?>