<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: http://localhost/USeP-ePark-main/Login/login.html');
    exit;
}
if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff') {
    header('Location: http://localhost/USeP-ePark-main/Admin/dashboard.php');
    exit;
}

// mga info sa qr atay 
$user_id      = $_SESSION['user_id'];
$firstname    = $_SESSION['firstname'];
$lastname     = $_SESSION['lastname'];
$plate_number = $_SESSION['plate_number'];
$vehicle_type = $_SESSION['vehicle_type'];

// grrr a comment for now
// $qr_data = json_encode([
//    'user_id'      => $user_id,
//    'name'         => $firstname . ' ' . $lastname,
//    'plate_number' => $plate_number,
//    'vehicle_type' => $vehicle_type
//]);

$qr_token = $_SESSION['qr_code'];
$qr_data  = json_encode([
    'token'        => $qr_token,
    'user_id'      => $user_id,
    'plate_number' => $plate_number
]);
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Code — USeP ePark</title>
  <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../User/css/user.css"> 
</head>
<body>
<div class="layout">

  <!-- ── SIDEBAR ── -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <img src="../assets/logo-icon.svg" class="sidebar-logo" alt="ePark Logo" style="width:32px;height:32px;border-radius:8px;object-fit:cover;flex-shrink:0;">
      <img src="../assets/logo-white.svg" class="sidebar-brand" alt="ePark" style="height:36px;object-fit:contain;object-position:left;">
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>

      <a class="nav-item" href="userDashboard.php" data-tooltip="Profile">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg>
        </span>
        <span class="nav-label">Profile</span>
      </a>

      <a class="nav-item" href="parkingreservations.php" data-tooltip="Parking Reservations">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-2 2-1.5-3.7A2 2 0 0 0 15.646 5H8.4a2 2 0 0 0-1.903 1.257L5 10 3 8"/><path d="M7 14h.01"/><path d="M17 14h.01"/><rect width="18" height="8" x="3" y="10" rx="2"/><path d="M5 18v2"/><path d="M19 18v2"/></svg>
        </span>
        <span class="nav-label">Parking Reservations</span>
      </a>

      <a class="nav-item active" href="qr.php" data-tooltip="QR Code">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M7 12h10"/></svg>
        </span>
        <span class="nav-label">QR Code</span>
      </a>

      <div class="nav-section-label">Account</div>

      <a class="nav-item" href="logs.php" data-tooltip="Parking History">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M16 22a4 4 0 0 0-8 0"/><circle cx="12" cy="15" r="3"/></svg>
        </span>
        <span class="nav-label">Parking History</span>
      </a>

      <a class="nav-item" href="transactions.php" data-tooltip="Transactions / Receipts">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2"/><path d="M3 11h3c.8 0 1.6.3 2.1.9l1.1.9c1.6 1.6 4.1 1.6 5.7 0l1.1-.9c.5-.5 1.3-.9 2.1-.9H21"/></svg>
        </span>
        <span class="nav-label">Transactions / Receipts</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <button class="sidebar-toggle" id="sidebarToggle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"/></svg>
        <span class="sidebar-toggle-label">Collapse</span>
      </button>
    </div>
  </aside>

  <!-- ── MAIN ── -->
  <div class="main" id="mainArea">

    <!-- ── TOPBAR ── -->
    <header class="topbar">
      <h2 class="topbar-title">QR Code</h2>
      <div class="topbar-spacer"></div>
      <button class="topbar-btn" id="notifBtn" title="Notifications">
        <img src="../assets/icons/icon-bell.svg" alt="Notifications" style="width:17px;height:17px;">
        <span class="topbar-notif-dot"></span>
      </button>
      <!-- FIX: wrapped topbar-user in dropdown for logout option on click -->
      <div class="topbar-user-dropdown" id="userDropdownWrapper" style="position:relative;">
        <div class="topbar-user" id="userDropdownToggle" style="cursor:pointer;">
          <div class="topbar-avatar">
            <img src="<?php echo !empty($_SESSION['profile_picture']) ? 'assets/uploads/' . htmlspecialchars($_SESSION['profile_picture']) : 'assets/img/userDefaultProfile.jpg'; ?>" class="topbar-avatar" alt="User" style="width:30px;height:30px;border-radius:6px;object-fit:cover;" onerror="this.src='assets/img/userDefaultProfile.jpg'">
          </div>
          <div class="topbar-user-info">
            <div class="topbar-user-name"><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></div>
            <div class="topbar-user-role">Customer</div>
          </div>
        </div>
        <!-- dropdown menu -->
        <div id="userDropdownMenu" style="display:none;position:absolute;right:0;top:calc(100% + 8px);background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,0.10);min-width:160px;z-index:999;overflow:hidden;">
          <a href="../Login/backend/auth/logout.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:#6b0606;font-size:14px;font-weight:500;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='#faf0f0'" onmouseout="this.style.background='#fff'">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Log Out
          </a>
        </div>
      </div>
    </header>

    <!-- ── PAGE CONTENT ── -->
    <div class="page-content">

      <div class="breadcrumb">
          <a href="userDashboard.php">Profile</a>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span>Qr Code</span>
        </div>

      <div class="page-header fade-up">
        <h1>My Parking Pass</h1>
        <p>Your entry pass for your reserved parking slot</p>
      </div>

      <!-- QR CARD -->
      <div class="card fade-up delay-1">
        <div class="qr-card-header">
          <span class="card-title" style="font-size: 24px;">Scan QR Code</span>
          <span class="qr-label">Present this QR code to the guard at the entrance for verification 
            <br> and access to your designated parking slot.</span>

        <!-- FIX: changed <canvas> to <div> — qrcodejs renders an <img> inside a div, not onto a canvas -->
        <div class="qr-container">
            <div id="qrCanvas"></div>
        </div>

         <button type="button" id="editProfileButton" onclick="downloadQR()" style="margin: 20px; height: 50px; width: 200px; font-size: 1rem;">
            Download Qr Code
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download-icon lucide-download"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
          </button>

        </div>
      </div>
      <!-- END QR CARD -->

    </div>
    <!-- END PAGE CONTENT -->

    <footer class="footer">
      <p>Copyright © 2026. All Rights Reserved.</p>
      <p><a href="">Terms of Service</a> | <a href="">Privacy Policy</a></p>
    </footer>

  </div>
  <!-- END MAIN -->

