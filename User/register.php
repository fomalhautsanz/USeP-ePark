<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: userDashboard.php');
    exit;
}

$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    require_once __DIR__ . '/../Login/backend/config/database.php';

    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'DB connection failed.']);
        exit;
    }

    $firstname      = trim($_POST['firstname'] ?? '');
    $lastname       = trim($_POST['lastname'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $password       = $_POST['password'] ?? '';
    $plate_number   = strtoupper(trim($_POST['plate_number'] ?? ''));
    $vehicle_type   = trim($_POST['vehicle_type'] ?? '');

    // ── Username field removed; user_code is now auto-generated ──

    if (!$firstname || !$lastname || !$email || !$contact_number || !$password || !$plate_number || !$vehicle_type) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
        exit;
    }

    // ── Check duplicate email ──
    $checkEmail = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($checkEmail, 's', $email);
    mysqli_stmt_execute($checkEmail);
    mysqli_stmt_store_result($checkEmail);
    if (mysqli_stmt_num_rows($checkEmail) > 0) {
        echo json_encode(['success' => false, 'message' => 'Email is already registered.']);
        mysqli_stmt_close($checkEmail);
        exit;
    }
    mysqli_stmt_close($checkEmail);

    // ── Auto-generate user_code in CUS-YYYY-XXXX format ──
    $year        = date('Y');
    $likePattern = "CUS-$year-%";

    $seqStmt = mysqli_prepare($conn, "
        SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(user_code, '-', -1) AS UNSIGNED)), 0)
        FROM users
        WHERE role = 'customer' AND user_code LIKE ?
    ");
    if (!$seqStmt) {
        echo json_encode(['success' => false, 'message' => 'System error (code generation failed).']);
        exit;
    }
    mysqli_stmt_bind_param($seqStmt, 's', $likePattern);
    mysqli_stmt_execute($seqStmt);
    mysqli_stmt_bind_result($seqStmt, $maxSeq);
    mysqli_stmt_fetch($seqStmt);
    mysqli_stmt_close($seqStmt);

    $sequence  = str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    $user_code = "CUS-$year-$sequence";

    // ── Hash password & set defaults ──
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $role   = 'customer';
    $status = 'active';

    // ── Insert user (user_code replaces the old username field) ──
    $insertUser = mysqli_prepare($conn, "
        INSERT INTO users
            (firstname, lastname, email, contact_number, role, password_hash, status, user_code, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    mysqli_stmt_bind_param($insertUser, 'ssssssss',
        $firstname, $lastname, $email, $contact_number,
        $role, $password_hash, $status, $user_code
    );

    if (!mysqli_stmt_execute($insertUser)) {
        echo json_encode(['success' => false, 'message' => mysqli_stmt_error($insertUser)]);
        mysqli_stmt_close($insertUser);
        exit;
    }

    $user_id = mysqli_insert_id($conn);
    mysqli_stmt_close($insertUser);

    // ── Generate permanent QR token ──
    $qr_token = hash('sha256', $user_id . $email . time());

    $updateQR = mysqli_prepare($conn, "UPDATE users SET qr_code = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($updateQR, 'si', $qr_token, $user_id);
    mysqli_stmt_execute($updateQR);
    mysqli_stmt_close($updateQR);

    // ── Insert vehicle ──
    $insertVehicle = mysqli_prepare($conn, "
        INSERT INTO vehicle (user_id, plate_number, vehicle_type) VALUES (?, ?, ?)
    ");
    mysqli_stmt_bind_param($insertVehicle, 'iss', $user_id, $plate_number, $vehicle_type);

    if (!mysqli_stmt_execute($insertVehicle)) {
        // Roll back user insert on vehicle failure
        $deleteUser = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
        mysqli_stmt_bind_param($deleteUser, 'i', $user_id);
        mysqli_stmt_execute($deleteUser);
        mysqli_stmt_close($deleteUser);
        echo json_encode(['success' => false, 'message' => 'Failed to save vehicle info. Please try again.']);
        mysqli_stmt_close($insertVehicle);
        exit;
    }

    mysqli_stmt_close($insertVehicle);
    mysqli_close($conn);

    echo json_encode([
        'success'  => true,
        'message'  => 'Account created successfully!',
        'redirect' => '../Login/login.html'
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — USeP ePark</title>
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --maroon: #7B1C2E;
    --maroon-dark: #5A1220;
    --maroon-light: #9E2438;
    --gold: #C9A84C;
    --gold-light: #E2C06A;
    --gold-pale: #F5E9C8;
    --bg: #F7F4F0;
    --bg-card: #FFFFFF;
    --text-primary: #1A1A1A;
    --text-secondary: #6B6B6B;
    --text-muted: #A0A0A0;
    --border: #E8E2D9;
    --border-strong: #D4C9B8;
    --success: #2D7A4F;
    --success-bg: #E8F5EE;
    --danger: #C0392B;
    --danger-bg: #FDEDEC;
    --shadow-lg: 0 10px 40px rgba(0,0,0,0.14), 0 4px 10px rgba(0,0,0,0.07);
  }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--maroon-dark);
    min-height: 100vh;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 24px;
    -webkit-font-smoothing: antialiased;
    position: relative;
    overflow: auto;
  }
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
      radial-gradient(ellipse 80% 60% at 20% 10%, rgba(201,168,76,0.12) 0%, transparent 60%),
      radial-gradient(ellipse 60% 80% at 80% 90%, rgba(201,168,76,0.08) 0%, transparent 60%);
    pointer-events: none;
  }
  .bg-pattern {
    position: fixed;
    inset: 0;
    opacity: 0.04;
    background-image:
      repeating-linear-gradient(0deg, transparent, transparent 40px, rgba(255,255,255,0.5) 40px, rgba(255,255,255,0.5) 41px),
      repeating-linear-gradient(90deg, transparent, transparent 40px, rgba(255,255,255,0.5) 40px, rgba(255,255,255,0.5) 41px);
    pointer-events: none;
  }
  .register-wrap {
    display: flex;
    width: 100%;
    max-width: 980px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    position: relative;
    z-index: 1;
    animation: fadeUp 0.5s cubic-bezier(0.4,0,0.2,1) both;
    align-self: flex-start;
    margin: auto;
  }
  .register-left {
    background: var(--maroon);
    width: 300px;
    flex-shrink: 0;
    padding: 48px 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
  }
  .register-left::after {
    content: 'EP';
    position: absolute;
    bottom: -30px;
    right: -20px;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 200px;
    color: rgba(255,255,255,0.04);
    line-height: 1;
    letter-spacing: -5px;
    pointer-events: none;
  }
  .register-logo { display: flex; align-items: center; gap: 14px; }
  .register-headline { position: relative; z-index: 1; }
  .register-headline h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 44px;
    color: #fff;
    line-height: 0.95;
    letter-spacing: 1px;
    margin-bottom: 14px;
  }
  .register-headline h1 span { color: var(--gold); }
  .register-headline p { font-size: 13px; color: rgba(255,255,255,0.55); line-height: 1.7; }
  .register-steps { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 14px; }
  .register-step { display: flex; align-items: center; gap: 12px; }
  .step-dot {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: rgba(201,168,76,0.2);
    border: 1.5px solid var(--gold);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 14px; color: var(--gold); flex-shrink: 0;
  }
  .step-text { font-size: 12px; color: rgba(255,255,255,0.55); line-height: 1.4; }
  .step-text strong { color: rgba(255,255,255,0.85); display: block; font-size: 13px; }
  .register-right {
    background: var(--bg-card);
    flex: 1;
    padding: 40px 44px;
    display: flex;
    flex-direction: column;
    overflow-y: visible;
  }
  .register-right h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 28px; letter-spacing: 1px;
    color: var(--maroon); margin-bottom: 4px;
  }
  .register-right .sub { font-size: 13px; color: var(--text-muted); margin-bottom: 28px; }
  .section-label {
    font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1.2px;
    color: var(--maroon);
    margin-bottom: 14px; margin-top: 22px;
    padding-bottom: 6px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 8px;
  }
  .section-label svg { width: 13px; height: 13px; }
  .section-label:first-of-type { margin-top: 0; }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
  .form-row.single { grid-template-columns: 1fr; }
  .form-group { display: flex; flex-direction: column; gap: 6px; }
  .form-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); }
  .input-wrap { position: relative; display: flex; align-items: center; }
  .input-icon { position: absolute; left: 12px; color: var(--text-muted); display: flex; pointer-events: none; }
  .input-icon svg { width: 15px; height: 15px; }
  .form-control {
    width: 100%;
    padding: 10px 13px 10px 38px;
    border: 1.5px solid var(--border-strong);
    border-radius: 9px;
    font-size: 13.5px; color: var(--text-primary);
    background: var(--bg); outline: none;
    font-family: 'DM Sans', sans-serif;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .form-control:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.14); background: #fff; }
  .form-control::placeholder { color: var(--text-muted); }
  .form-control.error { border-color: var(--danger); }
  .form-control.success { border-color: var(--success); }
  select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B6B6B' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center; cursor: pointer;
  }
  .input-toggle { position: absolute; right: 12px; background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; padding: 2px; }
  .input-toggle:hover { color: var(--text-primary); }
  .input-toggle svg { width: 15px; height: 15px; }
  .field-hint { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
  .field-error { font-size: 11px; color: var(--danger); margin-top: 2px; display: none; }
  .alert-msg {
    border-radius: 8px; padding: 10px 14px; font-size: 12.5px;
    margin-bottom: 18px; display: none; align-items: center; gap: 8px;
  }
  .alert-msg svg { width: 14px; height: 14px; flex-shrink: 0; }
  .alert-msg.error { background: var(--danger-bg); border: 1px solid #f5c6cb; color: var(--danger); }
  .btn-register {
    width: 100%; padding: 13px; background: var(--maroon); color: #fff;
    border: none; border-radius: 9px; font-size: 14px; font-weight: 700;
    font-family: 'DM Sans', sans-serif; cursor: pointer; letter-spacing: 0.5px;
    transition: background 0.2s, transform 0.15s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    margin-top: 24px;
  }
  .btn-register:hover { background: var(--maroon-dark); }
  .btn-register:active { transform: scale(0.99); }
  .btn-register svg { width: 16px; height: 16px; }
  .login-link { text-align: center; font-size: 13px; color: var(--text-muted); margin-top: 16px; }
  .login-link a { color: var(--maroon); font-weight: 600; text-decoration: none; }
  .login-link a:hover { color: var(--maroon-light); text-decoration: underline; }
  .register-footer { text-align: center; font-size: 11.5px; color: var(--text-muted); margin-top: 20px; padding-bottom: 8px; }
  .register-footer a { color: var(--maroon); font-weight: 500; }

  /* ── TOAST POPUP ── */
  .toast-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    z-index: 999;
    display: none;
    align-items: center;
    justify-content: center;
  }
  .toast-overlay.show {
    display: flex;
    animation: fadeIn 0.3s ease;
  }
  .toast-popup {
    background: #fff;
    border-radius: 20px;
    padding: 40px 44px;
    text-align: center;
    max-width: 380px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    position: relative;
    overflow: hidden;
    animation: popUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  }
  .toast-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: var(--success-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
  }
  .toast-icon svg {
    width: 36px; height: 36px;
    color: var(--success);
  }
  .toast-popup h3 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 28px;
    letter-spacing: 1px;
    color: var(--maroon);
    margin-bottom: 10px;
  }
  .toast-popup p {
    font-size: 13.5px;
    color: var(--text-muted);
    line-height: 1.7;
    margin-bottom: 4px;
  }
  /* Show the generated code in the success toast */
  .toast-code {
    display: inline-block;
    margin-top: 8px;
    padding: 6px 16px;
    background: var(--gold-pale);
    border: 1px solid var(--gold);
    border-radius: 6px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 15px;
    font-weight: 600;
    color: var(--maroon-dark);
    letter-spacing: 1px;
  }
  .toast-redirect {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }
  .toast-redirect svg {
    width: 12px; height: 12px;
    animation: spin 1s linear infinite;
  }
  .toast-progress {
    position: absolute;
    bottom: 0; left: 0;
    height: 5px;
    width: 100%;
    background: var(--success);
    border-radius: 0 0 20px 20px;
    animation: progress 2s linear forwards;
    transform-origin: left;
  }

  @keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
  @keyframes popUp   { from { opacity: 0; transform: scale(0.8) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
  @keyframes progress { from { width: 100%; } to { width: 0%; } }
  @keyframes spin    { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
  @keyframes fadeUp  { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

  @media (max-width: 700px) {
    .register-left { display: none; }
    .register-right { padding: 32px 24px; }
    .register-wrap { max-width: 480px; }
    .form-row { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>
<div class="bg-pattern"></div>

<!-- ── TOAST POPUP ── -->
<div class="toast-overlay" id="toastOverlay">
  <div class="toast-popup">
    <div class="toast-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    </div>
    <h3>Account Created!</h3>
    <p>Your account has been successfully registered.</p>
    <p style="font-size:12px;color:var(--text-muted);margin-top:6px;">Your user code is</p>
    <span class="toast-code" id="toastUserCode">CUS-2025-0001</span>
    <div class="toast-redirect">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
      Redirecting to login...
    </div>
    <div class="toast-progress"></div>
  </div>
</div>

<div class="register-wrap">

  <!-- ── LEFT PANEL ── -->
  <div class="register-left">
    <div class="register-logo">
      <img src="../assets/logo-icon.svg" alt="ePark" style="width:44px;height:44px;border-radius:10px;object-fit:cover;">
      <img src="../assets/logo-white.svg" alt="ePark" style="height:38px;object-fit:contain;object-position:left;display:block;">
    </div>
    <div class="register-headline">
      <h1>Create<br><span>Your</span><br>Account</h1>
      <p>Join USeP ePark to reserve parking slots and manage your vehicle entries with ease.</p>
    </div>
    <div class="register-steps">
      <div class="register-step">
        <div class="step-dot">1</div>
        <div class="step-text"><strong>Personal Info</strong>Name, email &amp; contact</div>
      </div>
      <div class="register-step">
        <div class="step-dot">2</div>
        <div class="step-text"><strong>Account Setup</strong>Password only — code is auto-assigned</div>
      </div>
      <div class="register-step">
        <div class="step-dot">3</div>
        <div class="step-text"><strong>Vehicle Info</strong>Plate number &amp; type</div>
      </div>
    </div>
  </div>

  <!-- ── RIGHT PANEL ── -->
  <div class="register-right">
    <h2>Register</h2>
    <p class="sub">Fill in the details below — your user code (e.g. CUS-2025-0001) will be assigned automatically</p>

    <div class="alert-msg error" id="errorMsg">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span id="errorText">Please fill in all required fields.</span>
    </div>

    <!-- SECTION 1: Personal Info -->
    <div class="section-label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Personal Information
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">First Name</label>
        <div class="input-wrap">
          <span class="input-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
          <input type="text" class="form-control" id="firstname" placeholder="Juan">
        </div>
        <span class="field-error" id="err-firstname">First name is required.</span>
      </div>
      <div class="form-group">
        <label class="form-label">Last Name</label>
        <div class="input-wrap">
          <span class="input-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
          <input type="text" class="form-control" id="lastname" placeholder="Dela Cruz">
        </div>
        <span class="field-error" id="err-lastname">Last name is required.</span>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="input-wrap">
          <span class="input-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
          <input type="email" class="form-control" id="email" placeholder="juan@gmail.com">
        </div>
        <span class="field-error" id="err-email">Enter a valid email address.</span>
      </div>
      <div class="form-group">
        <label class="form-label">Contact Number</label>
        <div class="input-wrap">
          <span class="input-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
          <input type="tel" class="form-control" id="contact_number" placeholder="09XXXXXXXXX">
        </div>
        <span class="field-error" id="err-contact">Enter a valid contact number.</span>
      </div>
    </div>

    <!-- SECTION 2: Account Setup (username field removed) -->
    <div class="section-label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      Account Setup
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-wrap">
          <span class="input-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
          <input type="password" class="form-control" id="password" placeholder="Min. 8 characters">
          <button class="input-toggle" type="button" onclick="togglePw('password')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <span class="field-error" id="err-password">Password must be at least 8 characters.</span>
      </div>
      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <div class="input-wrap">
          <span class="input-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
          <input type="password" class="form-control" id="confirm_password" placeholder="Re-enter password">
          <button class="input-toggle" type="button" onclick="togglePw('confirm_password')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <span class="field-error" id="err-confirm">Passwords do not match.</span>
      </div>
    </div>

    <!-- SECTION 3: Vehicle Info -->
    <div class="section-label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-2 2-1.5-3.7A2 2 0 0 0 15.646 5H8.4a2 2 0 0 0-1.903 1.257L5 10 3 8"/><path d="M7 14h.01"/><path d="M17 14h.01"/><rect width="18" height="8" x="3" y="10" rx="2"/><path d="M5 18v2"/><path d="M19 18v2"/></svg>
      Vehicle Information
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Plate Number</label>
        <div class="input-wrap">
          <span class="input-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="10" rx="2"/><path d="M6 11h.01M10 11h4M18 11h.01"/></svg></span>
          <input type="text" class="form-control" id="plate_number" placeholder="ABC 1234" style="text-transform:uppercase;">
        </div>
        <span class="field-error" id="err-plate">Plate number is required.</span>
      </div>
      <div class="form-group">
        <label class="form-label">Vehicle Type</label>
        <div class="input-wrap">
          <span class="input-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-2 2-1.5-3.7A2 2 0 0 0 15.646 5H8.4a2 2 0 0 0-1.903 1.257L5 10 3 8"/><rect width="18" height="8" x="3" y="10" rx="2"/></svg></span>
          <select class="form-control" id="vehicle_type" style="padding-left:38px;">
            <option value="" disabled selected>Select type</option>
            <option value="Motorcycle">Motorcycle</option>
            <option value="Car">Car</option>
            <option value="SUV">SUV</option>
            <option value="Van">Van</option>
            <option value="Truck">Truck</option>
          </select>
        </div>
        <span class="field-error" id="err-vehicle">Please select a vehicle type.</span>
      </div>
    </div>

    <!-- SUBMIT -->
    <button class="btn-register" onclick="handleRegister()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
      Create Account
    </button>

    <div class="login-link">
      Already have an account? <a href="../Login/login.html">Sign in here</a>
    </div>

    <div class="register-footer">
      University of Southeastern Philippines &mdash; Tagum Campus<br>
      <a href="#">USeP ePark System v1.0</a>
    </div>
  </div>

</div>

<script>
  function togglePw(id) {
    const input = document.getElementById(id);
    if (input) input.type = input.type === 'password' ? 'text' : 'password';
  }

  function setInputState(id, state) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('error', 'success');
    if (state) el.classList.add(state);
  }

  function validate() {
    let valid = true;

    // Username field removed — no longer validated client-side
    const fields = [
      { id: 'firstname',      errId: 'firstname', check: v => v.length > 0,                                msg: 'First name is required.' },
      { id: 'lastname',       errId: 'lastname',  check: v => v.length > 0,                                msg: 'Last name is required.' },
      { id: 'email',          errId: 'email',     check: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),        msg: 'Enter a valid email address.' },
      { id: 'contact_number', errId: 'contact',   check: v => /^[0-9]{10,13}$/.test(v.replace(/\s/g, '')), msg: 'Enter a valid contact number.' },
      { id: 'password',       errId: 'password',  check: v => v.length >= 8,                               msg: 'Password must be at least 8 characters.' },
      { id: 'plate_number',   errId: 'plate',     check: v => v.length > 0,                                msg: 'Plate number is required.' },
      { id: 'vehicle_type',   errId: 'vehicle',   check: v => v.length > 0,                                msg: 'Please select a vehicle type.' },
    ];

    fields.forEach(f => {
      const val   = (document.getElementById(f.id)?.value || '').trim();
      const ok    = f.check(val);
      const errEl = document.getElementById('err-' + f.errId);
      if (errEl) { errEl.textContent = f.msg; errEl.style.display = ok ? 'none' : 'block'; }
      setInputState(f.id, ok ? 'success' : 'error');
      if (!ok) valid = false;
    });

    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('confirm_password').value;
    const confirmErr = document.getElementById('err-confirm');
    if (pw !== cpw) {
      confirmErr.style.display = 'block';
      setInputState('confirm_password', 'error');
      valid = false;
    } else {
      confirmErr.style.display = 'none';
      if (cpw.length >= 8) setInputState('confirm_password', 'success');
    }
    return valid;
  }

  function handleRegister() {
    const errorMsg = document.getElementById('errorMsg');
    errorMsg.style.display = 'none';

    if (!validate()) {
      errorMsg.style.display = 'flex';
      document.getElementById('errorText').textContent = 'Please fix the errors below before submitting.';
      return;
    }

    const formData = new FormData();
    formData.append('firstname',      document.getElementById('firstname').value.trim());
    formData.append('lastname',       document.getElementById('lastname').value.trim());
    formData.append('email',          document.getElementById('email').value.trim());
    formData.append('contact_number', document.getElementById('contact_number').value.trim());
    // No 'username' appended — server generates user_code automatically
    formData.append('password',       document.getElementById('password').value);
    formData.append('plate_number',   document.getElementById('plate_number').value.trim().toUpperCase());
    formData.append('vehicle_type',   document.getElementById('vehicle_type').value);

    fetch('register.php', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Show the auto-generated code in the toast
          if (data.user_code) {
            document.getElementById('toastUserCode').textContent = data.user_code;
          }
          document.getElementById('toastOverlay').classList.add('show');
          setTimeout(() => { window.location.href = data.redirect; }, 2500);
        } else {
          errorMsg.style.display = 'flex';
          document.getElementById('errorText').textContent = data.message;
        }
      })
      .catch(() => {
        errorMsg.style.display = 'flex';
        document.getElementById('errorText').textContent = 'Server error. Please try again.';
      });
  }

  document.addEventListener('keydown', e => { if (e.key === 'Enter') handleRegister(); });
</script>
</body>
</html>