<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$vehicle_id = intval($_POST['vehicle_id'] ?? 0);

if (!$vehicle_id) {
    echo json_encode(['success' => false, 'message' => 'No vehicle ID provided.']);
    exit;
}


$stmt = $conn->prepare("CALL sp_remove_vehicle(?, @message)");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $vehicle_id);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Execution failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$stmt->close();

$result = $conn->query("SELECT @message AS message");

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve procedure output']);
    exit;
}

$output = $result->fetch_assoc();
$message = $output['message'];

$conn->close();

if (strpos($message, 'SUCCESS') === 0) {
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
}
?>