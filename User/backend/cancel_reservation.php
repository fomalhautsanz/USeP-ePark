<?php
// ============================================================================
// User/backend/cancel_reservation.php
// Lets a user cancel their own active reservation.
// Delegates to sp_cancel_reservation which:
//   - Verifies ownership (user_id must match)
//   - Verifies status is 'active'
//   - Sets status → 'cancelled'
//   - trg_release_slot_on_cancelled_reservation fires and frees the slot
// ============================================================================

session_start();
header('Content-Type: application/json');

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
$input          = json_decode(file_get_contents('php://input'), true);
$reservation_id = isset($input['reservation_id']) ? (int) $input['reservation_id'] : 0;

if (!$reservation_id) {
    echo json_encode(['success' => false, 'message' => 'No reservation specified.']);
    mysqli_close($conn);
    exit;
}

// ── Call sp_cancel_reservation ───────────────────────────────────────────────
$stmt = mysqli_prepare($conn, "CALL sp_cancel_reservation(?, ?, @p_message)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'System error (S1).']);
    mysqli_close($conn);
    exit;
}

mysqli_stmt_bind_param($stmt, 'ii', $reservation_id, $user_id);

if (!mysqli_stmt_execute($stmt)) {
    error_log('[cancel_reservation] execute failed: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    echo json_encode(['success' => false, 'message' => 'System error (S2).']);
    exit;
}
mysqli_stmt_close($stmt);

// ── Read OUT parameter ───────────────────────────────────────────────────────
$out    = mysqli_query($conn, "SELECT @p_message AS message");
$row    = mysqli_fetch_assoc($out);
$msg    = $row['message'] ?? 'Unknown error.';

mysqli_close($conn);

// sp_cancel_reservation prefixes success messages with 'SUCCESS:'
$success = str_starts_with($msg, 'SUCCESS:');
$clean   = trim(preg_replace('/^(SUCCESS|ERROR):\s*/', '', $msg));

echo json_encode([
    'success' => $success,
    'message' => $clean,
]);