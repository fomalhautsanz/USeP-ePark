<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: http://localhost:8000/Login/login.html');
    exit;
}
if ($_SESSION['role'] === 'customer') {
    header('Location: http://localhost:8000/User/userDashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — USeP ePark Admin</title>
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<div class="layout">

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <img src="../assets/logo-icon.svg" class="sidebar-logo" alt="ePark Logo" style="width:32px;height:32px;border-radius:8px;object-fit:cover;flex-shrink:0;">
      <img src="../assets/logo-white.svg" class="sidebar-brand" alt="ePark" style="height:36px;object-fit:contain;object-position:left;">
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <a class="nav-item active" href="dashboard.html" data-tooltip="Dashboard">
        <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></span>
        <span class="nav-label">Dashboard</span>
      </a>
      <a class="nav-item" href="vehicles.php" data-tooltip="Vehicles">
        <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></span>
        <span class="nav-label">Vehicles</span>
      </a>
      <a class="nav-item" href="slots.php" data-tooltip="Slot Monitoring">
        <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M15 3v18M3 9h18M3 15h18"/></svg></span>
        <span class="nav-label">Slot Monitoring</span>
      </a>
      <div class="nav-section-label">Management</div>
      <a class="nav-item" href="reservations.html" data-tooltip="Reservations">
        <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
        <span class="nav-label">Reservations</span>
        <span class="nav-badge">3</span>
      </a>
      <a class="nav-item" href="logs.html" data-tooltip="Entry / Exit Logs">
        <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>
        <span class="nav-label">Entry / Exit Logs</span>
      </a>
      <a class="nav-item" href="reports.html" data-tooltip="Reports">
        <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>
        <span class="nav-label">Reports</span>
      </a>
      <a class="nav-item" href="users.php" data-tooltip="User Access">
        <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
        <span class="nav-label">User Access</span>
      </a>
    </nav>
    <div class="sidebar-footer">
      <button class="sidebar-toggle" id="sidebarToggle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"/></svg>
        <span class="sidebar-toggle-label">Collapse</span>
      </button>
    </div>
  </aside>

  <div class="main" id="mainArea">
    <header class="topbar">
      <h2 class="topbar-title">Dashboard</h2>
      <div class="topbar-spacer"></div>
      <div class="topbar-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" placeholder="Search anything...">
      </div>
      <button class="topbar-btn" id="notifBtn" title="Notifications">
        <img src="../assets/icons/icon-bell.svg" alt="Notifications" style="width:17px;height:17px;">
        <span class="topbar-notif-dot"></span>
      </button>
      <div class="topbar-user">
        <img src="../assets/avatars/avatar-admin.svg" class="topbar-avatar" alt="Admin" style="width:30px;height:30px;border-radius:6px;object-fit:cover;">
        <div class="topbar-user-info">
          <div class="topbar-user-name">Admin</div>
          <div class="topbar-user-role">Superadmin</div>
        </div>
      </div>
    </header>

    <div class="page-content">
      <div class="page-header fade-up">
        <h1>Overview</h1>
        <p>Real-time parking status — as of <span id="nowTime"></span></p>
      </div>

      <div class="stats-grid">
        <div class="stat-card fade-up delay-1">
          <div class="stat-header">
            <span class="stat-label">Total Slots</span>
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M15 3v18M3 9h18M3 15h18"/></svg></span>
          </div>
          <div class="stat-value" data-target="50">0</div>
          <div class="stat-sub">Parking slots available</div>
        </div>
        <div class="stat-card success fade-up delay-2">
          <div class="stat-header">
            <span class="stat-label">Available</span>
            <span class="stat-icon success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></span>
          </div>
          <div class="stat-value" data-target="24">0</div>
          <div class="stat-sub"><span class="stat-change up">48%</span> of total capacity</div>
        </div>
        <div class="stat-card danger fade-up delay-3">
          <div class="stat-header">
            <span class="stat-label">Occupied</span>
            <span class="stat-icon danger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></span>
          </div>
          <div class="stat-value" data-target="18">0</div>
          <div class="stat-sub"><span class="stat-change down">36%</span> occupancy rate</div>
        </div>
        <div class="stat-card info fade-up delay-4">
          <div class="stat-header">
            <span class="stat-label">Today's Entries</span>
            <span class="stat-icon info"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span>
          </div>
          <div class="stat-value" data-target="47">0</div>
          <div class="stat-sub"><span class="stat-change up">+12</span> vs yesterday</div>
        </div>
        <div class="stat-card fade-up delay-5">
          <div class="stat-header">
            <span class="stat-label">Revenue Today</span>
            <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
          </div>
          <div class="stat-value" data-prefix="₱" data-target="2340">₱0</div>
          <div class="stat-sub"><span class="stat-change up">+₱320</span> vs yesterday</div>
        </div>
      </div>

      <div class="grid-2 fade-up delay-2" style="margin-bottom:16px;">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Weekly Entries</span>
            <span class="badge badge-gold">This Week</span>
          </div>
          <div class="card-body">
            <div class="bar-chart">
              <div class="bar-col"><div class="bar" style="height:60%;"></div><div class="bar-label">Mon</div></div>
              <div class="bar-col"><div class="bar" style="height:80%;"></div><div class="bar-label">Tue</div></div>
              <div class="bar-col"><div class="bar" style="height:55%;"></div><div class="bar-label">Wed</div></div>
              <div class="bar-col"><div class="bar" style="height:90%;"></div><div class="bar-label">Thu</div></div>
              <div class="bar-col"><div class="bar" style="height:70%;"></div><div class="bar-label">Fri</div></div>
              <div class="bar-col"><div class="bar" style="height:30%;"></div><div class="bar-label">Sat</div></div>
              <div class="bar-col"><div class="bar" style="height:20%;"></div><div class="bar-label">Sun</div></div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">Slot Utilization</span>
            <a href="slots.html" style="font-size:12px;color:var(--maroon);font-weight:600;">View All →</a>
          </div>
          <div class="card-body">
            <div class="donut-wrap">
              <svg class="donut" id="donutSvg" viewBox="0 0 120 120"></svg>
              <div class="donut-legend">
                <div class="donut-legend-item">
                  <div class="donut-legend-color" style="background:#2D7A4F;"></div>
                  <span class="donut-legend-label">Available</span>
                  <span class="donut-legend-val">24</span>
                </div>
                <div class="donut-legend-item">
                  <div class="donut-legend-color" style="background:#C0392B;"></div>
                  <span class="donut-legend-label">Occupied</span>
                  <span class="donut-legend-val">18</span>
                </div>
                <div class="donut-legend-item">
                  <div class="donut-legend-color" style="background:#C9A84C;"></div>
                  <span class="donut-legend-label">Reserved</span>
                  <span class="donut-legend-val">6</span>
                </div>
                <div class="donut-legend-item">
                  <div class="donut-legend-color" style="background:#A0A0A0;"></div>
                  <span class="donut-legend-label">Maintenance</span>
                  <span class="donut-legend-val">2</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid-2 fade-up delay-3">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Recent Activity</span>
            <a href="logs.html" style="font-size:12px;color:var(--maroon);font-weight:600;">View Logs →</a>
          </div>
          <div class="card-body" style="padding:0 22px;">
            <div class="log-list">
              <div class="log-item">
                <div class="log-dot entry"></div>
                <div class="log-content">
                  <div class="log-title">Entry — ABJ 1234</div>
                  <div class="log-sub">Slot A-05 · Juan dela Cruz</div>
                </div>
                <div class="log-time">8:42 AM</div>
              </div>
              <div class="log-item">
                <div class="log-dot exit"></div>
                <div class="log-content">
                  <div class="log-title">Exit — XYZ 5678</div>
                  <div class="log-sub">Slot B-12 · Maria Santos · ₱30</div>
                </div>
                <div class="log-time">8:38 AM</div>
              </div>
              <div class="log-item">
                <div class="log-dot entry"></div>
                <div class="log-content">
                  <div class="log-title">Entry — MNO 9999</div>
                  <div class="log-sub">Slot C-03 · Pedro Reyes</div>
                </div>
                <div class="log-time">8:31 AM</div>
              </div>
              <div class="log-item">
                <div class="log-dot denied"></div>
                <div class="log-content">
                  <div class="log-title">Denied — QRS 4321</div>
                  <div class="log-sub">No available slots</div>
                </div>
                <div class="log-time">8:24 AM</div>
              </div>
              <div class="log-item">
                <div class="log-dot exit"></div>
                <div class="log-content">
                  <div class="log-title">Exit — LMN 7890</div>
                  <div class="log-sub">Slot A-11 · Ana Gomez · ₱45</div>
                </div>
                <div class="log-time">8:19 AM</div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">Pending Reservations</span>
            <a href="reservations.html" style="font-size:12px;color:var(--maroon);font-weight:600;">Manage →</a>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>User</th>
                  <th>Slot</th>
                  <th>Time</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><div class="user-card"><img src="../assets/avatars/avatar-faculty.svg" class="user-avatar" alt="JD" style="width:38px;height:38px;border-radius:8px;object-fit:cover;"><div><div class="user-info-name">J. dela Cruz</div><div class="user-info-sub">Student</div></div></div></td>
                  <td class="td-mono">A-07</td>
                  <td style="font-size:12px;">9:00 AM</td>
                  <td><span class="badge badge-warning"><span class="badge-dot"></span>Pending</span></td>
                </tr>
                <tr>
                  <td><div class="user-card"><img src="../assets/avatars/avatar-student.svg" class="user-avatar" alt="MS" style="width:38px;height:38px;border-radius:8px;object-fit:cover;"><div><div class="user-info-name">M. Santos</div><div class="user-info-sub">Faculty</div></div></div></td>
                  <td class="td-mono">B-02</td>
                  <td style="font-size:12px;">10:30 AM</td>
                  <td><span class="badge badge-warning"><span class="badge-dot"></span>Pending</span></td>
                </tr>
                <tr>
                  <td><div class="user-card"><img src="../assets/avatars/avatar-staff.svg" class="user-avatar" alt="PR" style="width:38px;height:38px;border-radius:8px;object-fit:cover;"><div><div class="user-info-name">P. Reyes</div><div class="user-info-sub">Staff</div></div></div></td>
                  <td class="td-mono">C-05</td>
                  <td style="font-size:12px;">1:00 PM</td>
                  <td><span class="badge badge-warning"><span class="badge-dot"></span>Pending</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="../js/admin.js"></script>
<script>
  document.getElementById('nowTime').textContent = new Date().toLocaleString('en-PH', { dateStyle: 'long', timeStyle: 'short' });
</script>
</body>
</html>
