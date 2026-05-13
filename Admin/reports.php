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
<title>Reports — USeP ePark Admin</title>
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
      <a class="nav-item" href="logs.html" data-tooltip="Entry / Exit Logs">
        <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>
        <span class="nav-label">Entry / Exit Logs</span>
      </a>
      <a class="nav-item active" href="reports.php" data-tooltip="Reports">
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
      <h2 class="topbar-title">Reports</h2>
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
          <span>Reports</span>
        </div>
        <h1>Reports &amp; Analytics</h1>
        <p>Generate and export parking activity reports</p>
      </div>

      <!-- ── Generate Report ───────────────────────────────────────────── -->
      <div class="card fade-up delay-1" style="margin-bottom:16px;">
        <div class="card-header"><span class="card-title">Generate Report</span></div>
        <div class="card-body">
          <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">

            <div class="form-group" style="margin:0;">
              <label class="form-label">Report Type</label>
              <!-- value= must match PHP $type keys exactly -->
              <select class="form-control" id="reportType" style="min-width:180px;">
                <option value="daily">Daily Summary</option>
                <option value="monthly">Monthly Summary</option>
                <option value="vehicle">Vehicle Activity</option>
                <option value="revenue">Revenue Report</option>
                <option value="slots">Slot Utilization</option>
              </select>
            </div>

            <div class="form-group" style="margin:0;" id="dateFromGroup">
              <label class="form-label">Date From</label>
              <input type="date" class="form-control" id="dateFrom" style="min-width:150px;">
            </div>

            <div class="form-group" style="margin:0;" id="dateToGroup">
              <label class="form-label">Date To</label>
              <input type="date" class="form-control" id="dateTo" style="min-width:150px;">
            </div>

            <div class="form-group" style="margin:0;">
              <label class="form-label">Format</label>
              <select class="form-control" id="exportFormat" style="min-width:100px;">
                <option value="csv">CSV</option>
                <option value="json">JSON</option>
              </select>
            </div>

            <button class="btn btn-primary" id="generateBtn" style="margin-bottom:18px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Generate
            </button>
          </div>

          <!-- Preview area — shown after generate -->
          <div id="reportPreview" style="display:none;margin-top:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
              <span class="card-title" id="previewTitle">Results</span>
              <button class="btn btn-sm" id="downloadBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download
              </button>
            </div>
            <p id="previewEmpty" style="display:none;text-align:center;padding:2rem;color:var(--text-muted);">
              No data found for the selected range.
            </p>
            <div class="table-wrapper" id="previewTableWrap">
              <table>
                <thead id="previewHead"></thead>
                <tbody id="previewBody"></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Summary + Trend ───────────────────────────────────────────── -->
      <div class="grid-2 fade-up delay-2" style="margin-bottom:16px;">

        <!-- This Month's Summary -->
        <div class="card">
          <div class="card-header">
            <span class="card-title">This Month's Summary</span>
            <span class="badge badge-maroon" id="summaryMonthLabel">—</span>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;border-top:1px solid var(--border);">
            <div class="report-metric" style="border-right:1px solid var(--border);">
              <div class="report-metric-val" id="reportEntries">—</div>
              <div class="report-metric-label">Total Entries</div>
            </div>
            <div class="report-metric">
              <div class="report-metric-val" id="reportRevenue">—</div>
              <div class="report-metric-label">Revenue</div>
            </div>
            <div class="report-metric" style="border-top:1px solid var(--border);border-right:1px solid var(--border);">
              <div class="report-metric-val" id="reportOccupancy">—</div>
              <div class="report-metric-label">Avg Occupancy</div>
            </div>
            <div class="report-metric" style="border-top:1px solid var(--border);">
              <div class="report-metric-val" id="reportDuration">—</div>
              <div class="report-metric-label">Avg Duration</div>
            </div>
          </div>
        </div>

        <!-- Monthly Revenue Trend -->
        <div class="card">
          <div class="card-header"><span class="card-title">Monthly Revenue Trend</span></div>
          <div class="card-body">
            <div class="bar-chart" id="revenueTrendChart" style="height:120px;">
              <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-muted);font-size:13px;">Loading...</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Top Vehicles ──────────────────────────────────────────────── -->
      <div class="card fade-up delay-3">
        <div class="card-header">
          <span class="card-title">Top Vehicles by Activity</span>
          <span class="badge badge-gold">This Month</span>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Rank</th>
                <th>Plate Number</th>
                <th>Owner</th>
                <th>Type</th>
                <th>Total Entries</th>
                <th>Total Hours</th>
                <th>Total Fee</th>
                <th>Avg Duration</th>
              </tr>
            </thead>
            <tbody id="topVehiclesBody">
              <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /page-content -->
  </div><!-- /main -->
</div><!-- /layout -->

<script src="../js/admin.js"></script>
<script>
// ============================================================================
// HELPERS
// ============================================================================

