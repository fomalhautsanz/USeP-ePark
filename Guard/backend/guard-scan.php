<?php
// Guard/backend/guard-scan.php
// Handles QR scan for entry/exit.
//
// The QR code on each vehicle contains the vehicle's user_code from the `users` table.
// (The user registers → gets a user_code like CUS-2026-0001 → QR is generated from that)
//
// Logic:
//   - Find the vehicle by the scanned user_code (via users → vehicle join)
//   - If no open entry_exit_logs row (time_out IS NULL) → record ENTRY
//   - If open row exists → record EXIT

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config/database.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'action' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$qr_data  = trim($input['qr_data']  ?? '');
$guard_id = intval($input['guard_id'] ?? 0);

if (!$qr_data) {
    echo json_encode(['success' => false, 'action' => 'error', 'message' => 'No QR data received.']);
    exit;
}

// ── 1. Find the vehicle via user_code ───────────────────────────────────────
// The QR code stores the user's user_code (e.g. CUS-2026-0001)
// We join users → vehicle to get the vehicle info
$stmt = $conn->prepare("
    SELECT
        v.vehicle_id,
        v.plate_number,
        v.vehicle_type,
        CONCAT(u.firstname, ' ', u.lastname) AS owner_name,
        u.status AS user_status
    FROM users u
    JOIN vehicle v ON v.user_id = u.user_id
    WHERE u.user_code = ?
    LIMIT 1
");
$stmt->bind_param("s", $qr_data);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$vehicle) {
    echo json_encode([
        'success' => false,
        'action'  => 'error',
        'message' => 'Vehicle not found. QR code is not registered in the system.'
    ]);
    $conn->close();
    exit;
}

if ($vehicle['user_status'] === 'suspended') {
    echo json_encode([
        'success' => false,
        'action'  => 'error',
        'message' => 'This account is suspended. Entry denied.'
    ]);
    $conn->close();
    exit;
}

$vehicle_id = $vehicle['vehicle_id'];
$now        = date('Y-m-d H:i:s');

// ── 2. Check for an open parking session ────────────────────────────────────
// entry_exit_logs uses: log_id, vehicle_id, slot_id, time_in, time_out
$stmt = $conn->prepare("
    SELECT log_id, slot_id, time_in
    FROM entry_exit_logs
    WHERE vehicle_id = ? AND time_out IS NULL
    ORDER BY time_in DESC
    LIMIT 1
");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$open_log = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$open_log) {
    // ── ENTRY ────────────────────────────────────────────────────────────────
    // Find the first available slot
    $slot_result = $conn->query("
        SELECT slot_id, slot_number
        FROM parking_slots
        WHERE status = 'available'
        ORDER BY CAST(SUBSTRING_INDEX(slot_number, '-', -1) AS UNSIGNED) ASC
        LIMIT 1
    ");
    $slot        = $slot_result ? $slot_result->fetch_assoc() : null;
    $slot_id     = $slot['slot_id']     ?? null;
    $slot_number = $slot['slot_number'] ?? null;

    // Insert entry log
    $stmt = $conn->prepare("
        INSERT INTO entry_exit_logs (vehicle_id, slot_id, time_in)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $vehicle_id, $slot_id, $now);
    $stmt->execute();
    $stmt->close();

    // Mark slot as occupied
    if ($slot_id) {
        $upd = $conn->prepare("UPDATE parking_slots SET status = 'occupied' WHERE slot_id = ?");
        $upd->bind_param("i", $slot_id);
        $upd->execute();
        $upd->close();
    }

    echo json_encode([
        'success'      => true,
        'action'       => 'entry',
        'message'      => 'Entry recorded successfully.',
        'vehicle_type' => ucfirst($vehicle['vehicle_type']),
        'plate_number' => $vehicle['plate_number'],
        'owner_name'   => $vehicle['owner_name'],
        'slot_number'  => $slot_number ?? 'No slot assigned',
        'timestamp'    => date('h:i A', strtotime($now)),
    ]);

} else {
    // ── EXIT ─────────────────────────────────────────────────────────────────
    $log_id  = $open_log['log_id'];
    $slot_id = $open_log['slot_id'];

    // Close the log entry with time_out
    $stmt = $conn->prepare("
        UPDATE entry_exit_logs
        SET time_out = ?
        WHERE log_id = ?
    ");
    $stmt->bind_param("si", $now, $log_id);
    $stmt->execute();
    $stmt->close();

    // Free the slot
    if ($slot_id) {
        $upd = $conn->prepare("UPDATE parking_slots SET status = 'available' WHERE slot_id = ?");
        $upd->bind_param("i", $slot_id);
        $upd->execute();
        $upd->close();

        $sr          = $conn->query("SELECT slot_number FROM parking_slots WHERE slot_id = $slot_id");
        $slot_number = $sr ? $sr->fetch_assoc()['slot_number'] : null;
    }

    // Calculate duration
    $entry = new DateTime($open_log['time_in']);
    $exit  = new DateTime($now);
    $diff  = $entry->diff($exit);
    $dur   = ($diff->h > 0 ? $diff->h . 'h ' : '') . $diff->i . 'm';

    echo json_encode([
        'success'      => true,
        'action'       => 'exit',
        'message'      => 'Exit recorded. Duration: ' . $dur,
        'vehicle_type' => ucfirst($vehicle['vehicle_type']),
        'plate_number' => $vehicle['plate_number'],
        'owner_name'   => $vehicle['owner_name'],
        'slot_number'  => $slot_number ?? '–',
        'timestamp'    => date('h:i A', strtotime($now)),
        'duration'     => $dur,
    ]);
}

$conn->close();
