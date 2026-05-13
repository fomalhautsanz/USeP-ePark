<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

include '../../config/database.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// ============================================================================
// Validate Report Type
// ============================================================================

$allowed_types = ['daily', 'monthly', 'vehicle', 'revenue', 'slots', 'summary'];
$type = trim($_GET['type'] ?? '');

if (!in_array($type, $allowed_types, true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid report type. Allowed: ' . implode(', ', $allowed_types)]);
    exit;
}

// ============================================================================
// Validate Dates (except for slots, which needs no date params)
// ============================================================================

function isValidDate(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

$dateFrom = $_GET['from'] ?? date('Y-m-01');
$dateTo   = $_GET['to']   ?? date('Y-m-t');

if (!isValidDate($dateFrom) || !isValidDate($dateTo)) {
    echo json_encode(['success' => false, 'error' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

if ($dateFrom > $dateTo) {
    echo json_encode(['success' => false, 'error' => 'Date "from" must be before or equal to "to"']);
    exit;
}

// ============================================================================
// Handle 'slots' Report (No date params needed)
// ============================================================================

if ($type === 'slots') {
    $result = $conn->query("SELECT * FROM view_slot_availability");
    
    if (!$result) {
        error_log("Query failed: " . $conn->error);
        echo json_encode(['success' => false, 'error' => 'System error']);
        exit;
    }
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'location_area' => $row['location_area'],
            'total_slots' => (int)$row['total_slots'],
            'available_slots' => (int)$row['available_slots'],
            'occupied_slots' => (int)$row['occupied_slots'],
            'reserved_slots' => (int)$row['reserved_slots'],
            'availability_percentage' => (float)$row['availability_percentage'],
        ];
    }
    
    $conn->close();
    echo json_encode([
        'success' => true,
        'report_type' => 'slots',
        'count' => count($data),
        'data' => $data
    ]);
    exit;
}

// ============================================================================
// Dispatch to Appropriate Procedure/Query
// ============================================================================

switch ($type) {

    // ────────────────────────────────────────────────────────────────────
    // DAILY REPORT — uses sp_get_daily_report
    // ────────────────────────────────────────────────────────────────────
    case 'daily':
        $proc = $conn->prepare("CALL sp_get_daily_report(?, ?)");
        
        if (!$proc) {
            error_log("Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'error' => 'System error']);
            exit;
        }
        
        $proc->bind_param("ss", $dateFrom, $dateTo);
        
        if (!$proc->execute()) {
            error_log("Execute failed: " . $proc->error);
            echo json_encode(['success' => false, 'error' => 'System error']);
            exit;
        }
        
        $result = $proc->get_result();
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'report_date' => $row['report_date'],
                'total_entries' => (int)$row['total_entries'],
                'total_exits' => (int)$row['total_exits'],
                'total_denied' => (int)$row['total_denied'],
                'unique_vehicles' => (int)$row['unique_vehicles'],
                'unique_users' => (int)$row['unique_users'],
                'total_fees_collected' => (float)$row['total_fees_collected'],
                'avg_duration_hours' => (float)$row['avg_duration_hours'],
            ];
        }
        
        $proc->close();
        break;

    // ────────────────────────────────────────────────────────────────────
    // MONTHLY REPORT — use view_monthly_parking_stats
    // ────────────────────────────────────────────────────────────────────
    case 'monthly':
        $stmt = $conn->prepare("
            SELECT
                year,
                month,
                total_parking_sessions,
                unique_vehicles,
                unique_users,
                total_parking_fees,
                avg_parking_duration
            FROM view_monthly_parking_stats
            WHERE year >= YEAR(?)
              AND year <= YEAR(?)
              AND (year < YEAR(?) OR (year = YEAR(?) AND month >= MONTH(?)))
              AND (year > YEAR(?) OR (year = YEAR(?) AND month <= MONTH(?)))
            ORDER BY year DESC, month DESC
        ");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'error' => 'System error']);
            exit;
        }
        
        $stmt->bind_param("ssssssss", $dateFrom, $dateTo, $dateFrom, $dateFrom, $dateFrom, $dateTo, $dateTo, $dateTo);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            echo json_encode(['success' => false, 'error' => 'System error']);
            exit;
        }
        
        $result = $stmt->get_result();
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'year' => (int)$row['year'],
                'month' => (int)$row['month'],
                'total_parking_sessions' => (int)$row['total_parking_sessions'],
                'unique_vehicles' => (int)$row['unique_vehicles'],
                'unique_users' => (int)$row['unique_users'],
                'total_parking_fees' => (float)$row['total_parking_fees'],
                'avg_parking_duration' => (float)$row['avg_parking_duration'],
            ];
        }
        
        $stmt->close();
        break;

    // ────────────────────────────────────────────────────────────────────
    // VEHICLE REPORT — direct query (no procedure available)
    // ────────────────────────────────────────────────────────────────────
    case 'vehicle':
        $stmt = $conn->prepare("
            SELECT
                v.vehicle_id,
                v.plate_number,
                CONCAT(u.firstname, ' ', u.lastname) AS owner,
                u.user_code,
                v.vehicle_type,
                COUNT(eel.log_id)                    AS total_entries,
                COALESCE(SUM(eel.total_duration), 0) AS total_hours,
                COALESCE(SUM(eel.parking_fee), 0)    AS total_fee,
                COALESCE(AVG(eel.total_duration), 0) AS avg_duration,
                MAX(eel.time_in)                     AS last_used
            FROM entry_exit_logs eel
            JOIN vehicle v ON eel.vehicle_id = v.vehicle_id
            JOIN users u   ON v.user_id      = u.user_id
            WHERE eel.log_status = 'out'
              AND DATE(eel.time_in) BETWEEN ? AND ?
            GROUP BY v.vehicle_id, v.plate_number, u.firstname, u.lastname, u.user_code, v.vehicle_type
            ORDER BY total_entries DESC
        ");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'error' => 'System error']);
            exit;
        }
        
        $stmt->bind_param("ss", $dateFrom, $dateTo);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            echo json_encode(['success' => false, 'error' => 'System error']);
            exit;
        }
        
        $result = $stmt->get_result();
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'vehicle_id' => (int)$row['vehicle_id'],
                'plate_number' => $row['plate_number'],
                'owner' => $row['owner'],
                'user_code' => $row['user_code'],
                'vehicle_type' => $row['vehicle_type'],
                'total_entries' => (int)$row['total_entries'],
                'total_hours' => (float)$row['total_hours'],
                'total_fee' => (float)$row['total_fee'],
                'avg_duration' => (float)$row['avg_duration'],
                'last_used' => $row['last_used'],
            ];
        }
        
        $stmt->close();
        break;

    // ────────────────────────────────────────────────────────────────────
    // REVENUE REPORT — uses sp_get_revenue_summary
    // ────────────────────────────────────────────────────────────────────
    case 'revenue':
        $proc = $conn->prepare("CALL sp_get_revenue_summary(?, ?)");
        
        if (!$proc) {
            error_log("Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'error' => 'System error']);
            exit;
        }
        
        $proc->bind_param("ss", $dateFrom, $dateTo);
        
        if (!$proc->execute()) {
            error_log("Execute failed: " . $proc->error);
            echo json_encode(['success' => false, 'error' => 'System error']);
            exit;
        }
        
        $result = $proc->get_result();
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'total_transactions' => (int)$row['total_transactions'],
                'total_sessions' => (int)$row['total_sessions'],
                'unique_users' => (int)$row['unique_users'],
                'total_revenue' => (float)$row['total_revenue'],
                'avg_payment' => (float)$row['avg_payment'],
                'avg_duration_hours' => (float)$row['avg_duration_hours'],
                'first_transaction' => $row['first_transaction'],
                'last_transaction' => $row['last_transaction'],
            ];
        }
        
        $proc->close();
        break;

    // ────────────────────────────────────────────────────────────────────
    // SUMMARY REPORT — current period totals
    // ────────────────────────────────────────────────────────────────────
    case 'summary':
        $stmt = $conn->prepare("
            SELECT
                COUNT(*)                             AS total_entries,
                COALESCE(SUM(parking_fee), 0)        AS revenue,
                COALESCE(AVG(total_duration), 0)     AS avg_duration,
                COUNT(DISTINCT vehicle_id)          AS unique_vehicles,
                COUNT(DISTINCT COALESCE(reservation_id, 0)) AS reserved_entries
            FROM entry_exit_logs
            WHERE log_status = 'out'
              AND DATE(time_in) BETWEEN ? AND ?
        ");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'error' => 'System error']);
            exit;
        }
        
        $stmt->bind_param("ss", $dateFrom, $dateTo);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            echo json_encode(['success' => false, 'error' => 'System error']);
            exit;
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $data = [[
            'total_entries' => (int)$row['total_entries'],
            'revenue' => (float)$row['revenue'],
            'avg_duration' => (float)$row['avg_duration'],
            'unique_vehicles' => (int)$row['unique_vehicles'],
            'reserved_entries' => (int)$row['reserved_entries'],
        ]];
        
        $stmt->close();
        break;
}

$conn->close();

// ============================================================================
// Return JSON Response
// ============================================================================

echo json_encode([
    'success' => true,
    'report_type' => $type,
    'period' => "from_$dateFrom to_$dateTo",
    'count' => count($data),
    'data' => $data
]);
?>