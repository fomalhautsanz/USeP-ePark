<?php
include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$slot_number   = strtoupper(trim($_POST['slot_number']   ?? ''));
$location_area = strtoupper(trim($_POST['location_area'] ?? ''));
$status        = $_POST['status'] ?? 'available';

// Whitelist status
$allowed_statuses = ['available', 'occupied', 'reserved', 'maintenance'];
if (!in_array($status, $allowed_statuses, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
    exit;
}

if (!$slot_number || !$location_area) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Check duplicate slot number
$check = $conn->prepare("SELECT slot_id FROM parking_slots WHERE slot_number = ?");
if (!$check) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error']);
    exit;
}
$check->bind_param("s", $slot_number);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Slot number already exists.']);
    $check->close();
    exit;
}
$check->close();

$stmt = $conn->prepare("INSERT INTO parking_slots (slot_number, location_area, status) VALUES (?, ?, ?)");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error']);
    exit;
}
$stmt->bind_param("sss", $slot_number, $location_area, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'slot_id' => $conn->insert_id]);
} else {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Failed to add slot.']);
}

$stmt->close();
$conn->close();
?>