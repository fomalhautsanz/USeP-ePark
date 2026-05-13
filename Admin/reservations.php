<?php
// ============================================================================
// reservations.php — Reservations Management Page
// ============================================================================
// Replaces reservations.html. Stat cards and table are populated via
// get_reservations.php (JSON). Approve/Deny call update_reservation.php.
// ============================================================================

ini_set('display_errors', 0);
error_reporting(E_ALL);

// Optional: session/auth guard — uncomment when auth is wired up
// session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('Location: ../login.php');
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservations — USeP ePark Admin</title>
  <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <style>
    /* ── Loading skeleton ── */
    .skeleton {
      background: linear-gradient(90deg, var(--border) 25%, color-mix(in srgb, var(--border) 60%, transparent) 50%, var(--border) 75%);
      background-size: 200% 100%;
      animation: shimmer 1.4s infinite;
      border-radius: 6px;
      display: inline-block;
    }
    @keyframes shimmer { to { background-position: -200% 0; } }

    /* ── Toast notification ── */
    #toast {
      position: fixed;
      bottom: 28px;
      right: 28px;
      z-index: 9999;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 13px 18px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 500;
      box-shadow: 0 8px 28px rgba(0,0,0,.18);
      opacity: 0;
      transform: translateY(12px);
      transition: opacity .25s, transform .25s;
      pointer-events: none;
      max-width: 360px;
    }
    #toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
    #toast.toast-success { background: var(--success-bg); color: var(--success); border: 1px solid var(--success); }
    #toast.toast-error   { background: var(--danger-bg);  color: var(--danger);  border: 1px solid var(--danger); }

    /* ── Empty state ── */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--text-muted);
      font-size: 13px;
    }
    .empty-state svg { width: 48px; height: 48px; opacity: .3; margin-bottom: 12px; }

    /* ── Action button states ── */
    .btn-action { transition: opacity .15s; }
    .btn-action:disabled { opacity: .45; cursor: not-allowed; }
  </style>
</head>
<body>
<div class="layout">

  <!-- ═══════════════════════════════ SIDEBAR ════════════════════════════════ -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <img src="../assets/logo-icon.svg"  class="sidebar-logo"  alt="ePark Logo" style="width:32px;height:32px;border-radius:8px;object-fit:cover;flex-shrink:0;">
      <img src="../assets/logo-white.svg" class="sidebar-brand" alt="ePark"      style="height:36px;object-fit:contain;object-position:left;">
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <a class="nav-item" href="dashboard.php" data-tooltip="Dashboard">
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
      <a class="nav-item active" href="reservations.php" data-tooltip="Reservations">
        <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
        <span class="nav-label">Reservations</span>
        <span class="nav-badge" id="navPendingBadge" style="display:none;"></span>
      </a>
      <a class="nav-item" href="logs.php" data-tooltip="Entry / Exit Logs">
        <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>
        <span class="nav-label">Entry / Exit Logs</span>
      </a>
      <a class="nav-item" href="reports.php" data-tooltip="Reports">
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

  <!-- ═══════════════════════════════ MAIN ══════════════════════════════════ -->
  <div class="main" id="mainArea">
    <header class="topbar">
      <h2 class="topbar-title">Reservations</h2>
      <div class="topbar-spacer"></div>
      <div class="topbar-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="topbarSearch" placeholder="Search anything...">
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

      <!-- Breadcrumb & Title -->
      <div class="page-header fade-up">
        <div class="breadcrumb">
          <a href="dashboard.php">Dashboard</a>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span>Reservations</span>
        </div>
        <h1>Reservation Management</h1>
        <p>Review and manage parking slot reservations</p>
      </div>

      <!-- ── Stat Cards ── -->
      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
        <div class="stat-card fade-up delay-1">
          <div class="stat-header"><span class="stat-label">Today's Total</span></div>
          <div class="stat-value" id="statTotal">—</div>
        </div>
        <div class="stat-card fade-up delay-2" style="--before-bg:var(--warning);">
          <div class="stat-header"><span class="stat-label">Pending</span></div>
          <div class="stat-value" id="statPending">—</div>
        </div>
        <div class="stat-card success fade-up delay-3">
          <div class="stat-header"><span class="stat-label">Approved</span></div>
          <div class="stat-value" id="statApproved">—</div>
        </div>
        <div class="stat-card danger fade-up delay-4">
          <div class="stat-header"><span class="stat-label">Cancelled</span></div>
          <div class="stat-value" id="statCancelled">—</div>
        </div>
      </div>

      <!-- ── Reservations Table Card ── -->
      <div class="card fade-up delay-2">
        <div class="card-header">
          <span class="card-title">All Reservations</span>
          <div style="display:flex;gap:8px;align-items:center;">
            <!-- Status filter -->
            <select class="form-control" id="filterStatus" style="width:auto;padding:7px 32px 7px 12px;font-size:12px;">
              <option value="">All Status</option>
              <option value="active">Pending</option>
              <option value="completed">Approved</option>
              <option value="cancelled">Cancelled</option>
              <option value="expired">Expired</option>
            </select>
            <!-- Date filter -->
            <input type="date" class="form-control" id="filterDate" style="width:auto;padding:7px 12px;font-size:12px;">
          </div>
        </div>

        <div class="toolbar" style="padding:14px 22px 0;">
          <div class="toolbar-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" id="tableSearch" placeholder="Search name, slot, plate, ref…">
          </div>
          <div class="toolbar-spacer"></div>
          <button class="btn btn-outline btn-sm" id="exportBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
          </button>
        </div>

        <div class="table-wrapper">
          <table id="reservationTable">
            <thead>
              <tr>
                <th>Ref #</th>
                <th>User</th>
                <th>Plate</th>
                <th>Slot</th>
                <th>Schedule</th>
                <th>Status</th>
                <th>Requested</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="reservationTbody">
              <!-- Populated by JS -->
              <tr id="loadingRow">
                <td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);font-size:13px;">
                  Loading reservations…
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination controls -->
        <div class="pagination" id="paginationControls"></div>
      </div>

    </div><!-- /page-content -->
  </div><!-- /main -->
