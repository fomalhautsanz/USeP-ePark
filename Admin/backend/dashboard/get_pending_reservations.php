<?php
// ── Auth guard ────────────────────────────────────────────────────────────────
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'customer') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

if (!$conn) {
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

// ── Uses view_reservations_full which already joins:
//    reservations → users → vehicle → parking_slots
//
// Schema note: 'active' = pending in the ENUM
//   ENUM('active','expired','cancelled','completed')
//
// Capped at 5 rows for the dashboard widget.
// The full management list lives on reservations.php.

$sql = "
    SELECT
        reservation_id,
        ref_number,
        full_name,
        user_role,
        profile_picture,
        slot_number,
        location_area,
        time_label,
        date_label,
        minutes_until_expiry
    FROM view_reservations_full
    WHERE status = 'active'
    ORDER BY time_reserved ASC
    LIMIT 5
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
    mysqli_close($conn);
    exit;
}

$reservations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reservations[] = [
        'reservation_id'      => (int) $row['reservation_id'],
        'ref_number'          => $row['ref_number'],               // e.g. RES-001   
        'full_name'           => $row['full_name'],
        'user_role'           => $row['user_role'],
        'profile_picture'     => $row['profile_picture'] ?? '',
        'slot_number'         => $row['slot_number'],              // e.g. A-07
        'location_area'       => $row['location_area'],
        'time_label'          => $row['time_label'],               // e.g. 09:00 AM
        'date_label'          => $row['date_label'],               // e.g. Today / May 15, 2026
        'minutes_until_expiry'=> (int) $row['minutes_until_expiry'],
    ];
}

mysqli_free_result($result);
mysqli_close($conn);

echo json_encode($reservations);