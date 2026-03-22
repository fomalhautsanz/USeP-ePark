<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$vehicle_id   = $_POST['vehicle_id']   ?? '';
$plate_number = strtoupper(trim($_POST['plate_number'] ?? ''));
$vehicle_type = strtolower($_POST['vehicle_type'] ?? '');
$user_id      = $_POST['user_id']      ?? '';

if (!$vehicle_id || !$plate_number || !$vehicle_type || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Check duplicate plate — exclude current vehicle
$check = $conn->prepare("SELECT vehicle_id FROM vehicle WHERE plate_number = ? AND vehicle_id != ?");
$check->bind_param("si", $plate_number, $vehicle_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Plate number already used by another vehicle.']);
    $check->close();
    exit;
}
$check->close();

$stmt = $conn->prepare("UPDATE vehicle SET plate_number = ?, vehicle_type = ?, user_id = ? WHERE vehicle_id = ?");
$stmt->bind_param("ssii", $plate_number, $vehicle_type, $user_id, $vehicle_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>