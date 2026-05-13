<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

include '../../config/database.php';

function json_fail(string $client_msg, string $internal = ''): never
{
    if ($internal) error_log('[delete_user] ' . $internal);
    echo json_encode(['success' => false, 'message' => $client_msg]);
    exit;
}

if (!$conn) {
    json_fail('Service unavailable.', 'DB connection failed');
}


$user_id = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if (!$user_id) {
    json_fail('Invalid user ID.');
}

$stmt = $conn->prepare("CALL sp_delete_user(?, @p_message)");

if (!$stmt) {
    json_fail('Service unavailable.', 'prepare failed: ' . $conn->error);
}

$stmt->bind_param('i', $user_id);

if (!$stmt->execute()) {
    $err = $stmt->error;
    $stmt->close();
    json_fail('Deletion failed. Please try again.', 'execute failed: ' . $err);
}

$stmt->close();

$result = $conn->query("SELECT @p_message AS message");

if (!$result) {
    json_fail('Service unavailable.', 'OUT param read failed: ' . $conn->error);
}

$row = $result->fetch_assoc();
$conn->close();


if ($row && str_starts_with($row['message'], 'SUCCESS')) {
    echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
} else {
    $errMsg = isset($row['message'])
        ? preg_replace('/^ERROR:\s*/i', '', $row['message'])
        : 'Deletion failed.';
    error_log('[delete_user] sp_delete_user: ' . ($row['message'] ?? 'null'));
    echo json_encode(['success' => false, 'message' => $errMsg]);
}