<?php
include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$log_id = $_GET['log_id'] ?? '';

if (!$log_id || !is_numeric($log_id)) {
    echo json_encode(['error' => 'Invalid log ID']);
    exit;
}

// Pull full detail from view — includes payment and reservation info
$stmt = $conn->prepare("SELECT * FROM view_logs_list WHERE log_id = ?");

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

$stmt->bind_param("i", $log_id);

if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

$result = $stmt->get_result();
$row    = $result->fetch_assoc();

if (!$row) {
    echo json_encode(['error' => 'Log not found']);
    exit;
}

// Cast numeric fields
$row['log_id']         = (int)   $row['log_id'];
$row['total_duration'] = $row['total_duration'] !== null ? (float) $row['total_duration'] : null;
$row['parking_fee']    = $row['parking_fee']    !== null ? (float) $row['parking_fee']    : null;
$row['payment_amount'] = $row['payment_amount'] !== null ? (float) $row['payment_amount'] : null;

$stmt->close();
$conn->close();

echo json_encode($row);
?>