function formatDuration(hours) {
    if (!hours || isNaN(parseFloat(hours))) return '0H 0M';
    const h = Math.floor(parseFloat(hours));
    const m = Math.round((parseFloat(hours) - h) * 60);
    return `${h}H ${m}M`;
}

function formatCurrency(amount) {
    return '₱' + parseFloat(amount || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function currentMonthRange() {
    const now      = new Date();
    const firstDay = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`;
    const lastDay  = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
    return { firstDay, lastDay };
}

// Columns that should be formatted as currency in the preview table
const CURRENCY_COLS = new Set([
    'total_revenue', 'average_transaction', 'minimum_payment', 'maximum_payment',
    'total_parking_fees', 'total_fee', 'revenue', 'avg_transaction', 'amount'
]);

// Human-readable column labels per report type
const COLUMN_LABELS = {
    daily: {
        payment_date: 'Date', total_transactions: 'Transactions',
        unique_users: 'Unique Users', total_revenue: 'Total Revenue',
        average_transaction: 'Avg Transaction', minimum_payment: 'Min',
        maximum_payment: 'Max'
    },
    monthly: {
        year: 'Year', month: 'Month', total_parking_sessions: 'Sessions',
        unique_vehicles: 'Vehicles', unique_users: 'Users',
        total_parking_fees: 'Total Fees', avg_parking_duration: 'Avg Duration (h)'
    },
    vehicle: {
        plate_number: 'Plate', owner: 'Owner', vehicle_type: 'Type',
        total_entries: 'Entries', total_hours: 'Hours',
        total_fee: 'Total Fee', avg_duration: 'Avg Duration'
    },
    revenue: {
        date: 'Date', transactions: 'Transactions',
        total_revenue: 'Revenue', avg_transaction: 'Avg'
    },
    slots: {
        location_area: 'Area', total_slots: 'Total', available_slots: 'Available',
        occupied_slots: 'Occupied', reserved_slots: 'Reserved',
        maintenance_slots: 'Maintenance', availability_percentage: 'Avail %'
    },
    summary: {
        total_entries: 'Total Entries', revenue: 'Revenue',
        avg_duration: 'Avg Duration (h)'
    }
};

// ============================================================================
// 1. THIS MONTH'S SUMMARY
// ============================================================================
function loadSummary() {
    fetch('backend/reports/reports_summary.php')
        .then(r => r.json())
        .then(data => {
            if (data.error) { console.error('Summary:', data.error); return; }
            document.getElementById('summaryMonthLabel').textContent = data.month_label ?? '—';
            document.getElementById('reportEntries').textContent     = data.entries   ?? 0;
            document.getElementById('reportRevenue').textContent     = formatCurrency(data.revenue);
            document.getElementById('reportOccupancy').textContent   = (data.occupancy ?? 0) + '%';
            document.getElementById('reportDuration').textContent    = formatDuration(data.avg_duration);
        })
        .catch(e => console.error('loadSummary:', e));
}

// ============================================================================
// 2. MONTHLY REVENUE TREND
// ============================================================================
function loadRevenueTrend() {
    fetch('backend/reports/get_revenue_trend.php')
        .then(r => r.json())
        .then(data => {
            const chart = document.getElementById('revenueTrendChart');
            if (data.error || !Array.isArray(data) || !data.length) {
                chart.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-muted);font-size:13px;">No data available</div>';
                return;
            }

            const maxRev       = Math.max(...data.map(d => parseFloat(d.total_revenue)), 1);
            const currentMonth = new Date().getMonth() + 1;

            chart.innerHTML = data.map(d => {
                const pct      = Math.max(5, Math.round((parseFloat(d.total_revenue) / maxRev) * 100));
                const isCurrent = parseInt(d.month) === currentMonth;
                const color    = isCurrent ? 'var(--maroon)' : 'var(--gold, #c9a84c)';
                return `
                    <div class="bar-col" title="${d.month_label}: ${formatCurrency(d.total_revenue)}">
                        <div class="bar" style="height:${pct}%;background:${color};transition:height .4s ease;"></div>
                        <div class="bar-label">${d.month_label}</div>
                    </div>`;
            }).join('');
        })
        .catch(e => console.error('loadRevenueTrend:', e));
}

// ============================================================================
// 3. TOP VEHICLES
// ============================================================================
function loadTopVehicles() {
    const { firstDay, lastDay } = currentMonthRange();

    fetch(`backend/reports/top_vehicles.php?from=${firstDay}&to=${lastDay}`)
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('topVehiclesBody');
            if (data.error) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">Error loading data</td></tr>`;
                return;
            }
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No vehicle activity this month</td></tr>`;
                return;
            }
            tbody.innerHTML = data.map((v, i) => `
                <tr>
                    <td><strong>#${i + 1}</strong></td>
                    <td>${v.plate_number}</td>
                    <td>${v.owner}</td>
                    <td style="text-transform:capitalize;">${v.vehicle_type}</td>
                    <td>${v.total_entries}</td>
                    <td>${parseFloat(v.total_hours).toFixed(2)}h</td>
                    <td>${formatCurrency(v.total_fee)}</td>
                    <td>${formatDuration(v.avg_duration)}</td>
                </tr>`).join('');
        })
        .catch(e => {
            console.error('loadTopVehicles:', e);
            document.getElementById('topVehiclesBody').innerHTML =
                `<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">Failed to load data</td></tr>`;
        });
}

