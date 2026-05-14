<?php
// ============================================================
// Login/backend/auth/login.php
// ============================================================

session_set_cookie_params(['path' => '/', 'secure' => false, 'httponly' => true]);

ini_set('display_errors', 0);          // never expose errors to the browser
error_reporting(E_ALL);
ini_set('log_errors', 1);
// FIX: changed log path from /var/log/ (Linux only) to a path
//      that works on Windows/XAMPP. Adjust if your XAMPP is on a
//      different drive or folder. 
// ATAY NGANO NAKALINUX TO NAG LINUX MO????
ini_set('error_log', 'C:/xampp/php/logs/php-errors.log');

session_start();

// FIX: __DIR__ is Login/backend/auth/ so we go up 3 levels to reach the
//      project root, then into config/database.php
// config/ is at Login/backend/config/database.php
// Going up 1 level (../) from auth/ reaches backend/, then into config/ ----. F FOLDERS
// BOSET NAG BUG DIRIA MAO DIR NLNG 
include __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// FIX: reads 'email' (was already correct in login.php — the HTML was the bug) 
// USERNAME MAN GUD TO DAPAT EMAIL PARA PROFFESIONAL D MANI ROBLOX
$email    = trim($_POST['email']    ?? '');
$password =       $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Please enter your email and password.']);
    exit;
}

$stmt = $conn->prepare("
    SELECT u.user_id, u.firstname, u.lastname, u.email, u.role, u.status,
           u.password_hash, u.user_code, u.qr_code, u.contact_number,
           u.profile_picture, u.gender, u.birthdate,
           v.plate_number, v.vehicle_type
    FROM users u
    LEFT JOIN vehicle v ON v.user_id = u.user_id
    WHERE u.email = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query preparation failed.']);
    exit;
}

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
$_SESSION['user_id']         = $user['user_id'];
$_SESSION['user_code']       = $user['user_code'];
$_SESSION['firstname']       = $user['firstname'];
$_SESSION['lastname']        = $user['lastname'];
$_SESSION['email']           = $user['email'];
$_SESSION['role']            = $user['role'];
$_SESSION['contact_number']  = $user['contact_number'];
$_SESSION['profile_picture'] = $user['profile_picture'] ?? null;
$_SESSION['gender']          = $user['gender']          ?? null;
$_SESSION['birthdate']       = $user['birthdate']        ?? null;
$_SESSION['plate_number']    = $user['plate_number']     ?? null;
$_SESSION['vehicle_type']    = $user['vehicle_type']     ?? null;
$_SESSION['qr_code'] = $row['user_code'];

// ---------------------------------------------------------------
// Redirect based on role
// FIX: use a relative path from the web root (no hardcoded absolute
//      URL) so it works regardless of hostname / port.
//      Adjust folder names to match your actual htdocs structure.
// ---------------------------------------------------------------
$role = $user['role'];

$redirect = match($role) {
    'admin', 'staff' => '/Admin/dashboard.php',
    'customer'       => '/User/userDashboard.php', // DAPAT ANI 
    default          => null
};

if (!$redirect) {
    echo json_encode(['success' => false, 'message' => 'Unknown role. Contact support.']);
    exit;
}

echo json_encode(['success' => true, 'redirect' => $redirect]);

?>