<?php
session_start();
include '../Admin/config/database.php';

$user_id = $_SESSION['user_id'];

var_dump($_SESSION['user_id']);

$sql = "SELECT * FROM view_logs_list
        WHERE user_id = ?
        ORDER BY time_in DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>


<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Parking History — USeP ePark</title>

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

      <a class="nav-item" href="qr.html" data-tooltip="QR Code">
        <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M7 12h10"/></svg></span>
        <span class="nav-label">QR Code</span>
      </a>

      <div class="nav-section-label">Account</div>

      <a class="nav-item active" href="parkinghistory.php" data-tooltip="Parking History">
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
<span class="sidebar-toggle-label">Collapse</span>
</button>
</div>

</aside>

      <div class="main" id="mainArea">

        <header class="topbar">

          <h2 class="topbar-title">Parking History</h2>

          <div class="topbar-spacer"></div>

          <div class="topbar-search">
            <input type="text" placeholder="Search history...">
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

        <div class="page-content">

          <div class="page-header fade-up">
            <h1>
              <?= $_SESSION['firstname'] . ' ' . $_SESSION['lastname'] ?>'s Parking History
            </h1>
            <p>Previous reservations and parking activity for your account</p>
          </div>

          <div class="history-layout">

          <div class="history-sidebar card fade-up">

            <div class="card-header">
              <span class="card-title">Reservation History</span>
            </div>

            <div class="reservation-history-list">

            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <?php
              $feeValue = $row['payment_amount'] ?? $row['parking_fee'];
              $feeLabel = ($feeValue !== null && $feeValue !== '') ? '₱' . number_format($feeValue, 2) : '—';
            ?>

            <div class="reservation-tab"
              data-slot="<?= htmlspecialchars($row['slot_number']) ?>"
              data-status="<?= htmlspecialchars(ucfirst($row['log_status'])) ?>"
              data-reserved="<?= htmlspecialchars(date('F d, Y', strtotime($row['time_in']))) ?>"
              data-timein="<?= htmlspecialchars(date('h:i A', strtotime($row['time_in']))) ?>"
              data-timeout="<?= $row['time_out'] ? htmlspecialchars(date('h:i A', strtotime($row['time_out']))) : '—' ?>"
              data-duration="<?= htmlspecialchars($row['total_duration']) ?>"
              data-fee="<?= htmlspecialchars($feeLabel) ?>"
              data-transaction-id="<?= htmlspecialchars($row['receipt_number'] ?? '') ?>">

                <div class="reservation-tab-top">

                    <span class="reservation-slot">
                        <?= $row['slot_number'] ?>
                    </span>

                    <span class="reservation-status">
                        <?= ucfirst($row['log_status']) ?>
                    </span>

                </div>

                <div class="reservation-tab-bottom">
                    <?= date('M d, Y', strtotime($row['time_in'])) ?>
                </div>

            </div>

            <?php endwhile; ?>

              <div class="no-reservation hidden">
                No reservations made
              </div>

            </div>
          </div>

          <div class="history-main card fade-up delay-1">

            <div class="card-header">
              <span class="card-title">Reservation Details</span>
            </div>

            <div class="card-body">

              <div class="payment-grid">

                <div class="payment-item">
                  <span>Parking Slot</span>
                  <strong id="detailSlot">A-01</strong>
                </div>

                <div class="payment-item">
                  <span>Status</span>
                  <strong id="detailStatus">Paid</strong>
                </div>

                <div class="payment-item">
                  <span>Reserved Date</span>
                  <strong id="detailReserved">May 12, 2026</strong>
                </div>

                <div class="payment-item payment-transaction-card transaction-link"
                  data-transaction-id="">

                  <span>Transaction</span>

                  <strong id="detailTransaction">
                    Select a reservation to view receipt
                  </strong>

                  <p class="transaction-view">
                    View Transaction →
                  </p>

                </div>

                <div class="payment-item">
                  <span>Time In</span>
                  <strong id="detailTimeIn">6:00 AM</strong>
                </div>

                <div class="payment-item">
                  <span>Time Out</span>
                  <strong id="detailTimeOut">8:30 AM</strong>
                </div>

                <div class="payment-item">
                  <span>Total Duration</span>
                  <strong id="detailDuration">2 Hours</strong>
                </div>

                <div class="payment-item payment-fee-card">
                <span>Parking Fee</span>
                <strong id="detailFee">₱40</strong>
              </div>

              </div>

            </div>

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
    <script src="../User/js/parkinghistory.js"></script>

  </body>

</html>