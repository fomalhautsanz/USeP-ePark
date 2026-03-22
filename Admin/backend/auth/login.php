<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password =       $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Please enter your email and password.']);
    exit;
}

$stmt = $conn->prepare("
    SELECT user_id, firstname, lastname, email, role, status, password_hash, user_code
    FROM users
    WHERE email = ?
    LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

// User not found
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    exit;
}

// Wrong password
if (!password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    exit;
}

// Suspended account
if ($user['status'] === 'suspended') {
    echo json_encode(['success' => false, 'message' => 'Your account has been suspended. Please contact support.']);
    exit;
}

// Update last_login
$upd = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
$upd->bind_param("i", $user['user_id']);
$upd->execute();
$upd->close();
$conn->close();

// Set session
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['user_code'] = $user['user_code'];
$_SESSION['firstname'] = $user['firstname'];
$_SESSION['lastname']  = $user['lastname'];
$_SESSION['email']     = $user['email'];
$_SESSION['role']      = $user['role'];

// Redirect based on role
$base = 'http://' . $_SERVER['HTTP_HOST'];
$role = $user['role'];

$redirect = match($role) {
    'admin', 'staff' => $base . '/Admin/dashboard.php',
    'customer'       => $base . '/User/userDashboard.php',
    default          => null
};

if (!$redirect) {
    echo json_encode(['success' => false, 'message' => 'Unknown role. Contact support.']);
    exit;
}

echo json_encode(['success' => true, 'redirect' => $redirect]);
?>