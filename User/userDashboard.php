<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Dynamic redirect that works on any server
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $redirect_url = $protocol . '://' . $host . $base_path . '/../Login/login.html';
    header('Location: ' . $redirect_url);
    exit;
}

// para sa pic
$profile_picture = $_SESSION['profile_picture'] ?? null;
// FIX: corrected relative path — userDashboard.php is already inside User/, so no ../User/ prefix needed
$pic = !empty($profile_picture) ? 'assets/uploads/' . $profile_picture : 'assets/img/userDefaultProfile.jpg';

// do not touch grrr
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
$email = $_SESSION['email'];
$contact_number = $_SESSION['contact_number'];
// must... touch...


// FIX #1: vehicle_type and plate_number come from the `vehicle` table (not `users`),
// so they may not be set in session if your login script doesn't join that table.
// Added null coalescing fallbacks to prevent undefined index warnings.
$vehicle_type = $_SESSION['vehicle_type'] ?? 'Not set';
$plate_number = $_SESSION['plate_number'] ?? 'Not set';

$gender = $_SESSION['gender'] ?? null;
$birthdate = $_SESSION['birthdate'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile — USeP ePark</title>
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

      <a class="nav-item active" href="userDashboard.php" data-tooltip="Profile">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20a6 6 0 0 0-12[...]
        </span>
        <span class="nav-label">Profile</span>
      </a>

      <a class="nav-item" href="parkingreservations.php" data-tooltip="Parking Reservations">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-2 2-1.5-3.7A2[...]
        </span>
        <span class="nav-label">Parking Reservations</span>
      </a>

      <a class="nav-item" href="qr.php" data-tooltip="QR Code">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-[...]
        </span>
        <span class="nav-label">QR Code</span>
      </a>

      <div class="nav-section-label">Account</div>

      <a class="nav-item" href="parkinghistory.php" data-tooltip="Parking History">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22a2 2 0 0 1-2-2[...]
        </span>
        <span class="nav-label">Parking History</span>
      </a>

      <a class="nav-item" href="transactions.php" data-tooltip="Transactions / Receipts">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18"[...]
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
      <h2 class="topbar-title">PROFILE</h2>
      <div class="topbar-spacer"></div>
      <button class="topbar-btn" title="Notifications">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="[...]
        <span class="topbar-notif-dot"></span>
      </button>
     <!-- FIX: wrapped topbar-user in a dropdown container for logout option on click -->
     <div class="topbar-user-dropdown" id="userDropdownWrapper" style="position:relative;">
      <div class="topbar-user" id="userDropdownToggle" style="cursor:pointer;">
        <div class="topbar-avatar">
          <!-- FIX: use session profile pic with default fallback instead of hardcoded guest SVG -->
          <img src="<?php echo htmlspecialchars($pic); ?>" class="topbar-avatar" alt="User" style="width:30px;height:30px;border-radius:6px;object-fit:cover;" onerror="this.src='assets/img/userDe[...]
        </div>
        <div class="topbar-user-info">
          <div class="topbar-user-name"><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></div>
          <!-- FIX: changed hardcoded "User" to "Customer" to match other pages -->
          <div class="topbar-user-role">Customer</div>
        </div>
      </div>
      <!-- dropdown menu -->
      <div id="userDropdownMenu" style="display:none;position:absolute;right:0;top:calc(100% + 8px);background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 4px 16px rgba(0,[...]
        <a href="../Login/backend/auth/logout.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;color:#6b0606;font-size:14px;font-weight:500;text-decoration:none;transition:b[...]
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><pat[...]
          Log Out
        </a>
      </div>
     </div>
    </header>

    <!-- ── PERSONAL CONTENT ── -->
    <div class="page-content">

      <div class="page-header fade-up">
        <h1>My Profile</h1>
        <p>View and manage your personal details</p>
      </div>

      <!-- USER INFO CONTAINER -->
      <div class="card fade-up delay-1">

        <!-- FIX #2: profile-card-header div was closed too early (right after the <img>),
             which left the Edit Profile button floating outside the header div.
             Moved the closing </div> to after the button so both are properly wrapped. -->
        <!-- FIX: changed to column layout and centered so avatar, name and button are all centered -->
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 28px;border-bottom:1px solid var(--border);background:#faf9f7;gap:16px;">
          <!-- avatar + name/email -->
          <div style="display:flex;flex-direction:column;align-items:center;gap:12px;text-align:center;">
            <?php if ($pic): ?>
              <img src="<?php echo htmlspecialchars($pic); ?>" style="width:150px;height:150px;border-radius:50%;object-fit:cover;flex-shrink:0;background:#c8c8c8;">
            <?php else: ?>
              <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23c8c8c8'/%3E%3Ccircle cx='50' cy='38' r='22' [...]
            <?php endif; ?>
            <div>
              <div style="font-size:18px;font-weight:700;color:var(--text-primary);line-height:1.2;"><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></div>
              <div style="font-size:13px;color:var(--text-muted);margin-top:4px;"><?php echo htmlspecialchars($email); ?></div>
            </div>
          </div>
          <!-- Edit Profile button -->
          <button class="btn btn-primary" type="button" onclick="window.location.href='editProfile.php'">
            Edit Profile
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M13 21h8"/><path d="m15 5 4 4"/>
              <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
            </svg>
          </button>
        </div>

        <!-- Personal Information -->
        <div class="card-body">
          <div class="section-title">Personal Information</div>
          <div class="info-grid">

            <div class="info-field">
              <span class="info-label">Name</span>
              <span class="info-value"><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></span>
            </div>

            <div class="info-field">
              <span class="info-label">Date of Birth</span>
              <span class="info-value">
                <?php echo !empty($birthdate) ? htmlspecialchars($birthdate) : 'Not set'; ?>
              </span>
            </div>

            <div class="info-field">
              <span class="info-label">Gender</span>
              <span class="info-value">
                <?php echo !empty($gender) ? htmlspecialchars($gender) : 'Not set'; ?>
              </span>
            </div>

            <div class="info-field">
              <span class="info-label">Vehicle Type</span>
              <!-- FIX #3: vehicle_type may be 'Not set' (from FIX #1 fallback),
                   so wrapping with the same empty-check pattern used for gender/birthdate. -->
              <span class="info-value">
                <?php echo !empty($vehicle_type) ? htmlspecialchars($vehicle_type) : 'Not set'; ?>
              </span>
            </div>

            <div class="info-field">
              <span class="info-label">License Plate Number</span>
              <!-- FIX #3 (same): consistent empty-check for plate_number -->
              <span class="info-value">
                <?php echo !empty($plate_number) ? htmlspecialchars($plate_number) : 'Not set'; ?>
              </span>
            </div>

            <div class="info-field">
              <span class="info-label">Email</span>
              <span class="info-value"><?php echo htmlspecialchars($email); ?></span>
            </div>

            <div class="info-field">
              <span class="info-label">Contact</span>
              <span class="info-value"><?php echo htmlspecialchars($contact_number); ?></span>
            </div>

          </div>
        </div>

      </div>
      <!-- END OF USER INFO-->

    </div>
    <!-- END OF PERSONAL CONTENT -->

    <!-- ── SECURITY ── -->
    <div class="page-content">

      <div class="page-header fade-up">
        <h1>SECURITY</h1>
        <p>Manage your password and account security</p>
      </div>

      <!-- SECURITY CONTAINER -->
      <div class="card fade-up delay-1">
        <div class="card-body">
          <div class="section-title">Change Password</div>
          <div class="info-grid">

            <div class="PasswordConfiguration">
              <label for="currentPasswordInput">Current Password</label>
              <!-- FIX #4: <label for="..."> was pointing to "currentPassword" but the input id
                   is "currentPasswordInput". Fixed all three labels to match their input IDs. -->
              <div class="input-wrap">
                <input type="password" id="currentPasswordInput" name="currentPasswordInput" placeholder=" ">
                <button class="input-toggle" type="button" onclick="togglePw('currentPasswordInput')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"[...]
                </button>
              </div>

              <label for="newPasswordInput">New Password</label>
              <div class="input-wrap">
                <input type="password" id="newPasswordInput" name="newPasswordInput" placeholder=" ">
                <button class="input-toggle" type="button" onclick="togglePw('newPasswordInput')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"[...]
                </button>
              </div>

              <label for="confirmPasswordInput">Confirm New Password</label>
              <div class="input-wrap">
                <input type="password" id="confirmPasswordInput" name="confirmPasswordInput" placeholder=" " required>
                <button class="input-toggle" type="button" onclick="togglePw('confirmPasswordInput')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"[...]
                </button>
              </div>

              <button type="button" id="confirmPasswordButton">
                Update Password
              </button>
            </div>

            <div style="margin-top: 10px;">
              <div style="background: #6b0606; border-radius: 10px; padding: 24px; align-self: start;">
                <strong id="passwordTips">🔒 Password Tips</strong>
                <ul id="passwordTipsList">
                  <li>At least 8 characters long</li>
                  <li>Mix uppercase and lowercase letters</li>
                  <li>Include numbers and symbols</li>
                  <li>Avoid using your name or birthdate</li>
                </ul>

                <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);">
                  <p style="font-size: 14px; color: var(--border);">Forgot your password or having trouble?</p>
                  <!-- FIX #5: href was a blank space (" ") which causes a broken navigation.
                       Changed to "#" as a safe placeholder until the real support URL is set. -->
                  <a href="#" id="ContactSupportLink">Contact Support
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="r[...]
                      <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                  </a>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
      <!-- END OF SECURITY -->

    </div>
    <!-- END OF SECURITY CONTENT -->

    <footer class="footer">
      <p>Copyright &copy; 2026. All Rights Reserved.</p>
      <p><a href="#">Terms of Service</a> | <a href="#">Privacy Policy</a></p>
    </footer>

  </div>
  <!-- END MAIN -->

