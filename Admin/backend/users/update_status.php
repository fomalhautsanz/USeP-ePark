<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$user_id = $_POST['user_id'] ?? '';
$status  = $_POST['status']  ?? '';

if (!$user_id || !in_array($status, ['active', 'suspended'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$stmt = $conn->prepare("CALL sp_update_user_status(?, ?, @p_message)");

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error (S1)']);
    exit;
}

$stmt->bind_param("is", $user_id, $status);

if (!$stmt->execute()) {
    error_log("Procedure call failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Status update failed']);
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


if ($result && str_starts_with($result['message'], 'SUCCESS')) {
    echo json_encode(['success' => true]);
} else {
    $errMsg = isset($result['message'])
        ? preg_replace('/^ERROR:\s*/i', '', $result['message'])
        : 'Status update failed';
    error_log("sp_update_user_status error: " . ($result['message'] ?? 'null'));
    echo json_encode(['success' => false, 'message' => $errMsg]);
}
?>