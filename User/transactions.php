<?php
session_start();
include '../Admin/config/database.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("User not logged in.");
}

$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM view_logs_list WHERE user_id = ?";
$params = [$user_id];
$types = 'i';

if ($search) {
    $sql .= " AND (receipt_number LIKE ? OR slot_number LIKE ? OR log_status LIKE ? OR DATE_FORMAT(time_in, '%M %d, %Y') LIKE ? OR DATE_FORMAT(time_in, '%Y-%m-%d') LIKE ? OR TIME_FORMAT(time_in, '%h:%i %p') LIKE ? OR TIME_FORMAT(time_out, '%h:%i %p') LIKE ? OR CONCAT(total_duration, ' hour(s)') LIKE ? OR (CASE WHEN log_status = 'denied' THEN 'cancelled' WHEN log_status = 'out' AND receipt_number IS NOT NULL THEN 'paid' ELSE 'expired' END) LIKE ?)";
    $params = array_merge($params, array_fill(0, 9, "%$search%"));
    $types .= str_repeat('s', 9);
}

$sql .= " ORDER BY time_in DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$transactions = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Transactions / Receipts — USeP ePark</title>

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
<img src="../assets/logo-icon.svg" class="sidebar-logo">
<img src="../assets/logo-white.svg" class="sidebar-brand">
</div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>

      <a class="nav-item" href="userDashboard.php" data-tooltip="Profile">
        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg></span>
        <span class="nav-label">Profile</span>
      </a>

      <a class="nav-item" href="parkingreservations.php" data-tooltip="Parking Reservations">
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

      <a class="nav-item active" href="transactions.php" data-tooltip="Transactions / Receipts">
        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2"/><path d="M3 11h3c.8 0 1.6.3 2.1.9l1.1.9c1.6 1.6 4.1 1.6 5.7 0l1.1-.9c.5-.5 1.3-.9 2.1-.9H21"/></svg></span>
        <span class="nav-label">Transactions / Receipts</span>
      </a>
    </nav>

<div class="sidebar-footer">
<button class="sidebar-toggle" id="sidebarToggle">
<span class="sidebar-toggle-label">Collapse</span>
</button>
</div>

</aside>

<!-- ── MAIN AREA ── -->

<div class="main" id="mainArea">

<header class="topbar">

<h2 class="topbar-title">Transactions / Receipts</h2>

<div class="topbar-spacer"></div>

<div class="topbar-search">
<form method="GET" style="display: flex;">
<input type="text" name="search" placeholder="Search transactions..." value="<?= htmlspecialchars($search) ?>">
</form>
</div>

<div class="topbar-user">
<img src="../assets/avatars/avatar-customer.svg" class="topbar-avatar">
<div class="topbar-user-info">
<div class="topbar-user-name">
  <?= $_SESSION['firstname'] . ' ' . $_SESSION['lastname'] ?>
</div>
<div class="topbar-user-role">Customer</div>
</div>
</div>

</header>

<!-- ── PAGE CONTENT ── -->

<div class="page-content">

<div class="page-header fade-up">
<h1>Parking Transactions</h1>
<p>Search by date, status, or receipt to view a transaction receipt.</p>
</div>

<div class="toolbar fade-up">
  <div class="toolbar-search">
    <input id="transactionSearch" type="search" placeholder="Search receipt, slot, status...">
  </div>
  <div class="form-group">
    <label class="form-label" for="filterStatus">Status</label>
    <select id="filterStatus" class="form-control">
      <option value="">All statuses</option>
      <option value="paid">Paid</option>
      <option value="cancelled">Cancelled</option>
      <option value="expired">Expired</option>
    </select>
  </div>
  <div class="form-group">
    <label class="form-label" for="filterDateFrom">Date from</label>
    <input id="filterDateFrom" class="form-control" type="date">
  </div>
  <div class="form-group">
    <label class="form-label" for="filterDateTo">Date to</label>
    <input id="filterDateTo" class="form-control" type="date">
  </div>
</div>

