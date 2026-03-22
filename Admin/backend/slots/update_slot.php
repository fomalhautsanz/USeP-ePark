<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$slot_id       = $_POST['slot_id']       ?? '';
$slot_number   = strtoupper(trim($_POST['slot_number']   ?? ''));
$location_area = trim($_POST['location_area'] ?? '');
$status        = $_POST['status']        ?? '';

if (!$slot_id || !$slot_number || !$location_area || !$status) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Block manual status change if occupied
if ($status !== 'occupied') {
    $check = $conn->prepare("SELECT log_id FROM entry_exit_logs WHERE slot_id = ? AND time_out IS NULL");
    $check->bind_param("i", $slot_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot change status of an occupied slot.']);
        $check->close();
        exit;
    }
    $check->close();
}

// Check duplicate slot number excluding current
$check = $conn->prepare("SELECT slot_id FROM parking_slots WHERE slot_number = ? AND slot_id != ?");
$check->bind_param("si", $slot_number, $slot_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Slot number already used by another slot.']);
    $check->close();
    exit;
}
$check->close();

$stmt = $conn->prepare("UPDATE parking_slots SET slot_number = ?, location_area = ?, status = ? WHERE slot_id = ?");
$stmt->bind_param("sssi", $slot_number, $location_area, $status, $slot_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>