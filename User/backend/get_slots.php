<?php
// ============================================================================
// User/backend/get_slots.php
// Returns live parking slot data for the slot map + stat cards.
// Also returns the current user's active reservation (if any) so the
// frontend can show which slot they already hold.
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

// ── 1. All slots (uses existing view_all_slots_status) ──────────────────────
$slots_result = mysqli_query($conn, "SELECT * FROM view_all_slots_status ORDER BY slot_number ASC");
if (!$slots_result) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch slots.']);
    mysqli_close($conn);
    exit;
}

$slots = mysqli_fetch_all($slots_result, MYSQLI_ASSOC);

// ── 2. Slot availability summary (uses view_slot_availability) ───────────────
$avail_result = mysqli_query($conn, "SELECT * FROM view_slot_availability");
$availability = mysqli_fetch_all($avail_result, MYSQLI_ASSOC);

// ── 3. Overall stat counts ───────────────────────────────────────────────────
$stat_result = mysqli_query($conn, "
    SELECT
        SUM(CASE WHEN status = 'available'   THEN 1 ELSE 0 END) AS available,
        SUM(CASE WHEN status = 'occupied'    THEN 1 ELSE 0 END) AS occupied,
        SUM(CASE WHEN status = 'reserved'    THEN 1 ELSE 0 END) AS reserved,
        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) AS maintenance
    FROM parking_slots
");
$stats = mysqli_fetch_assoc($stat_result);

// ── 4. Current user's active reservation ────────────────────────────────────
$stmt = mysqli_prepare($conn, "
    SELECT
        r.reservation_id,
        r.slot_id,
        r.status,
        r.time_reserved,
        r.reservation_expiry,
        ps.slot_number,
        ps.location_area,
        CONCAT('RES-', LPAD(r.reservation_id, 3, '0')) AS ref_number,
        TIMESTAMPDIFF(MINUTE, NOW(), r.reservation_expiry) AS minutes_until_expiry
    FROM reservations r
    JOIN parking_slots ps ON ps.slot_id = r.slot_id
    WHERE r.user_id = ?
      AND r.status  = 'active'
    ORDER BY r.time_reserved DESC
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$active_reservation = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

mysqli_close($conn);

echo json_encode([
    'success'           => true,
    'slots'             => $slots,
    'availability'      => $availability,
    'stats'             => $stats,
    'active_reservation'=> $active_reservation ?: null,
]);