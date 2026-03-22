<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$slot_id = $_POST['slot_id'] ?? '';

if (!$slot_id) {
    echo json_encode(['success' => false, 'message' => 'No slot ID provided.']);
    exit;
}

// Block deletion if slot is occupied
$check = $conn->prepare("SELECT log_id FROM entry_exit_logs WHERE slot_id = ? AND time_out IS NULL");
$check->bind_param("i", $slot_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete an occupied slot.']);
    $check->close();
    exit;
}
$check->close();

$stmt = $conn->prepare("DELETE FROM parking_slots WHERE slot_id = ?");
$stmt->bind_param("i", $slot_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>