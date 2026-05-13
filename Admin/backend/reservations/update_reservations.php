<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

include '../../config/database.php';


function json_fail(string $message, string $internal = '', int $http = 400): never
{
    if ($internal) error_log('[update_reservation] ' . $internal);
    http_response_code($http);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

function json_ok(string $message, array $extra = []): never
{
    echo json_encode(array_merge(['success' => true, 'message' => $message], $extra));
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_fail('Method not allowed.', '', 405);
}

if (!$conn) {
    json_fail('Service unavailable.', 'DB connection failed', 503);
}


$input = [];
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';

if (str_contains($content_type, 'application/json')) {
    $raw   = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];
} else {
    $input = $_POST;
}

// reservation_id — must be a positive integer
$reservation_id = filter_var($input['reservation_id'] ?? '', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);
if ($reservation_id === false || $reservation_id === null) {
    json_fail('Invalid reservation ID.');
}

// action — strict allowlist only
$allowed_actions = ['approve', 'deny', 'cancel'];
$action = trim($input['action'] ?? '');
if (!in_array($action, $allowed_actions, true)) {
    json_fail('Invalid action. Must be approve, deny, or cancel.');
}


$check = $conn->prepare("
    SELECT 
        reservation_id, 
        status, 
        slot_id, 
        user_id,
        ref_number
    FROM view_reservations_full
    WHERE reservation_id = ?
    LIMIT 1
");

if (!$check) {
    json_fail('Service unavailable.', 'check prepare failed: ' . $conn->error, 503);
}

$check->bind_param('i', $reservation_id);
$check->execute();
$check->bind_result($found_id, $current_status, $slot_id, $user_id, $ref_number);
$check->fetch();
$check->close();

if (!$found_id) {
    json_fail('Reservation not found.', "reservation_id=$reservation_id not found", 404);
}


$valid_transitions = [
    'active' => ['approve', 'deny', 'cancel'],
    'completed' => [],  // No further changes
    'cancelled' => [],  // No further changes
    'expired' => ['cancel'],  // Can cancel expired reservations
];

if (!isset($valid_transitions[$current_status]) || !in_array($action, $valid_transitions[$current_status])) {
    json_fail(
        "Cannot $action a reservation with status '$current_status'.",
        "invalid transition: $current_status -> $action"
    );
}


$result_message = '';
$new_status = '';

switch ($action) {
    case 'approve':
        // ─────────────────────────────────────────────────────────────────
        // Direct UPDATE to 'completed' (no procedure needed)
        // Triggers fire to handle any automatic slot marking
        // ─────────────────────────────────────────────────────────────────
        $update = $conn->prepare("
            UPDATE reservations
            SET status = 'completed'
            WHERE reservation_id = ?
              AND status = 'active'
        ");
        
        if (!$update) {
            json_fail('Service unavailable.', 'approve prepare failed: ' . $conn->error, 503);
        }
        
        $update->bind_param('i', $reservation_id);
        $update->execute();
        $affected = $update->affected_rows;
        $update->close();
        
        if ($affected === 0) {
            json_fail('Reservation was already updated. Please refresh.');
        }
        
        $new_status = 'completed';
        $result_message = "Reservation $ref_number has been approved.";
        break;

    case 'deny':
    case 'cancel':
        // ─────────────────────────────────────────────────────────────────
        // Use sp_cancel_reservation for deny/cancel
        // CALL sp_cancel_reservation (
        //     IN  p_reservation_id INT,
        //     IN  p_user_id        INT,
        //     OUT p_message        VARCHAR(255)
        // );
        // ─────────────────────────────────────────────────────────────────
        $proc = $conn->prepare("CALL sp_cancel_reservation(?, ?, @proc_message)");
        
        if (!$proc) {
            json_fail('Service unavailable.', 'cancel prepare failed: ' . $conn->error, 503);
        }
        
        $proc->bind_param('ii', $reservation_id, $user_id);
        
        if (!$proc->execute()) {
            json_fail('Service unavailable.', 'cancel execute failed: ' . $proc->error, 503);
        }
        
        $proc->close();
        
        // Retrieve procedure output message
        $msg_result = $conn->query("SELECT @proc_message AS message");
        if (!$msg_result) {
            json_fail('Service unavailable.', 'message retrieval failed', 503);
        }
        
        $msg_row = $msg_result->fetch_assoc();
        $proc_message = $msg_row['message'] ?? '';
        
        // Check if procedure succeeded
        if (strpos($proc_message, 'SUCCESS') !== 0) {
            json_fail($proc_message, "procedure returned: $proc_message", 400);
        }
        
        $new_status = 'cancelled';
        $result_message = "Reservation $ref_number has been " . ($action === 'deny' ? 'denied' : 'cancelled') . ".";
        break;
}

$conn->close();

json_ok(
    $result_message,
    [
        'reservation_id' => $reservation_id,
        'ref_number'     => $ref_number,
        'new_status'     => $new_status,
        'action'         => $action,
    ]
);