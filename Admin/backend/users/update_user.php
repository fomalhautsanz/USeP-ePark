<?php
include '../../config/database.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// ── Inputs ───────────────────────────────────────────────────────────────────
$user_id   = $_POST['user_id']         ?? '';
$firstname = trim($_POST['first_name'] ?? '');
$lastname  = trim($_POST['last_name']  ?? '');
$email     = trim($_POST['email']      ?? '');
$phone     = trim($_POST['phone']      ?? '');
$role      = $_POST['role']            ?? '';
$password  = $_POST['password']        ?? '';

// ── Validation ───────────────────────────────────────────────────────────────
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
    exit;
}

if (!$firstname || !$lastname || !$email || !$phone || !$role) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!in_array($role, ['customer', 'staff', 'admin'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// ── Hash password only if a new one was provided, otherwise pass NULL ─────────
// The procedure skips updating password_hash when it receives NULL,
// so the existing hash in the DB is preserved.
$password_hash = !empty($password)
    ? password_hash($password, PASSWORD_DEFAULT)
    : null;

// ── Call sp_update_user_full ──────────────────────────────────────────────────
$stmt = $conn->prepare("
    CALL sp_update_user_full(?, ?, ?, ?, ?, ?, ?, @p_message)
");

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error (S1)']);
    exit;
}

$stmt->bind_param(
    "issssss",
    $user_id,
    $firstname,
    $lastname,
    $email,
    $phone,
    $role,
    $password_hash
);

if (!$stmt->execute()) {
    error_log("Procedure call failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Update failed']);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

$outStmt = $conn->query("SELECT @p_message AS message");

if (!$outStmt) {
    error_log("Failed to read OUT param: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error (S2)']);
    $conn->close();
    exit;
}

$result = $outStmt->fetch_assoc();
$outStmt->close();
$conn->close();

// ── Respond ───────────────────────────────────────────────────────────────────
if ($result && str_starts_with($result['message'], 'SUCCESS')) {
    echo json_encode(['success' => true]);
} else {
    $errMsg = isset($result['message'])
        ? preg_replace('/^ERROR:\s*/i', '', $result['message'])
        : 'Update failed';
    error_log("sp_update_user_full error: " . ($result['message'] ?? 'null'));
    echo json_encode(['success' => false, 'message' => $errMsg]);
}
?>