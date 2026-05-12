<?php
include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$result = $conn->query("SELECT * FROM view_logs_today_stats");

if (!$result) {
    error_log("view_logs_today_stats query failed: " . $conn->error);
    echo json_encode(['error' => 'System error']);
    exit;
}

$row = $result->fetch_assoc();

echo json_encode([
    'today_entries' => (int)   ($row['today_entries'] ?? 0),
    'today_exits'   => (int)   ($row['today_exits']   ?? 0),
    'today_denied'  => (int)   ($row['today_denied']  ?? 0),
    'today_revenue' => (float) ($row['today_revenue'] ?? 0),
]);

$conn->close();
?>