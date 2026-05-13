<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Login/login.html');
    exit;
}
if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff') {
    header('Location: ../Admin/dashboard.php');
    exit;
}

header('Content-Type: application/json');
require_once __DIR__ . '/../Login/backend/config/database.php';

$user_id      = $_SESSION['user_id'];
$firstname    = trim($_POST['firstname'] ?? '');
$lastname     = trim($_POST['lastname'] ?? '');
$email        = trim($_POST['email'] ?? '');
$contact      = trim($_POST['contact_number'] ?? '');
$gender       = trim($_POST['gender'] ?? '');
$birthdate    = trim($_POST['birthdate'] ?? '') ?: null;
$vehicle_type = strtolower(trim($_POST['vehicle_type'] ?? ''));
$plate_number = strtoupper(trim($_POST['plate_number'] ?? ''));

// ── Handle profile picture upload ──
$profile_picture = $_SESSION['profile_picture'] ?? null;

if (!empty($_FILES['profile_picture']['name'])) {
    $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext      = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type.']);
        exit;
    }
    if ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image too large. Max 2MB.']);
        exit;
    }

    $newFilename = 'user_' . $user_id . '_' . time() . '.' . $ext;
    $uploadPath  = __DIR__ . '/../User/assets/uploads/' . $newFilename;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
        // delete old picture if exists
        if (!empty($_SESSION['profile_picture'])) {
            $oldFile = __DIR__ . '/../User/assets/uploads/' . $_SESSION['profile_picture'];
            if (file_exists($oldFile)) unlink($oldFile);
        }
        $profile_picture = $newFilename;
    }
}

// ── Update users table ──
$stmt = mysqli_prepare($conn,
    "UPDATE users SET firstname=?, lastname=?, email=?, contact_number=?, gender=?, birthdate=?, profile_picture=? WHERE user_id=?"
);
mysqli_stmt_bind_param($stmt, 'sssssssi',
    $firstname, $lastname, $email, $contact, $gender, $birthdate, $profile_picture, $user_id
);

if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
    exit;
}
mysqli_stmt_close($stmt);

// ── Update vehicle table ──
$stmt2 = mysqli_prepare($conn,
    "INSERT INTO vehicle (user_id, vehicle_type, plate_number) 
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE vehicle_type=VALUES(vehicle_type), plate_number=VALUES(plate_number)"
);
mysqli_stmt_bind_param($stmt2, 'iss', $user_id, $vehicle_type, $plate_number);
mysqli_stmt_execute($stmt2);
mysqli_stmt_close($stmt2);

// ── Update session ──
$_SESSION['firstname']       = $firstname;
$_SESSION['lastname']        = $lastname;
$_SESSION['email']           = $email;
$_SESSION['contact_number']  = $contact;
$_SESSION['gender']          = $gender;
$_SESSION['birthdate']       = $birthdate;
$_SESSION['vehicle_type']    = $vehicle_type;
$_SESSION['plate_number']    = $plate_number;
$_SESSION['profile_picture'] = $profile_picture;

mysqli_close($conn);

echo json_encode(['success' => true, 'message' => 'Profile updated!']);
?>