</div><!-- /layout -->

<!-- Toast -->
<div id="toast"></div>

<script src="../js/admin.js"></script>
<script>
// ============================================================================
// reservations.js (inline) — Data fetching, rendering, and actions
// ============================================================================

const ENDPOINT_GET    = '/Admin/backend/reservations/get_reservations.php';
const ENDPOINT_UPDATE = '/Admin/backend/reservations/update_reservation.php';

const PAGE_SIZE = 10;

// State
let allReservations = [];
let currentPage     = 1;
let pendingActions  = new Set(); // reservation IDs with in-flight requests

// ── DOM refs ──────────────────────────────────────────────────────────────────
const tbody          = document.getElementById('reservationTbody');
const filterStatus   = document.getElementById('filterStatus');
const filterDate     = document.getElementById('filterDate');
const tableSearch    = document.getElementById('tableSearch');
const topbarSearch   = document.getElementById('topbarSearch');
const paginationEl   = document.getElementById('paginationControls');
const navBadge       = document.getElementById('navPendingBadge');

// ── Toast ─────────────────────────────────────────────────────────────────────
let toastTimer;
function showToast(message, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent  = message;
  t.className    = `show toast-${type}`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function esc(str) {
  return String(str ?? '')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatDuration(minutes) {
  if (!minutes) return '—';
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  return h > 0 ? `~${h}h ${m > 0 ? m + 'm' : ''}`.trim() : `~${m}m`;
}

function statusBadge(status) {
  const map = {
    active:    ['badge-warning', 'Pending'],
    completed: ['badge-success', 'Approved'],
    cancelled: ['badge-danger',  'Cancelled'],
    expired:   ['badge-danger',  'Expired'],
  };
  const [cls, label] = map[status] ?? ['badge-warning', status];
  return `<span class="badge ${cls}"><span class="badge-dot"></span>${esc(label)}</span>`;
}

function avatarFallback(row) {
  // Use profile picture if available, else a role-based SVG
  if (row.profile_picture) return esc(row.profile_picture);
  const roleMap = {
    customer: '../assets/avatars/avatar-student.svg',
    staff:    '../assets/avatars/avatar-staff.svg',
    admin:    '../assets/avatars/avatar-admin.svg',
  };
  return roleMap[row.user_role] ?? '../assets/avatars/avatar-student.svg';
}

function actionButtons(row) {
  if (row.status === 'active') {
    return `
      <div style="display:flex;gap:4px;">
        <button
          class="btn btn-sm btn-action"
          style="background:var(--success-bg);color:var(--success);border:1px solid var(--success);padding:5px 10px;font-size:11px;"
          onclick="handleAction(${row.reservation_id},'approve',this)"
          data-res-id="${row.reservation_id}">
          Approve
        </button>
        <button
          class="btn btn-sm btn-action"
          style="background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger);padding:5px 10px;font-size:11px;"
          onclick="handleAction(${row.reservation_id},'deny',this)"
          data-res-id="${row.reservation_id}">
          Deny
        </button>
      </div>`;
  }
  // View-only button for non-active
  return `
    <div style="display:flex;gap:4px;">
      <button class="btn btn-outline btn-icon btn-sm" title="View">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    </div>`;
}

// ── Render ────────────────────────────────────────────────────────────────────
function applyFilters() {
  const search = (tableSearch.value || topbarSearch.value).toLowerCase().trim();
  const status = filterStatus.value;
  const date   = filterDate.value;

  return allReservations.filter(r => {
    const matchStatus = !status || r.status === status;
    const matchDate   = !date   || r.time_reserved.startsWith(date);
    const matchSearch = !search || [
      r.ref_number, r.full_name, r.plate_number, r.slot_number
    ].some(v => String(v).toLowerCase().includes(search));
    return matchStatus && matchDate && matchSearch;
  });
}

function renderTable() {
  const filtered = applyFilters();
  const total    = filtered.length;
  const pages    = Math.max(1, Math.ceil(total / PAGE_SIZE));
  currentPage    = Math.min(currentPage, pages);

  const slice = filtered.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);

  if (slice.length === 0) {
    tbody.innerHTML = `
      <tr><td colspan="8">
        <div class="empty-state">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
          </svg>
          <p>No reservations found.</p>
        </div>
      </td></tr>`;
  } else {
    tbody.innerHTML = slice.map(r => `
      <tr data-res-id="${r.reservation_id}">
        <td class="td-mono">${esc(r.ref_number)}</td>
        <td>
          <div class="user-card">
            <img src="${avatarFallback(r)}" class="user-avatar" alt="" style="width:32px;height:32px;border-radius:6px;object-fit:cover;">
            <div>
              <div class="user-info-name">${esc(r.full_name)}</div>
              <div class="user-info-sub">${esc(r.user_role)}</div>
            </div>
          </div>
        </td>
        <td class="td-mono">${esc(r.plate_number)}</td>
        <td class="td-mono">${esc(r.slot_number)}</td>
        <td style="font-size:12px;">
          ${esc(r.date_label)} · ${esc(r.time_label)}<br>
          <span style="color:var(--text-muted);">${formatDuration(r.duration_minutes)}</span>
        </td>
        <td>${statusBadge(r.status)}</td>
        <td style="font-size:12px;color:var(--text-muted);">${esc(r.date_label)}</td>
        <td>${actionButtons(r)}</td>
      </tr>
    `).join('');
  }

  renderPagination(pages, total);
}

function renderPagination(pages, total) {
  if (pages <= 1) { paginationEl.innerHTML = ''; return; }

  let html = `<button class="page-btn" onclick="goPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
  </button>`;

  for (let i = 1; i <= pages; i++) {
    html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goPage(${i})">${i}</button>`;
  }

  html += `<button class="page-btn" onclick="goPage(${currentPage + 1})" ${currentPage === pages ? 'disabled' : ''}>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
  </button>`;

  paginationEl.innerHTML = html;
}

function goPage(n) {
  currentPage = n;
  renderTable();
}

// ── Stats ─────────────────────────────────────────────────────────────────────
function renderStats(stats) {
  document.getElementById('statTotal').textContent    = stats.total_today;
  document.getElementById('statPending').textContent  = stats.pending;
  document.getElementById('statApproved').textContent = stats.approved;
  document.getElementById('statCancelled').textContent= stats.cancelled;

  // Sidebar badge
  if (stats.pending > 0) {
    navBadge.textContent = stats.pending;
    navBadge.style.display = '';
  } else {
    navBadge.style.display = 'none';
  }
}

// ── Fetch all reservations ────────────────────────────────────────────────────
async function loadReservations() {
  tbody.innerHTML = `<tr id="loadingRow"><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);font-size:13px;">Loading reservations…</td></tr>`;

  try {
    const res  = await fetch(ENDPOINT_GET, { cache: 'no-store' });
    const data = await res.json();

    if (!data.success) {
      showToast(data.message || 'Failed to load reservations.', 'error');
      tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--danger);font-size:13px;">${esc(data.message)}</td></tr>`;
      return;
    }

    allReservations = data.reservations ?? [];
    renderStats(data.stats);
    renderTable();

  } catch (err) {
    console.error(err);
    showToast('Network error. Could not load reservations.', 'error');
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--danger);font-size:13px;">Network error. Please refresh.</td></tr>`;
  }
}

