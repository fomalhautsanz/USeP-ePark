<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

if (!$conn) {
    echo "error: Database connection failed";
    exit;
}

// ── Validate role
$allowedRoles = ['customer', 'staff', 'admin'];
$role = $_POST['role'] ?? '';
if (!in_array($role, $allowedRoles, true)) {
    echo "error: Invalid role specified";
    exit;
}

// ── Sanitize inputs 
$firstname      = trim($_POST['first_name']  ?? '');
$lastname       = trim($_POST['last_name']   ?? '');
$email          = trim($_POST['email']       ?? '');
$contact_number = trim($_POST['phone']       ?? '');
$password_raw   = $_POST['password']         ?? '';
$birthdate      = trim($_POST['birthdate']   ?? '') ?: null;
$gender         = trim($_POST['gender']      ?? '') ?: null;

if (!$firstname || !$lastname || !$email || !$contact_number || !$password_raw) {
    echo "error: All fields are required";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "error: Invalid email format";
    exit;
}

$password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

// ── Generate user_code (CUS-2026-0001 format)
$prefixMap = [
    'customer' => 'CUS',
    'staff'    => 'STA',
    'admin'    => 'ADM'
];

$prefix      = $prefixMap[$role];
$year        = date('Y');
$likePattern = "$prefix-$year-%";

$seqStmt = $conn->prepare("
    SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(user_code, '-', -1) AS UNSIGNED)), 0)
    FROM users
    WHERE role = ? AND user_code LIKE ?
");

if (!$seqStmt) {
    echo "error: System error (S1)";
    exit;
}

$seqStmt->bind_param("ss", $role, $likePattern);
$seqStmt->execute();
$seqStmt->bind_result($maxSeq);
$seqStmt->fetch();
$seqStmt->close();

$sequence  = str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
$user_code = "$prefix-$year-$sequence";

$stmt = $conn->prepare("
    CALL sp_register_user(?, ?, ?, ?, ?, ?, ?, ?, @p_user_id, @p_message)
");

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo "error: System error (S2)";
    exit;
}

$stmt->bind_param(
    "ssssssss",
    $firstname,
    $lastname,
    $email,
    $contact_number,
    $role,
    $password_hash,
    $birthdate,
    $gender
);

if (!$stmt->execute()) {
    error_log("Procedure call failed: " . $stmt->error);
    echo "error: Registration failed";
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();


$outStmt = $conn->query("SELECT @p_user_id AS user_id, @p_message AS message");

if (!$outStmt) {
    error_log("Failed to read OUT params: " . $conn->error);
    echo "error: System error (S3)";
    $conn->close();
    exit;
}

$result = $outStmt->fetch_assoc();
$outStmt->close();

if (!$result || !str_starts_with($result['message'], 'SUCCESS')) {
    $errMsg = isset($result['message'])
        ? preg_replace('/^ERROR:\s*/i', '', $result['message'])
        : 'Registration failed';
    error_log("sp_register_user error: " . ($result['message'] ?? 'null'));
    echo "error: " . $errMsg;
    $conn->close();
    exit;
}

$newUserId = $result['user_id'];


$updateStmt = $conn->prepare("UPDATE users SET user_code = ? WHERE user_id = ?");

if (!$updateStmt) {
    error_log("user_code update prepare failed: " . $conn->error);
   
} else {
    $updateStmt->bind_param("si", $user_code, $newUserId);
    if (!$updateStmt->execute()) {
        error_log("user_code update failed: " . $updateStmt->error);
    }
    $updateStmt->close();
}

// ── Success ──────────────────────────────────────────────────────────────────
echo "success:" . $newUserId . ":" . $user_code;

$conn->close();
?>