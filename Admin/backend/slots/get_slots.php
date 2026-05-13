<?php
include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("
    SELECT * FROM view_all_slots_status
    ORDER BY location_area ASC,
             CAST(SUBSTRING_INDEX(slot_number, '-', -1) AS UNSIGNED) ASC
");

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$slots = [];
while ($row = $result->fetch_assoc()) {
    $slots[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($slots);
?>