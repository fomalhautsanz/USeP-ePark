<?php
include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$slot_id = $_POST['slot_id'] ?? '';

if (!$slot_id || !is_numeric($slot_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid slot ID.']);
    exit;
}

// Block deletion if slot is currently occupied
$check = $conn->prepare("SELECT log_id FROM entry_exit_logs WHERE slot_id = ? AND log_status = 'in'");
if (!$check) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error']);
    exit;
}
$check->bind_param("i", $slot_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete an occupied slot.']);
    $check->close();
    exit;
}
$check->close();

// Also block if slot has an active reservation
$check2 = $conn->prepare("SELECT reservation_id FROM reservations WHERE slot_id = ? AND status = 'active'");
if (!$check2) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error']);
    exit;
}
$check2->bind_param("i", $slot_id);
$check2->execute();
$check2->store_result();
if ($check2->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete a slot with an active reservation.']);
    $check2->close();
    exit;
}
$check2->close();

$stmt = $conn->prepare("DELETE FROM parking_slots WHERE slot_id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error']);
    exit;
}
$stmt->bind_param("i", $slot_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Failed to delete slot.']);
}

$stmt->close();
$conn->close();
?>