// ── Approve / Deny ────────────────────────────────────────────────────────────
async function handleAction(reservationId, action, triggerBtn) {
  if (pendingActions.has(reservationId)) return; // Prevent double-click

  // Disable both action buttons in this row
  const row  = triggerBtn.closest('tr');
  const btns = row.querySelectorAll('.btn-action');
  btns.forEach(b => b.disabled = true);
  pendingActions.add(reservationId);

  try {
    const res  = await fetch(ENDPOINT_UPDATE, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ reservation_id: reservationId, action }),
    });
    const data = await res.json();

    if (data.success) {
      showToast(data.message, 'success');

      // Update local state and re-render (no full reload needed)
      const rec = allReservations.find(r => r.reservation_id === reservationId);
      if (rec) {
        rec.status = data.new_status;
      }

      // Recalculate stats from local state
      const today = new Date().toISOString().slice(0, 10);
      const todayRecs = allReservations.filter(r => r.time_reserved.startsWith(today));
      renderStats({
        total_today: todayRecs.length,
        pending:     todayRecs.filter(r => r.status === 'active').length,
        approved:    todayRecs.filter(r => r.status === 'completed').length,
        cancelled:   todayRecs.filter(r => r.status === 'cancelled' || r.status === 'expired').length,
      });

      renderTable();
    } else {
      showToast(data.message || 'Action failed.', 'error');
      btns.forEach(b => b.disabled = false);
    }

  } catch (err) {
    console.error(err);
    showToast('Network error. Please try again.', 'error');
    btns.forEach(b => b.disabled = false);
  } finally {
    pendingActions.delete(reservationId);
  }
}

