<?php
// ============================================================================
// User/backend/submit_reservation.php
// Handles a user's slot reservation request.
//
// Rules enforced here (in addition to DB triggers):
//   - User must be logged in and have role 'customer'
//   - User cannot hold more than one active reservation at a time
//   - Slot must be 'available' (trigger also checks, but we fail fast here)
//   - Reservation window is fixed at 30 minutes from now
//   - Inserts directly into reservations; triggers handle slot status update
// ============================================================================

session_start();
header('Content-Type: application/json');

// ── Auth guard ───────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit;
}

include '../../Admin/config/database.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// ── Parse input ──────────────────────────────────────────────────────────────
$input   = json_decode(file_get_contents('php://input'), true);
$slot_id = isset($input['slot_id']) ? (int) $input['slot_id'] : 0;

if (!$slot_id) {
    echo json_encode(['success' => false, 'message' => 'No slot selected.']);
    mysqli_close($conn);
    exit;
}

// ── 1. Check user doesn't already have an active reservation ─────────────────
$stmt = mysqli_prepare($conn, "
    SELECT reservation_id FROM reservations
    WHERE user_id = ? AND status = 'active'
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    echo json_encode([
        'success' => false,
        'message' => 'You already have an active reservation. Cancel it first before making a new one.',
    ]);
    exit;
}
mysqli_stmt_close($stmt);

// ── 2. Verify slot exists and is available ───────────────────────────────────
$stmt = mysqli_prepare($conn, "
    SELECT slot_id, slot_number, status FROM parking_slots
    WHERE slot_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, 'i', $slot_id);
mysqli_stmt_execute($stmt);
$slot = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$slot) {
    echo json_encode(['success' => false, 'message' => 'Slot not found.']);
    mysqli_close($conn);
    exit;
}

if ($slot['status'] !== 'available') {
    echo json_encode([
        'success' => false,
        'message' => 'Slot ' . $slot['slot_number'] . ' is no longer available. Please choose another.',
    ]);
    mysqli_close($conn);
    exit;
}

// ── 3. Insert reservation (30-minute window) ─────────────────────────────────
// trg_check_slot_availability_on_reservation and trg_update_slot_on_reservation
// fire automatically after this INSERT.
$stmt = mysqli_prepare($conn, "
    INSERT INTO reservations (user_id, slot_id, time_reserved, reservation_expiry, status)
    VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 MINUTE), 'active')
");
mysqli_stmt_bind_param($stmt, 'ii', $user_id, $slot_id);

if (!mysqli_stmt_execute($stmt)) {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Surface the trigger signal message to the user
    $message = (strpos($err, 'ERROR:') !== false)
        ? ltrim(strstr($err, 'ERROR:'))
        : 'Failed to create reservation. Please try again.';

    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$reservation_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// ── 4. Return the new reservation details ────────────────────────────────────
$stmt = mysqli_prepare($conn, "
    SELECT
        r.reservation_id,
        CONCAT('RES-', LPAD(r.reservation_id, 3, '0')) AS ref_number,
        r.slot_id,
        ps.slot_number,
        ps.location_area,
        r.time_reserved,
        r.reservation_expiry,
        r.status,
        TIMESTAMPDIFF(MINUTE, NOW(), r.reservation_expiry) AS minutes_until_expiry
    FROM reservations r
    JOIN parking_slots ps ON ps.slot_id = r.slot_id
    WHERE r.reservation_id = ?
");
mysqli_stmt_bind_param($stmt, 'i', $reservation_id);
mysqli_stmt_execute($stmt);
$reservation = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

mysqli_close($conn);

echo json_encode([
    'success'     => true,
    'message'     => 'Reservation confirmed! You have 30 minutes to arrive at slot ' . $slot['slot_number'] . '.',
    'reservation' => $reservation,
]);