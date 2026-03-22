<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$vehicle_id = $_POST['vehicle_id'] ?? '';

if (!$vehicle_id) {
    echo json_encode(['success' => false, 'message' => 'No vehicle ID provided.']);
    exit;
}

// Block deletion if vehicle is currently inside
$check = $conn->prepare("SELECT log_id FROM entry_exit_logs WHERE vehicle_id = ? AND time_out IS NULL");
$check->bind_param("i", $vehicle_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete a vehicle that is currently parked inside.']);
    $check->close();
    exit;
}
$check->close();

$stmt = $conn->prepare("DELETE FROM vehicle WHERE vehicle_id = ?");
$stmt->bind_param("i", $vehicle_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>