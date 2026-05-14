<?php
include '../../config/database.php';
header('Content-Type: application/json');

// Use sp_get_daily_report for the last 7 days
$from = date('Y-m-d', strtotime('-6 days'));
$to   = date('Y-m-d');

$stmt = $conn->prepare("CALL sp_get_daily_report(?, ?)");
$stmt->bind_param("ss", $from, $to);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'day'   => date('D', strtotime($row['report_date'])), // Mon, Tue...
        'date'  => $row['report_date'],
        'count' => (int) $row['total_entries'],
    ];
}

$stmt->close();
$conn->close();
echo json_encode($data);
?>