<div class="card fade-up">
  <div class="card-header">
    <span class="card-title">Transactions</span>
    <span class="text-muted" id="transactionCount"><?= count($transactions) ?> records</span>
  </div>
  <div class="card-body">
    <div class="transaction-list" id="transactionList">
      <?php if (empty($transactions)): ?>
        <div class="empty-state" id="transactionEmpty">
          <p>No transactions available.</p>
        </div>
      <?php else: ?>
        <?php foreach ($transactions as $txn): ?>
          <?php
            $rawStatus = strtolower($txn['log_status'] ?? '');
            $hasPayment = !empty($txn['receipt_number']);
            $displayStatus = 'expired';

            if ($rawStatus === 'denied') {
                $displayStatus = 'cancelled';
            } elseif ($rawStatus === 'out' && $hasPayment) {
                $displayStatus = 'paid';
            } elseif ($rawStatus === 'out') {
                $displayStatus = 'expired';
            }

            $statusLabel = ucfirst($displayStatus);
            $badgeClass = match($displayStatus) {
              'paid' => 'badge-success',
              'cancelled' => 'badge-danger',
              'expired' => 'badge-warning',
              default => 'badge-muted'
            };
            $dateValue = date('Y-m-d', strtotime($txn['time_in'] ?? ''));
            $displayDate = date('M d, Y', strtotime($txn['time_in'] ?? ''));
            $timeIn = date('h:i A', strtotime($txn['time_in'] ?? ''));
            $timeOut = $txn['time_out'] ? date('h:i A', strtotime($txn['time_out'])) : '—';
          ?>
          <button type="button" class="transaction-card" data-transaction-id="<?= htmlspecialchars($txn['receipt_number']) ?>"
            data-status="<?= htmlspecialchars($displayStatus) ?>"
            data-slot="<?= htmlspecialchars($txn['slot_number']) ?>"
            data-fee="<?= htmlspecialchars($txn['payment_amount']) ?>"
            data-time-in="<?= htmlspecialchars($timeIn) ?>"
            data-time-out="<?= htmlspecialchars($timeOut) ?>"
            data-duration="<?= htmlspecialchars($txn['total_duration']) ?>"
            data-date="<?= htmlspecialchars($dateValue) ?>"
            data-created-date="<?= htmlspecialchars($displayDate) ?>"
          >
            <div class="transaction-card-row">
              <div>
                <div class="transaction-number">#<?= htmlspecialchars($txn['receipt_number']) ?></div>
                <div class="transaction-meta-text"><?= htmlspecialchars($displayDate) ?> · Slot <?= htmlspecialchars($txn['slot_number']) ?></div>
              </div>
              <div class="transaction-card-badges">
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
                <span class="transaction-amount">₱<?= htmlspecialchars($txn['payment_amount']) ?></span>
              </div>
            </div>
          </button>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

</div>

<div class="modal-overlay" id="transactionModal" aria-hidden="true">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div class="modal-title">Transaction Details</div>
        <div class="transaction-meta-text text-muted" id="modalDate"></div>
      </div>
      <button class="modal-close btn-icon" id="closeTransactionModal" type="button" aria-label="Close modal">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group">
          <span class="form-label">Receipt</span>
          <div id="modalReceipt" class="transaction-number"></div>
        </div>
        <div class="form-group">
          <span class="form-label">Status</span>
          <span id="modalStatus" class="badge badge-muted"></span>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <span class="form-label">Slot</span>
          <div id="modalSlot">—</div>
        </div>
        <div class="form-group">
          <span class="form-label">Duration</span>
          <div id="modalDuration">—</div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <span class="form-label">Time In</span>
          <div id="modalTimeIn">—</div>
        </div>
        <div class="form-group">
          <span class="form-label">Time Out</span>
          <div id="modalTimeOut">—</div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <span class="form-label">Amount Paid</span>
          <div id="modalFee">—</div>
        </div>
        <div class="form-group">
          <span class="form-label">Status</span>
          <div id="modalStatusText">—</div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" id="closeTransactionButton" type="button">Close</button>
      <button class="btn btn-primary" id="printReceiptButton" type="button">Print Receipt</button>
    </div>
  </div>
</div>

<footer class="footer">
<p>Copyright © 2026. All Rights Reserved.</p>
<p><a href="">Terms of Service</a> | <a href="">Privacy Policy</a></p>
</footer>

</div>
</div>

<script src="../User/js/user.js"></script>
<script src="../User/js/transactions.js"></script>

</body>
</html>