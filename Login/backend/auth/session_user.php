<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

echo json_encode([
    'user_id'   => $_SESSION['user_id'],
    'user_code' => $_SESSION['user_code'],
    'firstname' => $_SESSION['firstname'],
    'lastname'  => $_SESSION['lastname'],
    'email'     => $_SESSION['email'],
    'role'      => $_SESSION['role'],
]);
?>