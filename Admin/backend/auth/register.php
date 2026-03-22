<?php
// ── Admin/backend/auth/register.php ──
 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
 
// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}
 
// ── DB Connection ──
require_once '../../config/database.php';
 
// ── Get & Sanitize Input ──
$firstname      = trim($_POST['firstname']      ?? '');
$lastname       = trim($_POST['lastname']       ?? '');
$email          = trim($_POST['email']          ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');
$username       = trim($_POST['username']       ?? '');
$password       = $_POST['password']            ?? '';
$plate_number   = strtoupper(trim($_POST['plate_number'] ?? ''));
$vehicle_type   = trim($_POST['vehicle_type']   ?? '');
 
// ── Server-side Validation ──
if (!$firstname || !$lastname || !$email || !$contact_number || !$username || !$password || !$plate_number || !$vehicle_type) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}
 
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}
 
if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
    exit;
}
 
// ── Check if email already exists ──
$checkEmail = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ?");
mysqli_stmt_bind_param($checkEmail, 's', $email);
mysqli_stmt_execute($checkEmail);
mysqli_stmt_store_result($checkEmail);
 
if (mysqli_stmt_num_rows($checkEmail) > 0) {
    echo json_encode(['success' => false, 'message' => 'Email is already registered.']);
    mysqli_stmt_close($checkEmail);
    exit;
}
mysqli_stmt_close($checkEmail);
 
// ── Check if username already exists ──
$checkUsername = mysqli_prepare($conn, "SELECT user_id FROM users WHERE user_code = ?");
mysqli_stmt_bind_param($checkUsername, 's', $username);
mysqli_stmt_execute($checkUsername);
mysqli_stmt_store_result($checkUsername);
 
if (mysqli_stmt_num_rows($checkUsername) > 0) {
    echo json_encode(['success' => false, 'message' => 'Username is already taken.']);
    mysqli_stmt_close($checkUsername);
    exit;
}
mysqli_stmt_close($checkUsername);
 
// ── Hash Password ──
$password_hash = password_hash($password, PASSWORD_BCRYPT);
$role   = 'customer';
$status = 'active';
 
// ── Insert into users table ──
$insertUser = mysqli_prepare($conn, "
    INSERT INTO users (firstname, lastname, email, contact_number, role, password_hash, status, user_code, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
 
mysqli_stmt_bind_param(
    $insertUser, 'ssssssss',
    $firstname,
    $lastname,
    $email,
    $contact_number,
    $role,
    $password_hash,
    $status,
    $username
);
 
if (!mysqli_stmt_execute($insertUser)) {
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    mysqli_stmt_close($insertUser);
    exit;
}
 
// ── Get the new user_id ──
$user_id = mysqli_insert_id($conn);
mysqli_stmt_close($insertUser);
 
// ── Insert into vehicle table ──
$insertVehicle = mysqli_prepare($conn, "
    INSERT INTO vehicle (user_id, plate_number, vehicle_type)
    VALUES (?, ?, ?)
");
 
mysqli_stmt_bind_param(
    $insertVehicle, 'iss',
    $user_id,
    $plate_number,
    $vehicle_type
);
 
if (!mysqli_stmt_execute($insertVehicle)) {
    // Rollback user insert if vehicle insert fails
    $deleteUser = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($deleteUser, 'i', $user_id);
    mysqli_stmt_execute($deleteUser);
    mysqli_stmt_close($deleteUser);
 
    echo json_encode(['success' => false, 'message' => 'Failed to save vehicle info. Please try again.']);
    mysqli_stmt_close($insertVehicle);
    exit;
}
 
mysqli_stmt_close($insertVehicle);
mysqli_close($conn);
 
// ── Success ──
echo json_encode([
    'success'  => true,
    'message'  => 'Account created successfully!',
    'redirect' => 'login.html' 
]);
?>