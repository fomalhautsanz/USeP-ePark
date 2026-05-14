
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Parking Reservation — USeP ePark</title>
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
      <img src="../assets/logo-icon.svg" class="sidebar-logo" alt="ePark Logo">
      <img src="../assets/logo-white.svg" class="sidebar-brand" alt="ePark">
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <a class="nav-item" href="userDashboard.php" data-tooltip="Profile">
        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg></span>
        <span class="nav-label">Profile</span>
      </a>
      <a class="nav-item active" href="parkingreservations.php" data-tooltip="Parking Reservations">
        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-2 2-1.5-3.7A2 2 0 0 0 15.646 5H8.4a2 2 0 0 0-1.903 1.257L5 10 3 8"/><path d="M7 14h.01"/><path d="M17 14h.01"/><rect width="18" height="8" x="3" y="10" rx="2"/><path d="M5 18v2"/><path d="M19 18v2"/></svg></span>
        <span class="nav-label">Parking Reservations</span>
      </a>
      <a class="nav-item" href="qr.php" data-tooltip="QR Code">
        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M7 12h10"/></svg></span>
        <span class="nav-label">QR Code</span>
      </a>
      <div class="nav-section-label">Account</div>
      <a class="nav-item" href="parkinghistory.php" data-tooltip="Parking History">
        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M16 22a4 4 0 0 0-8 0"/><circle cx="12" cy="15" r="3"/></svg></span>
        <span class="nav-label">Parking History</span>
      </a>
      <a class="nav-item" href="transactions.php" data-tooltip="Transactions / Receipts">
        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2"/><path d="M3 11h3c.8 0 1.6.3 2.1.9l1.1.9c1.6 1.6 4.1 1.6 5.7 0l1.1-.9c.5-.5 1.3-.9 2.1-.9H21"/></svg></span>
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
    <header class="topbar">
      <h2 class="topbar-title">Parking Reservations</h2>
      <div class="topbar-spacer"></div>
      <button class="topbar-btn" id="notifBtn" title="Notifications">
        <img src="../assets/icons/icon-bell.svg" alt="Notifications" style="width:17px;height:17px;">
        <span class="topbar-notif-dot"></span>
      </button>
      <div class="topbar-user-dropdown" id="userDropdownWrapper" style="position:relative;">
        <div class="topbar-user" id="userDropdownToggle" style="cursor:pointer;">
          <div class="topbar-avatar">
            <img src="../assets/img/userDefaultProfile.jpg" class="topbar-avatar" alt="User" style="width:30px;height:30px;border-radius:6px;object-fit:cover;">
          </div>
          <div class="topbar-user-info">
            <div class="topbar-user-name">Loading…</div>
            <div class="topbar-user-role">Customer</div>
          </div>
        </div>
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
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Parking Reservations</span>
      </div>

      <div class="page-header fade-up">
        <h1>Parking Slots</h1>
        <p>Reserve a parking slot for your visit. Reservations expire after 30 minutes.</p>
      </div>

      <!-- ── ACTIVE RESERVATION BANNER (hidden until user has one) ── -->
      <div id="reservationBanner" class="alert alert-info fade-up" style="display:none;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px;padding:14px 18px;border-radius:10px;background:var(--maroon, #6b0606);color:#fff;">
        <div style="display:flex;align-items:center;gap:12px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-2 2-1.5-3.7A2 2 0 0 0 15.646 5H8.4a2 2 0 0 0-1.903 1.257L5 10 3 8"/><path d="M7 14h.01"/><path d="M17 14h.01"/><rect width="18" height="8" x="3" y="10" rx="2"/><path d="M5 18v2"/><path d="M19 18v2"/></svg>
          <div>
            <div style="font-weight:600;font-size:14px;">
              Active Reservation: <span id="bannerRef"></span> — Slot <span id="bannerSlot"></span>
            </div>
            <div style="font-size:12px;opacity:0.85;">
              Expires at <span id="bannerExpiry"></span> &nbsp;·&nbsp; <span id="bannerCountdown">—</span>
            </div>
          </div>
        </div>
        <button id="cancelBannerBtn" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,0.5);font-size:13px;padding:6px 14px;">
          Cancel Reservation
        </button>
      </div>

      <!-- ── STAT CARDS ── -->
      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
        <div class="stat-card success fade-up delay-1">
          <div class="stat-header"><span class="stat-label">Available</span></div>
          <div class="stat-value" id="statAvailable">—</div>
          <div class="progress-bar" style="margin-top:12px;"><div class="progress-fill success" style="width:0%;transition:width 0.6s;"></div></div>
        </div>
        <div class="stat-card danger fade-up delay-2">
          <div class="stat-header"><span class="stat-label">Occupied</span></div>
          <div class="stat-value" id="statOccupied">—</div>
          <div class="progress-bar" style="margin-top:12px;"><div class="progress-fill danger" style="width:0%;transition:width 0.6s;"></div></div>
        </div>
        <div class="stat-card fade-up delay-3">
          <div class="stat-header"><span class="stat-label">Reserved</span></div>
          <div class="stat-value" id="statReserved">—</div>
          <div class="progress-bar" style="margin-top:12px;"><div class="progress-fill" style="width:0%;transition:width 0.6s;"></div></div>
        </div>
        <div class="stat-card fade-up delay-4">
          <div class="stat-header"><span class="stat-label">Maintenance</span></div>
          <div class="stat-value" id="statMaintenance">—</div>
          <div class="progress-bar" style="margin-top:12px;"><div class="progress-fill" style="width:0%;background:var(--text-muted);transition:width 0.6s;"></div></div>
        </div>
      </div>

      <!-- ── LIVE SLOT MAP ── -->
      <div class="card fade-up delay-2">
        <div class="card-header">
          <span class="card-title">Live Slot Map</span>
          <div style="display:flex;gap:8px;align-items:center;">
            <select class="form-control" style="width:auto;padding:7px 32px 7px 12px;font-size:12px;" id="sectionFilter">
              <option value="">All Sections</option>
              <option value="A">Section A</option>
              <option value="B">Section B</option>
              <option value="C">Section C</option>
            </select>
            <button class="btn btn-outline btn-sm" id="refreshSlotsBtn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
              Refresh
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="slot-legend">
            <div class="slot-legend-item" style="color:var(--success);"><div class="slot-legend-dot"></div>Available</div>
            <div class="slot-legend-item" style="color:var(--danger);"><div class="slot-legend-dot"></div>Occupied</div>
            <div class="slot-legend-item" style="color:#8A6A0A;"><div class="slot-legend-dot"></div>Reserved</div>
            <div class="slot-legend-item" style="color:var(--text-muted);"><div class="slot-legend-dot"></div>Maintenance</div>
          </div>

          <!-- Slot sections injected here by JS -->
          <div id="slotGrids">
            <div style="text-align:center;padding:40px;color:var(--text-muted);">Loading slots…</div>
          </div>
        </div>
      </div>

    </div>
    <!-- END PAGE CONTENT -->

    <footer class="footer">
      <p>Copyright © 2026. All Rights Reserved.</p>
      <p><a href="">Terms of Service</a> | <a href="">Privacy Policy</a></p>
    </footer>
  </div>
  <!-- END MAIN -->

