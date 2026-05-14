<?php
// ============================================================================
// update_reservation.php — Handle approve/deny actions
// ============================================================================
// POST endpoint that updates a reservation status from 'active' to either:
//   - 'completed' (approve)
//   - 'cancelled' (deny)
// ============================================================================

ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

include '../../config/database.php';

// ────────────────────────────────────────────────────────────────────────────
// 1. Validate database connection
// ────────────────────────────────────────────────────────────────────────────

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Service unavailable.'
    ]);
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// 2. Get and validate input
// ────────────────────────────────────────────────────────────────────────────

$input = json_decode(file_get_contents('php://input'), true);

$reservation_id = isset($input['reservation_id']) ? (int)$input['reservation_id'] : null;
$action         = isset($input['action'])         ? trim($input['action'])      : null;

if (!$reservation_id || !$action) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing or invalid parameters.'
    ]);
    exit;
}

// Validate action
if (!in_array($action, ['approve', 'deny'], true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action. Must be "approve" or "deny".'
    ]);
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// 3. Check reservation exists and is active
// ────────────────────────────────────────────────────────────────────────────

$stmt = $conn->prepare("SELECT status FROM reservations WHERE reservation_id = ?");
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error.'
    ]);
    exit;
}

$stmt->bind_param('i', $reservation_id);
$stmt->execute();
$result = $stmt->get_result();
$row    = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode([
        'success' => false,
        'message' => 'Reservation not found.'
    ]);
    exit;
}

if ($row['status'] !== 'active') {
    echo json_encode([
        'success' => false,
        'message' => 'Reservation is not in pending status. Cannot ' . $action . '.'
    ]);
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// 4. Update reservation status
// ────────────────────────────────────────────────────────────────────────────

$new_status = ($action === 'approve') ? 'completed' : 'cancelled';

$stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error.'
    ]);
    exit;
}

$stmt->bind_param('si', $new_status, $reservation_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    echo json_encode([
        'success'    => true,
        'message'    => ucfirst($action) . ' successfully.',
        'new_status' => $new_status
    ]);
} else {
    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => false,
        'message' => 'Failed to update reservation.'
    ]);
}
?>