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
        v.vehicle_id,
        v.plate_number,
        v.vehicle_type,
        u.user_id,
        u.user_code,
        u.firstname,
        u.lastname,
        u.role,
        CASE WHEN e.log_id IS NOT NULL THEN 'inside' ELSE 'outside' END AS parking_status,
        s.slot_number,
        (
            SELECT MAX(time_in)
            FROM entry_exit_logs
            WHERE vehicle_id = v.vehicle_id
        ) AS last_seen
    FROM vehicle v
    JOIN users u ON u.user_id = v.user_id
    LEFT JOIN entry_exit_logs e ON e.vehicle_id = v.vehicle_id AND e.time_out IS NULL
    LEFT JOIN parking_slots s ON s.slot_id = e.slot_id
    ORDER BY v.vehicle_id ASC
");

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$vehicles = [];
while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($vehicles);
?>