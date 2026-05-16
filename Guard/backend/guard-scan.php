<?php
// Guard/backend/guard-scan.php
// Handles QR scan for vehicle entry / exit.
// All business logic (vehicle lookup, open-session check, slot assignment,
// entry/exit recording) is delegated to sp_guard_process_scan which:
//   - Reads via  view_guard_vehicle_lookup  (SELECT via view)
//   - Writes via INSERT / UPDATE inside the procedure
//   - Lets the existing DB triggers handle fee calculation and slot status
// This file's only job: receive the JSON payload, call the procedure,
// return the result as JSON.

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config/database.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'action' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// ── 1. Parse input ───────────────────────────────────────────────────────────
$input    = json_decode(file_get_contents('php://input'), true);
$qr_raw   = trim($input['qr_data']  ?? '');
$guard_id = intval($input['guard_id'] ?? 0);

if (!$qr_raw) {
    echo json_encode(['success' => false, 'action' => 'error', 'message' => 'No QR data received.']);
    exit;
}

// ── 2. Extract token from QR payload ────────────────────────────────────────
// The QR encodes a JSON object: {"token":"abc123...","user_id":1,"plate_number":"CMD008"}
// The stored procedure expects just the token string, not the full JSON blob.
// FIX: parse the JSON and pull out the token before calling the procedure.
$qr_decoded = json_decode($qr_raw, true);
// $qr_decoded['token'] = the actual token string ✓
$qr_data = trim($qr_decoded['token']);

if (is_array($qr_decoded) && !empty($qr_decoded['token'])) {
    // Normal path: QR contains a JSON object with a token field
    $qr_data = trim($qr_decoded['token']);
} else {
    // Fallback: QR was encoded as a raw token string (old format)
    $qr_data = $qr_raw;
}

if (empty($qr_data) || $qr_data === 'null') {
    echo json_encode(['success' => false, 'action' => 'error', 'message' => 'Invalid QR code — missing token.']);
    exit;
}

// ── 3. Call stored procedure ─────────────────────────────────────────────────
$stmt = $conn->prepare("CALL sp_guard_process_scan(?, ?, @success, @action, @message, @plate, @vtype, @owner, @slot, @ts, @dur, @fee)");
if (!$stmt) {
    error_log('[guard-scan] prepare failed: ' . $conn->error);
    echo json_encode(['success' => false, 'action' => 'error', 'message' => 'System error (S1).']);
    exit;
}

$stmt->bind_param('si', $qr_data, $guard_id);

if (!$stmt->execute()) {
    error_log('[guard-scan] execute failed: ' . $stmt->error);
    $stmt->close();
    echo json_encode(['success' => false, 'action' => 'error', 'message' => 'System error (S2).']);
    exit;
}
$stmt->close();

// ── 4. Read OUT parameters ───────────────────────────────────────────────────
$out = $conn->query("SELECT
    @success AS success,
    @action  AS action,
    @message AS message,
    @plate   AS plate_number,
    @vtype   AS vehicle_type,
    @owner   AS owner_name,
    @slot    AS slot_number,
    @ts      AS timestamp,
    @dur     AS duration,
    @fee     AS fee
");

if (!$out) {
    error_log('[guard-scan] OUT param read failed: ' . $conn->error);
    echo json_encode(['success' => false, 'action' => 'error', 'message' => 'System error (S3).']);
    $conn->close();
    exit;
}

$row = $out->fetch_assoc();
$conn->close();

// ── 5. Return response ───────────────────────────────────────────────────────
echo json_encode([
    'success'      => (bool)(int)($row['success'] ?? 0),
    'action'       => $row['action']       ?? 'error',
    'message'      => $row['message']      ?? 'Unknown error.',
    'plate_number' => $row['plate_number'] ?? null,
    'vehicle_type' => $row['vehicle_type'] ?? null,
    'owner_name'   => $row['owner_name']   ?? null,
    'slot_number'  => $row['slot_number']  ?? null,
    'timestamp'    => $row['timestamp']    ?? null,
    'duration'     => $row['duration']     ?? null,
    'fee'          => $row['fee']          ?? null,   // NEW
]);