</div>

<!-- sa anim rani and shi -->
<script src="../User/js/user.js"> </script> 


<!-- qr code -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script> // sa qr functionality

 const sidebar  = document.getElementById('sidebar');
  const mainArea = document.getElementById('mainArea');
  document.getElementById('sidebarToggle').addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainArea.classList.toggle('expanded');
  });

  // FIX: topbar user dropdown toggle for logout
  const userDropdownToggle = document.getElementById('userDropdownToggle');
  const userDropdownMenu   = document.getElementById('userDropdownMenu');
  userDropdownToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = userDropdownMenu.style.display === 'block';
    userDropdownMenu.style.display = isOpen ? 'none' : 'block';
  });
  document.addEventListener('click', () => {
    userDropdownMenu.style.display = 'none';
  });


// FIX: $qr_data is already a JSON string from PHP, so pass it directly without wrapping in json_encode again
const qrData = <?php echo $qr_data; ?>;

new QRCode(document.getElementById('qrCanvas'), {
    text:         JSON.stringify(qrData),
    width:        250,
    height:       250,
    colorDark:    '#000000',
    colorLight:   '#ffffff',
    correctLevel: QRCode.CorrectLevel.H
});

 // FIX: qrcodejs creates an <img> inside the div, use that for download
  function downloadQR() {
    setTimeout(() => {
        const img  = document.querySelector('#qrCanvas img');
        if (!img) { alert('QR code not ready yet, please try again.'); return; }
        const link = document.createElement('a');
        link.href     = img.src;
        link.download = 'parking-qr-<?php echo htmlspecialchars($plate_number); ?>.png';
        link.click();
    }, 500);
}


</script>
</body>
</html>