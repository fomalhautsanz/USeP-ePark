<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$user_id      = intval($_POST['user_id'] ?? 0);
$plate_number = strtoupper(trim($_POST['plate_number'] ?? ''));
$vehicle_type = strtolower(trim($_POST['vehicle_type'] ?? ''));


if (!$user_id || !$plate_number || !$vehicle_type) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!preg_match('/^[A-Z0-9\-]{2,20}$/', $plate_number)) {
    echo json_encode(['success' => false, 'message' => 'Invalid plate number format.']);
    exit;
}

if (!in_array($vehicle_type, ['car', 'motorcycle'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid vehicle type.']);
    exit;
}


$stmt = $conn->prepare("CALL sp_register_vehicle(?, ?, ?, @vehicle_id, @message)");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iss", $user_id, $plate_number, $vehicle_type);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Execution failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$stmt->close();

$result = $conn->query("SELECT @vehicle_id AS vehicle_id, @message AS message");

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve procedure output']);
    exit;
}

$output = $result->fetch_assoc();
$vehicle_id = $output['vehicle_id'];
$message    = $output['message'];

$conn->close();

if ($vehicle_id) {
    echo json_encode([
        'success'    => true,
        'vehicle_id' => $vehicle_id,
        'message'    => $message
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
}
?>