<?php
session_start();
$base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base . '/Login/login.html');
    exit;
}
if ($_SESSION['role'] === 'customer') {
    header('Location: ' . $base . '/User/userDashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logs — USeP ePark Admin</title>
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<div class="layout">

  <!-- ── Sidebar ─────────────────────────────────────────────────────────── -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <img src="../assets/logo-icon.svg" class="sidebar-logo" alt="ePark Logo" style="width:32px;height:32px;border-radius:8px;object-fit:cover;flex-shrink:0;">
      <img src="../assets/logo-white.svg" class="sidebar-brand" alt="ePark" style="height:36px;object-fit:contain;object-position:left;">
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
      <a class="nav-item" href="reservations.php" data-tooltip="Reservations">
        <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
        <span class="nav-label">Reservations</span>
        <span class="nav-badge" id="reservationBadge"></span>
      </a>
      <a class="nav-item active" href="logs.php" data-tooltip="Entry / Exit Logs">
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

  <!-- ── Main ──────────────────────────────────────────────────────────── -->
  <div class="main" id="mainArea">
    <header class="topbar">
      <h2 class="topbar-title">Entry / Exit Logs</h2>
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
          <div class="topbar-user-name"><?= htmlspecialchars($_SESSION['firstname'] ?? 'Admin') ?></div>
          <div class="topbar-user-role"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')) ?></div>
        </div>
      </div>
    </header>

    <div class="page-content">

      <!-- Page Header -->
      <div class="page-header fade-up">
        <div class="breadcrumb">
          <a href="dashboard.php">Dashboard</a>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span>Logs</span>
        </div>
        <h1>Entry / Exit Logs</h1>
        <p>Complete audit trail of all parking activity</p>
      </div>

      <!-- ── Stat Cards ─────────────────────────────────────────────────── -->
      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
        <div class="stat-card info fade-up delay-1">
          <div class="stat-header"><span class="stat-label">Today's Entries</span></div>
          <div class="stat-value" id="statEntries">—</div>
        </div>
        <div class="stat-card fade-up delay-2">
          <div class="stat-header"><span class="stat-label">Today's Exits</span></div>
          <div class="stat-value" id="statExits">—</div>
        </div>
        <div class="stat-card danger fade-up delay-3">
          <div class="stat-header"><span class="stat-label">Denied</span></div>
          <div class="stat-value" id="statDenied">—</div>
        </div>
        <div class="stat-card success fade-up delay-4">
          <div class="stat-header"><span class="stat-label">Revenue Today</span></div>
          <div class="stat-value" id="statRevenue">—</div>
        </div>
      </div>

      <!-- ── Activity Log Table ─────────────────────────────────────────── -->
      <div class="card fade-up delay-2">
        <div class="card-header">
          <span class="card-title">Activity Log</span>
          <div style="display:flex;gap:8px;align-items:center;">

            <!-- Status filter — values match log_status ENUM: 'in', 'out', 'denied' -->
            <select class="form-control" id="filterStatus" style="width:auto;padding:7px 32px 7px 12px;font-size:12px;">
              <option value="">All Events</option>
              <option value="in">Entry</option>
              <option value="out">Exit</option>
              <option value="denied">Denied</option>
            </select>

            <!-- Date filter -->
            <input type="date" class="form-control" id="filterDate" style="width:auto;padding:7px 12px;font-size:12px;">

            <!-- Export CSV -->
            <button class="btn btn-outline btn-sm" id="exportBtn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Export
            </button>
          </div>
        </div>

        <!-- Search bar -->
        <div class="toolbar" style="padding:14px 22px 0;">
          <div class="toolbar-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" id="searchInput" placeholder="Search plate, name, slot...">
          </div>
        </div>

        <!-- Table -->
        <div class="table-wrapper">
          <table id="logsTable">
            <thead>
              <tr>
                <th>Log ID</th>
                <th>Plate Number</th>
                <th>Owner</th>
                <th>Event</th>
                <th>Slot</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Duration</th>
                <th>Fee</th>
              </tr>
            </thead>
            <tbody id="logsBody">
              <tr>
                <td colspan="9" style="text-align:center;padding:3rem;color:var(--text-muted);">Loading...</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination" id="pagination"></div>
      </div>

    </div><!-- /page-content -->
  </div><!-- /main -->
</div><!-- /layout -->

<script src="../js/admin.js"></script>
<script>
// ============================================================================
// HELPERS
// ============================================================================

function formatCurrency(amount) {
    if (amount === null || amount === undefined) return '—';
    return '₱' + parseFloat(amount).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDuration(hours) {
    if (hours === null || hours === undefined || isNaN(parseFloat(hours))) return '—';
    const h = Math.floor(parseFloat(hours));
    const m = Math.round((parseFloat(hours) - h) * 60);
    return `${h}h ${m}m`;
}

function formatTime(datetime) {
    if (!datetime) return '—';
    return new Date(datetime).toLocaleTimeString('en-PH', {
        hour: '2-digit', minute: '2-digit'
    });
}

function eventBadge(status) {
    const map = {
        'in':     `<span class="badge badge-success"><span class="badge-dot"></span>Entry</span>`,
        'out':    `<span class="badge badge-info"><span class="badge-dot"></span>Exit</span>`,
        'denied': `<span class="badge badge-danger"><span class="badge-dot"></span>Denied</span>`,
    };
    return map[status] ?? `<span class="badge">${status}</span>`;
}

// ============================================================================
// STATE
// ============================================================================
let currentPage   = 1;
let searchTimeout = null;
let lastData      = [];   // held for CSV export

// ============================================================================
// 1. STAT CARDS — get_logs_stats.php → view_logs_today_stats
// ============================================================================
function loadStats() {
    fetch('backend/logs/get_logs_stats.php')
        .then(r => r.json())
        .then(data => {
            if (data.error) { console.error('Stats error:', data.error); return; }
            document.getElementById('statEntries').textContent = data.today_entries ?? 0;
            document.getElementById('statExits').textContent   = data.today_exits   ?? 0;
            document.getElementById('statDenied').textContent  = data.today_denied  ?? 0;
            document.getElementById('statRevenue').textContent = formatCurrency(data.today_revenue);
        })
        .catch(e => console.error('loadStats:', e));
}

// ============================================================================
// 2. LOGS TABLE — get_logs.php → view_logs_list
// ============================================================================
function loadLogs(page = 1) {
    currentPage = page;

    const status = document.getElementById('filterStatus').value;
    const date   = document.getElementById('filterDate').value;
    const search = document.getElementById('searchInput').value.trim();

    let url = `backend/logs/get_logs.php?page=${page}`;
    if (status) url += `&status=${encodeURIComponent(status)}`;
    if (date)   url += `&date=${encodeURIComponent(date)}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    const tbody = document.getElementById('logsBody');
    tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:3rem;color:var(--text-muted);">Loading...</td></tr>`;

    fetch(url)
        .then(r => r.json())
        .then(resp => {
            if (resp.error) {
                tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--text-muted);">Error: ${resp.error}</td></tr>`;
                return;
            }

            lastData = resp.data;

            if (!resp.data.length) {
                tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:3rem;color:var(--text-muted);">No logs found</td></tr>`;
                renderPagination(0, 1, 1);
                return;
            }

            tbody.innerHTML = resp.data.map(log => {
                const isActive = log.log_status === 'in';
                const isDenied = log.log_status === 'denied';
                return `
                <tr>
                    <td class="td-mono" style="font-size:11px;">#L-${String(log.log_id).padStart(4, '0')}</td>
                    <td class="td-mono">${log.plate_number}</td>
                    <td style="font-size:13px;">${log.owner_name}</td>
                    <td>${eventBadge(log.log_status)}</td>
                    <td class="td-mono">${log.slot_number ?? '—'}</td>
                    <td style="font-size:12px;font-family:'JetBrains Mono',monospace;">${formatTime(log.time_in)}</td>
                    <td style="font-size:12px;font-family:'JetBrains Mono',monospace;">${isActive || isDenied ? '<span style="color:var(--text-muted);">—</span>' : formatTime(log.time_out)}</td>
                    <td style="font-size:12px;">${isActive ? '<span style="color:var(--text-muted);">Active</span>' : isDenied ? '<span style="color:var(--text-muted);">—</span>' : formatDuration(log.total_duration)}</td>
                    <td style="font-size:13px;font-weight:700;color:var(--success);">${isActive || isDenied ? '<span style="color:var(--text-muted);">—</span>' : formatCurrency(log.parking_fee)}</td>
                </tr>`;
            }).join('');

            renderPagination(resp.total, resp.page, resp.total_pages);
        })
        .catch(e => {
            console.error('loadLogs:', e);
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--text-muted);">Failed to load logs</td></tr>`;
        });
}

// ============================================================================
// 3. PAGINATION
// ============================================================================
function renderPagination(total, page, totalPages) {
    const container = document.getElementById('pagination');
    if (totalPages <= 1) { container.innerHTML = ''; return; }

    let html = `
        <button class="page-btn" ${page === 1 ? 'disabled' : ''} onclick="loadLogs(${page - 1})">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </button>`;

    const start = Math.max(1, page - 2);
    const end   = Math.min(totalPages, page + 2);

    if (start > 1) html += `<button class="page-btn" onclick="loadLogs(1)">1</button>`;
    if (start > 2) html += `<span class="page-btn" style="cursor:default;">…</span>`;

    for (let i = start; i <= end; i++) {
        html += `<button class="page-btn ${i === page ? 'active' : ''}" onclick="loadLogs(${i})">${i}</button>`;
    }

    if (end < totalPages - 1) html += `<span class="page-btn" style="cursor:default;">…</span>`;
    if (end < totalPages)     html += `<button class="page-btn" onclick="loadLogs(${totalPages})">${totalPages}</button>`;

    html += `
        <button class="page-btn" ${page === totalPages ? 'disabled' : ''} onclick="loadLogs(${page + 1})">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>`;

    container.innerHTML = html;
}

// ============================================================================
// 4. CSV EXPORT
// ============================================================================
function exportCSV() {
    if (!lastData.length) { alert('No data to export.'); return; }

    const headers = ['Log ID','Plate Number','Owner','Vehicle Type','Status','Slot','Location','Time In','Time Out','Duration (h)','Fee (PHP)'];
    const rows    = lastData.map(log => [
        `#L-${String(log.log_id).padStart(4,'0')}`,
        log.plate_number,
        log.owner_name,
        log.vehicle_type,
        log.log_status,
        log.slot_number  ?? '',
        log.location_area ?? '',
        log.time_in      ?? '',
        log.time_out     ?? '',
        log.total_duration ?? '',
        log.parking_fee    ?? ''
    ].map(v => `"${String(v).replace(/"/g, '""')}"`).join(','));

    const csv  = [headers.join(','), ...rows].join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), {
        href: url,
        download: `logs_${new Date().toISOString().split('T')[0]}.csv`
    });
    a.click();
    URL.revokeObjectURL(url);
}

// ============================================================================
// INIT
// ============================================================================
document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadLogs(1);

    document.getElementById('filterStatus').addEventListener('change', () => loadLogs(1));
    document.getElementById('filterDate').addEventListener('change',   () => loadLogs(1));

    // Debounced search — waits 400ms after user stops typing
    document.getElementById('searchInput').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadLogs(1), 400);
    });

    document.getElementById('exportBtn').addEventListener('click', exportCSV);

    // Auto-refresh stats every 60 seconds
    setInterval(loadStats, 60000);
});
</script>
</body>
</html>