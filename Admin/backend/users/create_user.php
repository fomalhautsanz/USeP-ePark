<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

if (!$conn) {
    echo "error: Database connection failed";
    exit;
}

$firstname      = $_POST['first_name']  ?? '';
$lastname       = $_POST['last_name']   ?? '';
$email          = $_POST['email']       ?? '';
$contact_number = $_POST['phone']       ?? '';
$role           = $_POST['role']        ?? 'student';
$password_hash  = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
$status         = 'active';

$prefixMap = [
    'customer' => 'CUS',
    'staff'   => 'STA',
    'admin'   => 'ADM',
];

$prefix = $prefixMap[$role] ?? 'USR';
$year   = date('Y');

$likePattern = "$prefix-$year-%";
$seqStmt = $conn->prepare("
    SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(user_code, '-', -1) AS UNSIGNED)), 0)
    FROM users
    WHERE role = ? AND user_code LIKE ?
");
$seqStmt->bind_param("ss", $role, $likePattern);
$seqStmt->execute();
$seqStmt->bind_result($maxSeq);
$seqStmt->fetch();
$seqStmt->close();

$sequence  = str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
$user_code = "$prefix-$year-$sequence";

$stmt = $conn->prepare("
    INSERT INTO users (firstname, lastname, email, contact_number, role, password_hash, status, user_code, qr_code)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL)
");

if (!$stmt) {
    echo "error: Prepare failed — " . $conn->error;
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
    $status,
    $user_code
);

if ($stmt->execute()) {
    echo "success:" . $conn->insert_id . ":" . $user_code;
} else {
    echo "error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>