// ============================================================================
// 4. GENERATE REPORT
// ============================================================================
let lastReportData = [];
let lastReportType = '';

function generateReport() {
    const type   = document.getElementById('reportType').value;
    const from   = document.getElementById('dateFrom').value;
    const to     = document.getElementById('dateTo').value;
    const btn    = document.getElementById('generateBtn');

    // Slots type doesn't need dates
    if (type !== 'slots' && (!from || !to)) {
        alert('Please select a date range.');
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Loading...';

    let url = `backend/reports/reports.php?type=${encodeURIComponent(type)}`;
    if (from) url += `&from=${encodeURIComponent(from)}`;
    if (to)   url += `&to=${encodeURIComponent(to)}`;

    fetch(url)
        .then(r => r.json())
        .then(data => {
            btn.disabled  = false;
            btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Generate`;

            if (data.error) { alert('Error: ' + data.error); return; }

            lastReportData = data;
            lastReportType = type;

            const preview    = document.getElementById('reportPreview');
            const emptyEl    = document.getElementById('previewEmpty');
            const tableWrap  = document.getElementById('previewTableWrap');

            preview.style.display = 'block';
            document.getElementById('previewTitle').textContent =
                document.getElementById('reportType').selectedOptions[0].text;

            if (!data.length) {
                emptyEl.style.display   = 'block';
                tableWrap.style.display = 'none';
                return;
            }

            emptyEl.style.display   = 'none';
            tableWrap.style.display = 'block';

            const labels = COLUMN_LABELS[type] ?? {};
            const keys   = Object.keys(data[0]);

            document.getElementById('previewHead').innerHTML =
                '<tr>' + keys.map(k => `<th>${labels[k] ?? k}</th>`).join('') + '</tr>';

            document.getElementById('previewBody').innerHTML = data.map(row =>
                '<tr>' + keys.map(k => {
                    let val = row[k] ?? '—';
                    if (CURRENCY_COLS.has(k)) val = formatCurrency(val);
                    return `<td>${val}</td>`;
                }).join('') + '</tr>'
            ).join('');
        })
        .catch(e => {
            btn.disabled  = false;
            btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Generate`;
            console.error('generateReport:', e);
            alert('Failed to generate report.');
        });
}

// ============================================================================
// 5. CSV / JSON DOWNLOAD
// ============================================================================
function downloadReport() {
    if (!lastReportData.length) return;
    const format = document.getElementById('exportFormat').value;
    const name   = `${lastReportType}_report_${new Date().toISOString().split('T')[0]}`;

    if (format === 'json') {
        const blob = new Blob([JSON.stringify(lastReportData, null, 2)], { type: 'application/json' });
        triggerDownload(blob, name + '.json');
        return;
    }

    // CSV
    const labels = COLUMN_LABELS[lastReportType] ?? {};
    const keys   = Object.keys(lastReportData[0]);
    const header = keys.map(k => labels[k] ?? k).join(',');
    const rows   = lastReportData.map(row =>
        keys.map(k => `"${(row[k] ?? '').toString().replace(/"/g, '""')}"`).join(',')
    );
    const blob = new Blob([[header, ...rows].join('\n')], { type: 'text/csv' });
    triggerDownload(blob, name + '.csv');
}

function triggerDownload(blob, filename) {
    const url = URL.createObjectURL(blob);
    const a   = Object.assign(document.createElement('a'), { href: url, download: filename });
    a.click();
    URL.revokeObjectURL(url);
}

// ============================================================================
// 6. HIDE DATES FOR SLOTS TYPE (no date range needed)
// ============================================================================
function toggleDateFields() {
    const type    = document.getElementById('reportType').value;
    const display = type === 'slots' ? 'none' : 'block';
    document.getElementById('dateFromGroup').style.display = display;
    document.getElementById('dateToGroup').style.display   = display;
}

// ============================================================================
// INIT
// ============================================================================
document.addEventListener('DOMContentLoaded', () => {
    // Set default date range to current month
    const { firstDay, lastDay } = currentMonthRange();
    document.getElementById('dateFrom').value = firstDay;
    document.getElementById('dateTo').value   = lastDay;

    // Wire up events
    document.getElementById('reportType').addEventListener('change', toggleDateFields);
    document.getElementById('generateBtn').addEventListener('click',  generateReport);
    document.getElementById('downloadBtn').addEventListener('click',  downloadReport);

    // Load all live data
    loadSummary();
    loadRevenueTrend();
    loadTopVehicles();
});
</script>
</body>
</html>