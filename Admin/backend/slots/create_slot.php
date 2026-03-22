<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$slot_number   = strtoupper(trim($_POST['slot_number']   ?? ''));
$location_area = trim($_POST['location_area'] ?? '');
$status        = $_POST['status'] ?? 'available';

if (!$slot_number || !$location_area) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Check duplicate slot number
$check = $conn->prepare("SELECT slot_id FROM parking_slots WHERE slot_number = ?");
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
$stmt->bind_param("sss", $slot_number, $location_area, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'slot_id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>