<?php
// ============================================================================
// User/backend/get_reservation_status.php
// Lightweight polling endpoint — returns the user's current active reservation
// so the frontend can update the status banner and countdown timer without
// a full page reload.
// ============================================================================

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

include '../../Admin/config/database.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "
    SELECT
        r.reservation_id,
        CONCAT('RES-', LPAD(r.reservation_id, 3, '0'))      AS ref_number,
        r.slot_id,
        ps.slot_number,
        ps.location_area,
        r.status,
        DATE_FORMAT(r.time_reserved,      '%M %d, %Y')      AS date_label,
        TIME_FORMAT(r.time_reserved,      '%h:%i %p')       AS time_reserved_label,
        TIME_FORMAT(r.reservation_expiry, '%h:%i %p')       AS expiry_label,
        TIMESTAMPDIFF(MINUTE, NOW(), r.reservation_expiry)  AS minutes_until_expiry,
        TIMESTAMPDIFF(SECOND, NOW(), r.reservation_expiry)  AS seconds_until_expiry
    FROM reservations r
    JOIN parking_slots ps ON ps.slot_id = r.slot_id
    WHERE r.user_id = ?
      AND r.status  = 'active'
    ORDER BY r.time_reserved DESC
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$reservation = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

mysqli_close($conn);

echo json_encode([
    'success'     => true,
    'reservation' => $reservation ?: null,
]);