<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

if (empty($_SESSION['qr_code'])) {
    require_once __DIR__ . '/../config/database.php';
    $stmt = mysqli_prepare($conn, "SELECT qr_code FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    if ($row && !empty($row['qr_code'])) {
        $_SESSION['qr_code'] = $row['qr_code'];
    }
}

echo json_encode([
    'user_id'         => $_SESSION['user_id'],
    'user_code'       => $_SESSION['user_code'],
    'firstname'       => $_SESSION['firstname'],
    'lastname'        => $_SESSION['lastname'],
    'email'           => $_SESSION['email'],
    'role'            => $_SESSION['role'],
    'qr_code'         => $_SESSION['qr_code'] ?? null,
    'profile_picture' => $_SESSION['profile_picture'] ?? null,
]);
?>