<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("
    SELECT
        s.slot_id,
        s.slot_number,
        s.location_area,
        s.status,
        -- Get current occupant if occupied
        v.plate_number,
        CONCAT(u.firstname, ' ', u.lastname) AS occupant_name,
        e.time_in
    FROM parking_slots s
    LEFT JOIN entry_exit_logs e ON e.slot_id = s.slot_id AND e.time_out IS NULL
    LEFT JOIN vehicle v ON v.vehicle_id = e.vehicle_id
    LEFT JOIN users u ON u.user_id = v.user_id
    ORDER BY s.location_area ASC, 
         CAST(SUBSTRING_INDEX(s.slot_number, '-', -1) AS UNSIGNED) ASC
");

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
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