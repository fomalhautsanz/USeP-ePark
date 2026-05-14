<?php
include '../../config/database.php';
header('Content-Type: application/json');

// Use view_logs_list — already joins vehicle, user, slot, payment
$result = $conn->query("
    SELECT 
        log_id, log_status, time_in, time_out,
        total_duration, parking_fee,
        plate_number, vehicle_type,
        owner_name, contact_number,
        slot_number, location_area,
        payment_amount, receipt_number
    FROM view_logs_list
    ORDER BY COALESCE(time_out, time_in) DESC
    LIMIT 5
");

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

echo json_encode($logs);
$conn->close();
?>