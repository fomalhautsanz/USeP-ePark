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
<title>User Access — USeP ePark Admin</title>
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
      <a class="nav-item" href="slots.php" data-tooltip="Slot Monitoring"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M15 3v18M3 9h18M3 15h18"/></svg></span><span class="nav-label">Slot Monitoring</span></a>
      <div class="nav-section-label">Management</div>
      <a class="nav-item" href="reservations.html" data-tooltip="Reservations"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span><span class="nav-label">Reservations</span><span class="nav-badge">3</span></a>
      <a class="nav-item" href="logs.html" data-tooltip="Entry / Exit Logs"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span><span class="nav-label">Entry / Exit Logs</span></a>
      <a class="nav-item" href="reports.html" data-tooltip="Reports"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span><span class="nav-label">Reports</span></a>
      <a class="nav-item active" href="users.php" data-tooltip="User Access"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="nav-label">User Access</span></a>
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
      <h2 class="topbar-title">User Access</h2>
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
        <div class="breadcrumb"><a href="dashboard.html">Dashboard</a><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg><span>User Access</span></div>
        <h1>User Access Control</h1>
        <p>Manage system users and their access permissions</p>
      </div>

      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
        <div class="stat-card fade-up delay-1"><div class="stat-header"><span class="stat-label">Total Users</span></div><div class="stat-value">0</div></div>
        <div class="stat-card success fade-up delay-2"><div class="stat-header"><span class="stat-label">Active</span></div><div class="stat-value">0</div></div>
        <div class="stat-card danger fade-up delay-3"><div class="stat-header"><span class="stat-label">Suspended</span></div><div class="stat-value">0</div></div>
        <div class="stat-card fade-up delay-4"><div class="stat-header"><span class="stat-label">Admins</span></div><div class="stat-value">0</div></div>
      </div>

      <div class="card fade-up delay-2">
        <div class="card-header">
          <span class="card-title">System Users</span>
          <div style="display:flex;gap:8px;align-items:center;">
            <select class="form-control" style="width:auto;padding:7px 32px 7px 12px;font-size:12px;" data-filter-table="usersTable" data-filter-col="3">
              <option value="">All Roles</option>
              <option value="superadmin">Superadmin</option>
              <option value="admin">Admin</option>
              <option value="student">Student</option>
              <option value="faculty">Faculty</option>
              <option value="staff">Staff</option>
              <option value="guest">Guest</option>
            </select>
            <select class="form-control" style="width:auto;padding:7px 32px 7px 12px;font-size:12px;" data-filter-table="usersTable" data-filter-col="4">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
            </select>
            <button class="btn btn-primary btn-sm" data-open-modal="addUserModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Add User
            </button>
          </div>
        </div>
        <div class="toolbar" style="padding:14px 22px 0;">
          <div class="toolbar-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" placeholder="Search name, email, ID..." data-search-table="usersTable">
          </div>
          <div class="toolbar-spacer"></div>
          <button class="btn btn-outline btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
          </button>
        </div>
        <div class="table-wrapper">
          <table id="usersTable">
            <thead>
              <tr>
                <th>User</th>
                <th>ID</th>
                <th>Vehicles</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Joined</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><div class="user-card"><img src="../assets/avatars/avatar-admin.svg" alt="SA" style="width:38px;height:38px;border-radius:8px;object-fit:cover;flex-shrink:0;"><div><div class="user-info-name">System Admin</div><div class="user-info-sub">epark@usep.edu.ph</div><div class="user-info-sub">0917-100-4001</div></div></div></td>
                <td class="td-mono" style="font-size:11px;">ADM-001</td>
                <td style="font-size:13px;color:var(--text-muted);">—</td>
                <td><span class="badge badge-maroon">Superadmin</span></td>
                <td><span class="badge badge-success"><span class="badge-dot"></span>Active</span></td>
                <td style="font-size:12px;color:var(--text-muted);">Just now</td>
                <td style="font-size:12px;color:var(--text-muted);">Jan 2025</td>
                <td><div style="display:flex;gap:4px;"><button class="btn btn-outline btn-icon btn-sm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button></div></td>
              </tr>
              <tr>
                <td><div class="user-card"><img src="../assets/avatars/avatar-faculty.svg" alt="JD" style="width:38px;height:38px;border-radius:8px;object-fit:cover;flex-shrink:0;"><div><div class="user-info-name">Juan dela Cruz</div><div class="user-info-sub">jdelaCruz@usep.edu.ph</div><div class="user-info-sub">0917-100-0001</div></div></div></td>
                <td class="td-mono" style="font-size:11px;">FAC-2021-0042</td>
                <td style="font-size:13px;">1 vehicle</td>
                <td><span class="badge badge-info">Faculty</span></td>
                <td><span class="badge badge-success"><span class="badge-dot"></span>Active</span></td>
                <td style="font-size:12px;color:var(--text-muted);">Today 8:42 AM</td>
                <td style="font-size:12px;color:var(--text-muted);">Jun 2023</td>
                <td><div style="display:flex;gap:4px;"><button class="btn btn-outline btn-icon btn-sm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="btn btn-outline btn-icon btn-sm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="btn btn-outline btn-icon btn-sm" style="color:var(--warning);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></button></div></td>
              </tr>
              <tr>
                <td><div class="user-card"><img src="../assets/avatars/avatar-student.svg" alt="MS" style="width:38px;height:38px;border-radius:8px;object-fit:cover;flex-shrink:0;"><div><div class="user-info-name">Maria Santos</div><div class="user-info-sub">msantos@usep.edu.ph</div><div class="user-info-sub">0977-043-2140</div></div></div></td>
                <td class="td-mono" style="font-size:11px;">STU-2023-1188</td>
                <td style="font-size:13px;">1 vehicle</td>
                <td><span class="badge badge-muted">Student</span></td>
                <td><span class="badge badge-success"><span class="badge-dot"></span>Active</span></td>
                <td style="font-size:12px;color:var(--text-muted);">Today 8:38 AM</td>
                <td style="font-size:12px;color:var(--text-muted);">Aug 2023</td>
                <td><div style="display:flex;gap:4px;"><button class="btn btn-outline btn-icon btn-sm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="btn btn-outline btn-icon btn-sm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="btn btn-outline btn-icon btn-sm" style="color:var(--warning);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></button></div></td>
              </tr>
              <tr>
                <td><div class="user-card"><img src="../assets/avatars/avatar-student.svg" alt="RL" style="width:38px;height:38px;border-radius:8px;object-fit:cover;flex-shrink:0;"><div><div class="user-info-name">Rosa Lopez</div><div class="user-info-sub">rlopez@usep.edu.ph</div><div class="user-info-sub">0916-555-8899</div></div></div></td>
                <td class="td-mono" style="font-size:11px;">STU-2022-0891</td>
                <td style="font-size:13px;">2 vehicles</td>
                <td><span class="badge badge-muted">Student</span></td>
                <td><span class="badge badge-danger"><span class="badge-dot"></span>Suspended</span></td>
                <td style="font-size:12px;color:var(--text-muted);">Mar 1, 2026</td>
                <td style="font-size:12px;color:var(--text-muted);">Aug 2022</td>
                <td><div style="display:flex;gap:4px;"><button class="btn btn-outline btn-icon btn-sm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="btn btn-sm" style="background:var(--success-bg);color:var(--success);border:1px solid var(--success);font-size:11px;padding:5px 10px;">Restore</button></div></td>
              </tr>
              <tr>
                <td><div class="user-card"><img src="../assets/avatars/avatar-staff.svg" alt="PR" style="width:38px;height:38px;border-radius:8px;object-fit:cover;flex-shrink:0;"><div><div class="user-info-name">Pedro Reyes</div><div class="user-info-sub">preyes@usep.edu.ph</div><div class="user-info-sub">0921-905-8099</div></div></div></td>
                <td class="td-mono" style="font-size:11px;">STA-2020-0015</td>
                <td style="font-size:13px;">1 vehicle</td>
                <td><span class="badge badge-gold">Staff</span></td>
                <td><span class="badge badge-success"><span class="badge-dot"></span>Active</span></td>
                <td style="font-size:12px;color:var(--text-muted);">Today 8:31 AM</td>
                <td style="font-size:12px;color:var(--text-muted);">Mar 2020</td>
                <td><div style="display:flex;gap:4px;"><button class="btn btn-outline btn-icon btn-sm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button><button class="btn btn-outline btn-icon btn-sm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="btn btn-outline btn-icon btn-sm" style="color:var(--warning);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></button></div></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="pagination">
          <button class="page-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg></button>
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
          <button class="page-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="addUserModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add New User</span>
      <button class="modal-close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-body">
      <div class="form-row">
        <form id="createUserForm">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="customer">Customer</option>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            Create User
        </button>
        </div> 
      </form>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Edit User</span>
      <button class="modal-close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-body">
      <form id="editUserForm">
        <input type="hidden" name="user_id" id="editUserId">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" id="editFirstName" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" id="editLastName" class="form-control">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" id="editEmail" class="form-control">
        </div>

        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" id="editPhone" class="form-control">
        </div>

        <div class="form-group">
          <label class="form-label">Role</label>
          <select name="role" id="editRole" class="form-control">
            <option value="customer">Customer</option>
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">New Password <span style="font-size:11px;color:var(--text-muted);text-transform:none;font-weight:400;">(leave blank to keep current)</span></label>
          <input type="password" name="password" id="editPassword" class="form-control" placeholder="Enter new password">
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px;">
          <button type="button" class="btn btn-sm" id="deleteUserBtn"
            style="background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger);display:flex;align-items:center;gap:6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            Delete User
          </button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal-overlay" id="deleteConfirmModal">
  <div class="modal" style="max-width:380px;">
    <div class="modal-header">
      <span class="modal-title">Delete User</span>
      <button class="modal-close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-body">
      <p style="font-size:14px;color:var(--text-secondary);margin-bottom:6px;">
        You are about to permanently delete:
      </p>
      <p style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;" id="deleteUserName"></p>
      <p style="font-size:12px;color:var(--text-muted);margin-bottom:20px;" id="deleteUserCode"></p>
      <p style="font-size:13px;color:var(--danger);background:var(--danger-bg);padding:10px 14px;border-radius:8px;">
        This action cannot be undone. All data associated with this user will be permanently removed.
      </p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline btn-sm" id="cancelDeleteBtn">Cancel</button>
      <button class="btn btn-danger btn-sm" id="confirmDeleteBtn">Yes, delete user</button>
    </div>
  </div>
