<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: http://localhost:8000/Admin/login.html');
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
<title>Slots — USeP ePark Admin</title>
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
      <a class="nav-item" href="dashboard.php" data-tooltip="Dashboard"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></span><span class="nav-label">Dashboard</span></a>
      <a class="nav-item" href="vehicles.php" data-tooltip="Vehicles"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></span><span class="nav-label">Vehicles</span></a>
      <a class="nav-item active" href="slots.html" data-tooltip="Slot Monitoring"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M15 3v18M3 9h18M3 15h18"/></svg></span><span class="nav-label">Slot Monitoring</span></a>
      <div class="nav-section-label">Management</div>
      <a class="nav-item" href="reservations.html" data-tooltip="Reservations"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span><span class="nav-label">Reservations</span><span class="nav-badge">3</span></a>
      <a class="nav-item" href="logs.html" data-tooltip="Entry / Exit Logs"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span><span class="nav-label">Entry / Exit Logs</span></a>
      <a class="nav-item" href="reports.html" data-tooltip="Reports"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span><span class="nav-label">Reports</span></a>
      <a class="nav-item" href="users.php" data-tooltip="User Access"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="nav-label">User Access</span></a>
    </nav>
    <div class="sidebar-footer">
      <button class="sidebar-toggle" id="sidebarToggle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"/></svg><span class="sidebar-toggle-label">Collapse</span></button>
    </div>
  </aside>

  <div class="main" id="mainArea">
    <header class="topbar">
      <h2 class="topbar-title">Slot Monitoring</h2>
      <div class="topbar-spacer"></div>
      <div class="topbar-search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg><input type="text" placeholder="Search anything..."></div>
      <button class="topbar-btn" id="notifBtn" title="Notifications">
  <img src="../assets/icons/icon-bell.svg" alt="Notifications" style="width:17px;height:17px;">
  <span class="topbar-notif-dot"></span>
</button>
      <div class="topbar-user"><img src="../assets/avatars/avatar-admin.svg" class="topbar-avatar" alt="Admin" style="width:30px;height:30px;border-radius:6px;object-fit:cover;"><div class="topbar-user-info"><div class="topbar-user-name">Admin</div><div class="topbar-user-role">Superadmin</div></div></div>
    </header>

    <div class="page-content">
      <div class="page-header fade-up">
        <div class="breadcrumb"><a href="dashboard.html">Dashboard</a><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg><span>Slot Monitoring</span></div>
        <h1>Parking Slots</h1>
        <p>Live status of all 50 parking slots</p>
      </div>

      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
          <div class="stat-card success fade-up delay-1">
              <div class="stat-header"><span class="stat-label">Available</span></div>
              <div class="stat-value">0</div>
              <div class="progress-bar" style="margin-top:12px;">
                  <div class="progress-fill success" style="width:0%;"></div>
              </div>
          </div>
          <div class="stat-card danger fade-up delay-2">
              <div class="stat-header"><span class="stat-label">Occupied</span></div>
              <div class="stat-value">0</div>
              <div class="progress-bar" style="margin-top:12px;">
                  <div class="progress-fill danger" style="width:0%;"></div>
              </div>
          </div>
          <div class="stat-card fade-up delay-3">
              <div class="stat-header"><span class="stat-label">Reserved</span></div>
              <div class="stat-value">0</div>
              <div class="progress-bar" style="margin-top:12px;">
                  <div class="progress-fill" style="width:0%;"></div>
              </div>
          </div>
          <div class="stat-card fade-up delay-4">
              <div class="stat-header"><span class="stat-label">Maintenance</span></div>
              <div class="stat-value">0</div>
              <div class="progress-bar" style="margin-top:12px;">
                  <div class="progress-fill" style="width:0%;background:var(--text-muted);"></div>
              </div>
          </div>
      </div>

     <div class="card fade-up delay-2">
        <div class="card-header">
          <span class="card-title">Live Slot Map</span>
          <div style="display:flex;gap:8px;">
            <select class="form-control" style="width:auto;padding:7px 32px 7px 12px;font-size:12px;" id="sectionFilter">
              <option value="">All Sections</option>
              <option value="A">Section A</option>
              <option value="B">Section B</option>
              <option value="C">Section C</option>
            </select>
            <button class="btn btn-outline btn-sm" onclick="loadSlots(); loadSlotStats();">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
              Refresh
            </button>
            <button class="btn btn-primary btn-sm" data-open-modal="addSlotModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Add Slot
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
          <div id="slotGrid">
            <!-- filled by loadSlots() -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Slot Detail / Edit Modal -->
<div class="modal-overlay" id="slotModal">
    <div class="modal" style="max-width:380px;">
        <div class="modal-header">
            <span class="modal-title">Slot Details</span>
            <button class="modal-close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body">
            <div style="text-align:center;margin-bottom:20px;">
                <div style="font-family:'Bebas Neue',sans-serif;font-size:52px;color:var(--maroon);line-height:1;" id="modalSlotNum">—</div>
                <span class="badge" id="modalSlotStatus">—</span>
            </div>

            <!-- Occupant info (shown only when occupied) -->
            <div id="slotOccupantInfo" style="display:none;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                    <span style="color:var(--text-muted);font-size:11px;font-weight:700;text-transform:uppercase;">Plate</span>
                    <span id="slotOccupantPlate" style="font-family:'JetBrains Mono',monospace;font-weight:700;"></span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                    <span style="color:var(--text-muted);font-size:11px;font-weight:700;text-transform:uppercase;">Owner</span>
                    <span id="slotOccupantName"></span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:var(--text-muted);font-size:11px;font-weight:700;text-transform:uppercase;">Time In</span>
                    <span id="slotTimeIn" style="font-family:'JetBrains Mono',monospace;"></span>
                </div>
            </div>

            <form id="updateSlotForm">
                <input type="hidden" name="slot_id" id="editSlotId">
                <div class="form-group">
                    <label class="form-label">Slot Number</label>
                    <input type="text" name="slot_number" id="editSlotNumber" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Location Area</label>
                    <input type="text" name="location_area" id="editLocationArea" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="editSlotStatus" class="form-control">
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="reserved">Reserved</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <div class="modal-footer" style="justify-content:space-between;">
                  <button type="button" class="btn btn-sm" id="deleteSlotBtn"
                      style="background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger);">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                      Delete Slot
                  </button>
                  <div style="display:flex;gap:8px;">
                      <button type="button" class="btn btn-outline" data-close-modal>Cancel</button>
                      <button type="submit" class="btn btn-primary">Update Slot</button>
                  </div>
              </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Slot Modal -->
<div class="modal-overlay" id="addSlotModal">
    <div class="modal" style="max-width:380px;">
        <div class="modal-header">
            <span class="modal-title">Add New Slot</span>
            <button class="modal-close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body">
            <form id="createSlotForm">
                <div class="form-group">
                    <label class="form-label">Slot Number</label>
                    <input type="text" name="slot_number" class="form-control" placeholder="e.g. A-01" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Location Area</label>
                    <input type="text" name="location_area" class="form-control" placeholder="e.g. A" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-close-modal>Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Slot</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../js/admin.js"></script>
</body>
</html>
