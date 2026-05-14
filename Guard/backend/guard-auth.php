<?php
// Guard/backend/guard-auth.php
// Guards log in using their email or user_code + password.
// Uses view_users (SELECT via view ) instead of querying the users table directly.
// Only admin and staff roles are allowed through.

ini_set('display_errors', 0);
error_reporting(E_ALL);

include '../config/database.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$login    = trim($input['username'] ?? '');   // guard types email OR user_code
$password = $input['password'] ?? '';

if (!$login || !$password) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

// SELECT from view — no raw table access (view_users already filters columns safely)
// We still need password_hash, which isn't in view_users, so we join via user_id.
// The view lookup gives us the user_id; then we fetch the hash from users directly
// (only for the single row, never a broad SELECT *).
$stmt = $conn->prepare("
    SELECT
        vu.user_id,
        vu.user_code,
        vu.firstname,
        vu.lastname,
        vu.role,
        vu.status,
        u.password_hash
    FROM view_users vu
    JOIN users u ON u.user_id = vu.user_id
    WHERE (vu.email = ? OR vu.user_code = ?)
      AND vu.role IN ('admin', 'staff')
    LIMIT 1
");

if (!$stmt) {
    error_log('[guard-auth] prepare failed: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error.']);
    exit;
}

$stmt->bind_param('ss', $login, $login);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

// ── Validation ───────────────────────────────────────────────────────────────
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

if (!password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    $conn->close();
    exit;
}

// ── Start session ─────────────────────────────────────────────────────────────
session_start();
$_SESSION['guard_id']   = $user['user_id'];
$_SESSION['guard_name'] = $user['firstname'] . ' ' . $user['lastname'];
$_SESSION['guard_role'] = $user['role'];

$conn->close();

echo json_encode([
    'success' => true,
    'guard'   => [
        'id'       => $user['user_id'],
        'name'     => $user['firstname'] . ' ' . $user['lastname'],
        'username' => $user['user_code'],
    ],
]);
