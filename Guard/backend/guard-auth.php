<?php
// Guard/backend/guard-auth.php
// Guards log in as users with role = 'admin' or 'staff'
// Uses the exact same `users` table and column names as the rest of the repo

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config/database.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$email    = trim($input['username'] ?? '');   // guard enters email or user_code
$password = $input['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

// Look up by email OR user_code — whichever they type
$stmt = $conn->prepare("
    SELECT user_id, firstname, lastname, email, role, status, password_hash, user_code
    FROM users
    WHERE (email = ? OR user_code = ?)
    AND role IN ('admin', 'staff')
    LIMIT 1
");
$stmt->bind_param("ss", $email, $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Guard account not found.']);
    $conn->close();
    exit;
}

if ($user['status'] === 'suspended') {
    echo json_encode(['success' => false, 'message' => 'This account has been suspended.']);
    $conn->close();
    exit;
}

// Verify password (uses password_hash like the rest of the repo)
if (!password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    $conn->close();
    exit;
}

// Start session
session_start();
$_SESSION['guard_id']   = $user['user_id'];
$_SESSION['guard_name'] = $user['firstname'] . ' ' . $user['lastname'];
$_SESSION['guard_role'] = $user['role'];

echo json_encode([
    'success' => true,
    'guard'   => [
        'id'       => $user['user_id'],
        'name'     => $user['firstname'] . ' ' . $user['lastname'],
        'username' => $user['user_code'],
    ]
]);

$conn->close();