</div>

<!-- ── RESERVATION CONFIRMATION MODAL ── -->
<div class="modal-overlay" id="slotModal">
  <div class="modal" style="max-width:380px;">
    <div class="modal-header">
      <span class="modal-title" id="modalTitle">Reserve Slot</span>
      <button class="modal-close" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div style="text-align:center;margin-bottom:16px;">
        <div style="font-family:'Bebas Neue',sans-serif;font-size:52px;color:var(--maroon);line-height:1;" id="modalSlotNum">—</div>
        <span class="badge badge-success" id="modalSlotStatus">Available</span>
      </div>
      <p style="font-size:13px;color:var(--text-muted);text-align:center;margin-bottom:0;">
        Your reservation will be held for <strong>30 minutes</strong>. Please arrive before it expires.
      </p>
      <!-- Feedback message area -->
      <div id="modalFeedback" style="display:none;margin-top:14px;font-size:13px;text-align:center;padding:8px 12px;border-radius:6px;"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close-modal type="button">Cancel</button>
      <button class="btn btn-primary" id="confirmReserveBtn" type="button">Confirm Reservation</button>
    </div>
  </div>
</div>

<script src="../User/js/user.js"></script>
<script src="../User/js/parkingreservations.js"></script>

<script>
  // Topbar user dropdown
  const userDropdownToggle = document.getElementById('userDropdownToggle');
  const userDropdownMenu   = document.getElementById('userDropdownMenu');
  if (userDropdownToggle) {
    userDropdownToggle.addEventListener('click', e => {
      e.stopPropagation();
      userDropdownMenu.style.display = userDropdownMenu.style.display === 'block' ? 'none' : 'block';
    });
    document.addEventListener('click', () => { userDropdownMenu.style.display = 'none'; });
  }
</script>
</body>
</html>