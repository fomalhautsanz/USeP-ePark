<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$result = $conn->query("SELECT * FROM view_monthly_revenue_trend");
if (!$result) {
    error_log("Query failed: " . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'year'               => (int)$row['year'],
        'month'              => (int)$row['month'],
        'month_label'        => $row['month_label'],
        'yearmonth'          => $row['yearmonth'],
        'total_revenue'      => (float)$row['total_revenue'],
        'total_transactions' => (int)$row['total_transactions'],
    ];
}

// Reverse so oldest month is first (for left-to-right chart rendering)
$data = array_reverse($data);

$conn->close();
echo json_encode($data);
?>