</div>

<script src="../User/js/user.js"></script>
<script>
  // Sidebar toggle
  const sidebar = document.getElementById('sidebar');
  const mainArea = document.getElementById('mainArea');
  document.getElementById('sidebarToggle').addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainArea.classList.toggle('expanded');
  });

  // ── INLINE EDIT FUNCTIONS ──
  function toggleEdit(field) {
    document.getElementById('display-' + field).style.display = 'none';
    document.getElementById('input-' + field).style.display = 'inline-block';
    document.getElementById('save-' + field).style.display = 'inline-block';
    document.getElementById('cancel-' + field).style.display = 'inline-block';
    document.getElementById('edit-btn-' + field).style.display = 'none';
  }

  function cancelEdit(field) {
    document.getElementById('display-' + field).style.display = 'inline-block';
    document.getElementById('input-' + field).style.display = 'none';
    document.getElementById('save-' + field).style.display = 'none';
    document.getElementById('cancel-' + field).style.display = 'none';
    document.getElementById('edit-btn-' + field).style.display = 'inline-block';
  }

  function saveField(field) {
    const value = document.getElementById('input-' + field).value;

    if (!value) {
      alert('Please fill in the field before saving!');
      return;
    }

    fetch('updateField.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ field: field, value: value })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById('display-' + field).textContent = value;
        cancelEdit(field);
      } else {
        // FIX #6: Generic alert replaced with the server's error message when available,
        // so the DB trigger errors (e.g. invalid email/contact format) are shown to the user.
        alert(data.message || 'Failed to save. Please try again.');
      }
    })
    .catch(() => alert('Something went wrong. Please try again.'));
  }

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

  document.getElementById('confirmPasswordButton').addEventListener('click', function () {
    const current = document.getElementById('currentPasswordInput').value;
    const newPw   = document.getElementById('newPasswordInput').value;
    const confirm = document.getElementById('confirmPasswordInput').value;

    // basic client-side checks
    if (!current || !newPw || !confirm) {
      alert('Please fill in all password fields.');
      return;
    }
    if (newPw.length < 8) {
      alert('New password must be at least 8 characters.');
      return;
    }
    if (newPw !== confirm) {
      alert('Passwords do not match.');
      return;
    }

    // FIX #7: Added a check so the new password cannot be the same as the current one.
    // This is a client-side convenience check; the real enforcement should also be in updatePassword.php.
    if (newPw === current) {
      alert('New password must be different from your current password.');
      return;
    }

    const formData = new FormData();
    formData.append('current_password', current);
    formData.append('new_password',     newPw);
    formData.append('confirm_password', confirm);

    fetch('updatePassword.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('Password updated successfully!');
        // clear the fields
        document.getElementById('currentPasswordInput').value = '';
        document.getElementById('newPasswordInput').value     = '';
        document.getElementById('confirmPasswordInput').value = '';
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(() => alert('Server error. Please try again.'));
  });
</script>
</body>
</html>
