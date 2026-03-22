<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$user_id      = $_POST['user_id']      ?? '';
$plate_number = strtoupper(trim($_POST['plate_number'] ?? ''));
$vehicle_type = strtolower($_POST['vehicle_type'] ?? '');

if (!$user_id || !$plate_number || !$vehicle_type) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Check for duplicate plate number
$check = $conn->prepare("SELECT vehicle_id FROM vehicle WHERE plate_number = ?");
$check->bind_param("s", $plate_number);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Plate number already registered.']);
    $check->close();
    exit;
}
$check->close();

$stmt = $conn->prepare("INSERT INTO vehicle (user_id, plate_number, vehicle_type) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $plate_number, $vehicle_type);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'vehicle_id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>