// ── Export CSV ────────────────────────────────────────────────────────────────
document.getElementById('exportBtn').addEventListener('click', () => {
  const filtered = applyFilters();
  if (!filtered.length) { showToast('Nothing to export.', 'error'); return; }

  const cols = ['Ref #','User','Role','Plate','Slot','Area','Scheduled','Duration (min)','Status','Requested'];
  const rows = filtered.map(r => [
    r.ref_number, r.full_name, r.user_role, r.plate_number,
    r.slot_number, r.location_area,
    `${r.date_label} ${r.time_label}`, r.duration_minutes,
    r.status, r.date_label,
  ].map(v => `"${String(v).replace(/"/g,'""')}"`).join(','));

  const csv  = [cols.join(','), ...rows].join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const url  = URL.createObjectURL(blob);
  const a    = Object.assign(document.createElement('a'), {
    href: url,
    download: `reservations_${new Date().toISOString().slice(0,10)}.csv`
  });
  a.click();
  URL.revokeObjectURL(url);
});

// ── Filter / search event listeners ──────────────────────────────────────────
filterStatus.addEventListener('change', () => { currentPage = 1; renderTable(); });
filterDate.addEventListener('change',   () => { currentPage = 1; renderTable(); });

let searchTimer;
function onSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => { currentPage = 1; renderTable(); }, 280);
}
tableSearch.addEventListener('input',  onSearch);
topbarSearch.addEventListener('input', onSearch);

// ── Auto-refresh every 60 seconds ────────────────────────────────────────────
loadReservations();
setInterval(loadReservations, 60_000);
</script>
</body>
</html>