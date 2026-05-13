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

// para sa pic 
$profile_picture = $_SESSION['profile_picture'] ?? null;
// FIX: fallback to default image instead of null so topbar avatar never breaks
$pic = !empty($profile_picture) ? '../User/assets/uploads/' . $profile_picture : '../User/assets/img/userDefaultProfile.jpg';

$firstname      = $_SESSION['firstname'];
$lastname       = $_SESSION['lastname'];
$email          = $_SESSION['email'];
$contact_number = $_SESSION['contact_number'];
$vehicle_type   = $_SESSION['vehicle_type'];
$plate_number   = $_SESSION['plate_number'];
$gender         = $_SESSION['gender'] ?? '';
$birthdate      = $_SESSION['birthdate'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile — USeP ePark</title>
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
      <a class="nav-item" href="qr.php" data-tooltip="QR Code">
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
      <h2 class="topbar-title">PROFILE</h2>
      <div class="topbar-spacer"></div>
      <button class="topbar-btn" title="Notifications">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span class="topbar-notif-dot"></span>
      </button>
      <!-- FIX: wrapped topbar-user in dropdown for logout option on click -->
      <div class="topbar-user-dropdown" id="userDropdownWrapper" style="position:relative;">
        <div class="topbar-user" id="userDropdownToggle" style="cursor:pointer;">
          <div class="topbar-avatar">
            <!-- FIX: use session profile pic with default fallback instead of hardcoded guest SVG -->
            <img src="<?php echo htmlspecialchars($pic); ?>" class="topbar-avatar" alt="User" style="width:30px;height:30px;border-radius:6px;object-fit:cover;" onerror="this.src='../User/assets/img/userDefaultProfile.jpg'">
          </div>
          <div class="topbar-user-info">
            <div class="topbar-user-name"><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></div>
            <!-- FIX: changed hardcoded "User" to "Customer" to match other pages -->
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

      <div class="breadcrumb fade-up">
        <a href="userDashboard.php">Profile</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <span>Edit Profile</span>
      </div>

      <div class="page-header fade-up">
        <h1>Edit Profile</h1>
        <p>Update your personal information and profile photo</p>
      </div>

      <div class="card fade-up delay-1">

        <!-- ── AVATAR UPLOAD ── -->
        <div class="avatar-edit-area">
          <div class="avatar-upload-wrapper" onclick="document.getElementById('avatarFileInput').click()">
            <img src="" id="avatarPreview" class="avatar-img" alt="Profile Photo">
            <div class="avatar-overlay">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3z"/>
                <circle cx="12" cy="13" r="3"/>
              </svg>
              <span>Change Photo</span>
            </div>
          </div>
          <input type="file" id="avatarFileInput" accept="image/*" onchange="previewAvatar(event)"/>
          <div class="avatar-hint">Click photo to upload a new one<br/>JPG, PNG or GIF · Max 2MB</div>
        </div>

        <!-- ── EDIT FORM ── -->
        <form class="edit-form" id="editProfileForm" onsubmit="saveProfile(event)">

          <div class="form-section">
            <div class="section-title">Personal Information</div>
            <div class="form-row-2">

              <!-- Forms -->
              <div class="form-group">
                <label class="form-label" for="inputFirstname">First Name</label>
                <input class="form-control" type="text" id="inputFirstname"
                  placeholder="First name"
                  value="<?php echo htmlspecialchars($firstname); ?>" required/>
              </div>

              <div class="form-group">
                <label class="form-label" for="inputLastname">Last Name</label>
                <input class="form-control" type="text" id="inputLastname"
                  placeholder="Last name"
                  value="<?php echo htmlspecialchars($lastname); ?>" required/>
              </div>

              <div class="form-group">
                <label class="form-label" for="inputDOB">Date of Birth</label>
                <input class="form-control" type="date" id="inputDOB"
                  value="<?php echo htmlspecialchars($birthdate); ?>"/>
              </div>

              <div class="form-group">
                <label class="form-label" for="inputGender">Gender</label>
                <select class="form-control" id="inputGender">
                  <option value="">Select gender</option>
                  <option value="Male"              <?php echo $gender === 'Male'              ? 'selected' : ''; ?>>Male</option>
                  <option value="Female"            <?php echo $gender === 'Female'            ? 'selected' : ''; ?>>Female</option>
                  <option value="Prefer not to say" <?php echo $gender === 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label" for="inputContact">Contact Number</label>
                <input class="form-control" type="tel" id="inputContact"
                  placeholder="09xxxxxxxxx"
                  value="<?php echo htmlspecialchars($contact_number); ?>" required/>
              </div>

              <div class="form-group">
                <label class="form-label" for="inputEmail">Email Address</label>
                <input class="form-control" type="email" id="inputEmail"
                  placeholder="example@gmail.com"
                  value="<?php echo htmlspecialchars($email); ?>" required/>
              </div>

            </div>
          </div>

          <div class="form-section">
            <div class="section-title">Vehicle Information</div>
            <div class="form-row-2">

              <div class="form-group">
                <label class="form-label" for="inputVehicle">Vehicle Type</label>
                <select class="form-control" id="inputVehicle" required>
                  <option value="">Select vehicle type</option>
                  <option value="Car"        <?php echo $vehicle_type === 'Car'        ? 'selected' : ''; ?>>Car</option>
                  <option value="Motorcycle" <?php echo $vehicle_type === 'Motorcycle' ? 'selected' : ''; ?>>Motorcycle</option>
                  <option value="Van"        <?php echo $vehicle_type === 'Van'        ? 'selected' : ''; ?>>Van</option>
                  <option value="Truck"      <?php echo $vehicle_type === 'Truck'      ? 'selected' : ''; ?>>Truck</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label" for="inputPlate">License Plate Number</label>
                <input class="form-control" type="text" id="inputPlate"
                  placeholder="e.g. JKL 3456"
                  value="<?php echo htmlspecialchars($plate_number); ?>" required
                  style="font-family:'JetBrains Mono',monospace;letter-spacing:1px;text-transform:uppercase;"
                  oninput="this.value = this.value.toUpperCase()"/>
              </div>

            </div>
          </div>

        </form>

        <!-- ── ACTIONS ── -->
        <div class="form-actions">
          <a href="userDashboard.php" class="btn btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Cancel
          </a>
          <button class="btn btn-primary" type="submit" form="editProfileForm">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Changes
          </button>
        </div>

      </div>
    </div>

    <footer class="footer">
      <p>Copyright © 2026. All Rights Reserved.</p>
      <p><a href="">Terms of Service</a> | <a href="">Privacy Policy</a></p>
    </footer>

  </div>
</div>

<!-- ── TOAST ── -->
<div class="toast" id="toast">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
  Profile updated successfully!
</div>

<script>
  // Sidebar toggle
  const sidebar = document.getElementById('sidebar');
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

  // Avatar preview
  function previewAvatar(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
      alert('Image is too large! Please choose a file under 2MB.');
      return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      document.getElementById('avatarPreview').src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  // save profile
  function saveProfile(e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('firstname',      document.getElementById('inputFirstname').value.trim());
    formData.append('lastname',       document.getElementById('inputLastname').value.trim());
    formData.append('email',          document.getElementById('inputEmail').value.trim());
    formData.append('contact_number', document.getElementById('inputContact').value.trim());
    formData.append('gender',         document.getElementById('inputGender').value);
    formData.append('birthdate',      document.getElementById('inputDOB').value);
    formData.append('vehicle_type',   document.getElementById('inputVehicle').value);
    formData.append('plate_number',   document.getElementById('inputPlate').value.trim().toUpperCase());

     // ↓ THIS sends the image file to updateProfile.php
    const imageFile = document.getElementById('avatarFileInput').files[0];
    if (imageFile) {
        formData.append('profile_picture', imageFile);
    }

    fetch('updateProfile.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const toast = document.getElementById('toast');
        toast.classList.add('show');
        setTimeout(() => {
          toast.classList.remove('show');
          window.location.href = 'userDashboard.php';
        }, 2000);
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(() => alert('Server error. Please try again.'));
  }

  // avatar
  window.addEventListener('load', () => {
    const img = document.getElementById('avatarPreview');
    <?php if ($pic): ?>
        img.src = "<?php echo htmlspecialchars($pic); ?>";
    <?php else: ?>
        img.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23c8c8c8'/%3E%3Ccircle cx='50' cy='38' r='22' fill='%23fff'/%3E%3Cellipse cx='50' cy='85' rx='32' ry='24' fill='%23fff'/%3E%3C/svg%3E";
    <?php endif; ?>
});
</script>
</body>
</html>