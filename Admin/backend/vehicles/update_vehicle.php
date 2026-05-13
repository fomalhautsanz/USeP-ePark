<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$vehicle_id   = intval($_POST['vehicle_id'] ?? 0);
$plate_number = strtoupper(trim($_POST['plate_number'] ?? ''));
$vehicle_type = strtolower(trim($_POST['vehicle_type'] ?? ''));
$user_id      = intval($_POST['user_id'] ?? 0);

// ── Validation ──────────────────────────────────────────────────────────
if (!$vehicle_id || !$plate_number || !$vehicle_type || !$user_id) {
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

// ── Verify vehicle exists ───────────────────────────────────────────────
$verify = $conn->prepare("SELECT vehicle_id, user_id FROM vehicle WHERE vehicle_id = ?");
$verify->bind_param("i", $vehicle_id);
$verify->execute();
$verify_result = $verify->get_result();
$current_vehicle = $verify_result->fetch_assoc();
$verify->close();

if (!$current_vehicle) {
    echo json_encode(['success' => false, 'message' => 'Vehicle not found.']);
    exit;
}

// ── Verify user exists and is active ────────────────────────────────────
$user_check = $conn->prepare("SELECT user_id, status FROM users WHERE user_id = ?");
$user_check->bind_param("i", $user_id);
$user_check->execute();
$user_result = $user_check->get_result();
$user_data = $user_result->fetch_assoc();
$user_check->close();

if (!$user_data) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

if ($user_data['status'] === 'suspended') {
    echo json_encode(['success' => false, 'message' => 'Cannot assign vehicle to a suspended user.']);
    exit;
}

$dup_check = $conn->prepare("
    SELECT vehicle_id 
    FROM vehicle 
    WHERE plate_number = ? 
      AND user_id = ? 
      AND vehicle_id != ?
");
$dup_check->bind_param("sii", $plate_number, $user_id, $vehicle_id);
$dup_check->execute();
$dup_result = $dup_check->get_result();
$dup_check->close();

if ($dup_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This user already has a vehicle with this plate number.']);
    exit;
}

// ── Check if vehicle is currently parked ────────────────────────────────
$parked_check = $conn->prepare("
    SELECT log_id 
    FROM entry_exit_logs 
    WHERE vehicle_id = ? AND time_out IS NULL
");
$parked_check->bind_param("i", $vehicle_id);
$parked_check->execute();
$parked_result = $parked_check->get_result();
$parked_check->close();

if ($parked_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Cannot update a vehicle that is currently parked.']);
    exit;
}

// ── Update vehicle ──────────────────────────────────────────────────────
$stmt = $conn->prepare("
    UPDATE vehicle 
    SET plate_number = ?, vehicle_type = ?, user_id = ? 
    WHERE vehicle_id = ?
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssii", $plate_number, $vehicle_type, $user_id, $vehicle_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Vehicle updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>