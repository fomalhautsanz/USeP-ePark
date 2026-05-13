<?php
include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$slot_id       = $_POST['slot_id']       ?? '';
$slot_number   = strtoupper(trim($_POST['slot_number']   ?? ''));
$location_area = strtoupper(trim($_POST['location_area'] ?? ''));
$status        = $_POST['status']        ?? '';

// Validate all fields present
if (!$slot_id || !is_numeric($slot_id) || !$slot_number || !$location_area || !$status) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Whitelist status — this was missing and is why 'maintenance' silently failed
$allowed_statuses = ['available', 'occupied', 'reserved', 'maintenance'];
if (!in_array($status, $allowed_statuses, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
    exit;
}

// Block manual status change if slot is currently occupied
if ($status !== 'occupied') {
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
        echo json_encode(['success' => false, 'message' => 'Cannot change status of an occupied slot.']);
        $check->close();
        exit;
    }
    $check->close();
}

// Check duplicate slot number excluding current slot
$check = $conn->prepare("SELECT slot_id FROM parking_slots WHERE slot_number = ? AND slot_id != ?");
if (!$check) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error']);
    exit;
}
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
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error']);
    exit;
}
$stmt->bind_param("sssi", $slot_number, $location_area, $status, $slot_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Failed to update slot.']);
}

$stmt->close();
$conn->close();
?>