</div>

<!-- View User Modal -->
<div class="modal-overlay" id="viewUserModal">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <span class="modal-title">User Details</span>
      <button class="modal-close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-body">

      <!-- Avatar + name -->
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
        <img id="viewAvatar" src="" alt=""
          style="width:64px;height:64px;border-radius:12px;object-fit:cover;flex-shrink:0;"
          onerror="this.src='../assets/avatars/avatar-student.svg'">
        <div>
          <div id="viewFullName" style="font-size:17px;font-weight:700;color:var(--text-primary);"></div>
          <div id="viewUserCode" style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--maroon);margin-top:3px;"></div>
          <div id="viewStatusBadge" style="margin-top:6px;"></div>
        </div>
      </div>

      <!-- Details grid -->
      <div style="display:flex;flex-direction:column;gap:0;border:1px solid var(--border);border-radius:10px;overflow:hidden;">

        <div style="display:flex;align-items:center;padding:11px 16px;border-bottom:1px solid var(--border);background:var(--bg);">
          <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);width:110px;flex-shrink:0;">Role</span>
          <span id="viewRoleBadge"></span>
        </div>

        <div style="display:flex;align-items:center;padding:11px 16px;border-bottom:1px solid var(--border);">
          <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);width:110px;flex-shrink:0;">Email</span>
          <span id="viewEmail" style="font-size:13px;color:var(--text-primary);"></span>
        </div>

        <div style="display:flex;align-items:center;padding:11px 16px;border-bottom:1px solid var(--border);background:var(--bg);">
          <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);width:110px;flex-shrink:0;">Phone</span>
          <span id="viewPhone" style="font-size:13px;color:var(--text-primary);"></span>
        </div>

        <div style="display:flex;align-items:center;padding:11px 16px;border-bottom:1px solid var(--border);">
          <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);width:110px;flex-shrink:0;">Vehicles</span>
          <span id="viewVehicles" style="font-size:13px;color:var(--text-primary);"></span>
        </div>

        <div style="display:flex;align-items:center;padding:11px 16px;border-bottom:1px solid var(--border);background:var(--bg);">
          <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);width:110px;flex-shrink:0;">Joined</span>
          <span id="viewJoined" style="font-size:13px;color:var(--text-primary);"></span>
        </div>

        <div style="display:flex;align-items:center;padding:11px 16px;">
          <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);width:110px;flex-shrink:0;">Last Login</span>
          <span id="viewLastLogin" style="font-size:13px;color:var(--text-primary);"></span>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Suspend Confirm Modal -->
<div class="modal-overlay" id="suspendConfirmModal">
  <div class="modal" style="max-width:380px;">
    <div class="modal-header">
      <span class="modal-title" id="suspendModalTitle">Suspend User</span>
      <button class="modal-close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-body">
      <p style="font-size:14px;color:var(--text-secondary);margin-bottom:6px;" id="suspendActionLabel">You are about to suspend:</p>
      <p style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;" id="suspendUserName"></p>
      <p style="font-size:12px;color:var(--text-muted);margin-bottom:20px;" id="suspendUserCode"></p>
      <p style="font-size:13px;color:var(--warning);background:var(--warning-bg);padding:10px 14px;border-radius:8px;">
        This user will lose access to the system until their account is restored.
      </p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline btn-sm" id="cancelSuspendBtn">Cancel</button>
      <button class="btn btn-sm" id="confirmSuspendBtn"
        style="background:var(--warning-bg);color:var(--warning);border:1px solid var(--warning);">
        Yes, suspend user
      </button>
    </div>
  </div>
</div>

<script src="../js/admin.js"></script>
</body>
</html>
