<?php
include '../../config/database.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// ── Inputs ───────────────────────────────────────────────────────────────────
$user_id   = $_POST['user_id']         ?? '';
$firstname = trim($_POST['first_name'] ?? '');
$lastname  = trim($_POST['last_name']  ?? '');
$email     = trim($_POST['email']      ?? '');
$phone     = trim($_POST['phone']      ?? '');
$role      = $_POST['role']            ?? '';
$password  = $_POST['password']        ?? '';
$profile_picture = null;


// ── Validation ───────────────────────────────────────────────────────────────
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext     = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image format']);
        exit;
    }

    $filename = 'user_' . uniqid() . '.' . $ext;
    $dest = __DIR__ . '/../../../User/assets/uploads/' . $filename;

    if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save profile picture']);
        exit;
    }

    $profile_picture = $filename;
}

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
    exit;
}

if (!$firstname || !$lastname || !$email || !$phone || !$role) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!in_array($role, ['customer', 'staff', 'admin'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// ── Hash password only if a new one was provided, otherwise pass NULL ─────────
// The procedure skips updating password_hash when it receives NULL,
// so the existing hash in the DB is preserved.
$password_hash = !empty($password)
    ? password_hash($password, PASSWORD_DEFAULT)
    : null;

// ── Call sp_update_user_full ──────────────────────────────────────────────────
$stmt = $conn->prepare("
    CALL sp_update_user_full(?, ?, ?, ?, ?, ?, ?, @p_message)
");

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error (S1)']);
    exit;
}

$stmt->bind_param(
    "issssss",
    $user_id,
    $firstname,
    $lastname,
    $email,
    $phone,
    $role,
    $password_hash
);

if (!$stmt->execute()) {
    error_log("Procedure call failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Update failed']);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

$outStmt = $conn->query("SELECT @p_message AS message");

if (!$outStmt) {
    error_log("Failed to read OUT param: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'System error (S2)']);
    $conn->close();
    exit;
}

$result = $outStmt->fetch_assoc();
$outStmt->close();


// ── Respond ───────────────────────────────────────────────────────────────────
if ($result && str_starts_with($result['message'], 'SUCCESS')) {

    // ── Update profile picture if a new one was uploaded ─────────────────────
    if ($profile_picture) {
        // Delete old picture from disk first
        $oldStmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
        if ($oldStmt) {
            $oldStmt->bind_param("i", $user_id);
            $oldStmt->execute();
            $oldStmt->bind_result($oldPic);
            $oldStmt->fetch();
            $oldStmt->close();

            if ($oldPic) {
                $oldPath = __DIR__ . '/../../User/assets/uploads/' . $oldPic;
                if (file_exists($oldPath)) unlink($oldPath);
            }
        }

        $picStmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        if ($picStmt) {
            $picStmt->bind_param("si", $profile_picture, $user_id);
            $picStmt->execute();
            $picStmt->close();
        }
    }

    echo json_encode(['success' => true]);
} else {
    $errMsg = isset($result['message'])
        ? preg_replace('/^ERROR:\s*/i', '', $result['message'])
        : 'Update failed';
    error_log("sp_update_user_full error: " . ($result['message'] ?? 'null'));
    echo json_encode(['success' => false, 'message' => $errMsg]);
}

$conn->close();

?>