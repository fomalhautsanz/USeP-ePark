/* =============================================================================
   User/js/parkingreservations.js
   Handles:
     - Loading live slot map from get_slots.php
     - Stat card counts
     - Active reservation status banner + countdown
     - Slot click → confirm modal → submit_reservation.php
     - Cancel reservation → cancel_reservation.php
     - Auto-refresh every 30 s
============================================================================= */

(function () {
  'use strict';

  // ── DOM refs ────────────────────────────────────────────────────────────────
  const slotGrids       = document.getElementById('slotGrids');
  const statAvailable   = document.getElementById('statAvailable');
  const statOccupied    = document.getElementById('statOccupied');
  const statReserved    = document.getElementById('statReserved');
  const statMaintenance = document.getElementById('statMaintenance');

  const reservationBanner   = document.getElementById('reservationBanner');
  const bannerRef           = document.getElementById('bannerRef');
  const bannerSlot          = document.getElementById('bannerSlot');
  const bannerExpiry        = document.getElementById('bannerExpiry');
  const bannerCountdown     = document.getElementById('bannerCountdown');
  const cancelBannerBtn     = document.getElementById('cancelBannerBtn');

  const slotModal           = document.getElementById('slotModal');
  const modalSlotNum        = document.getElementById('modalSlotNum');
  const modalSlotStatus     = document.getElementById('modalSlotStatus');
  const modalTitle          = document.getElementById('modalTitle');
  const confirmReserveBtn   = document.getElementById('confirmReserveBtn');
  const modalFeedback       = document.getElementById('modalFeedback');

  const sectionFilter       = document.getElementById('sectionFilter');

  let selectedSlotId    = null;
  let countdownInterval = null;
  let refreshInterval   = null;
  let currentReservation = null;

  // ── Helpers ─────────────────────────────────────────────────────────────────
  function showFeedback(el, msg, isError = false) {
    if (!el) return;
    el.textContent  = msg;
    el.className    = 'modal-feedback ' + (isError ? 'text-danger' : 'text-success');
    el.style.display = 'block';
  }

  function hideFeedback(el) {
    if (!el) return;
    el.style.display = 'none';
    el.textContent   = '';
  }

  function closeModal() {
    if (slotModal) slotModal.classList.remove('open');
    selectedSlotId = null;
    hideFeedback(modalFeedback);
    if (confirmReserveBtn) {
      confirmReserveBtn.disabled    = false;
      confirmReserveBtn.textContent = 'Confirm Reservation';
    }
  }

  // ── Countdown timer ──────────────────────────────────────────────────────────
  function startCountdown(secondsLeft) {
    clearInterval(countdownInterval);
    if (!bannerCountdown) return;

    function tick() {
      if (secondsLeft <= 0) {
        clearInterval(countdownInterval);
        bannerCountdown.textContent = 'Expired';
        // Re-fetch to sync status
        loadSlots();
        return;
      }
      const m = Math.floor(secondsLeft / 60);
      const s = secondsLeft % 60;
      bannerCountdown.textContent = `${m}m ${String(s).padStart(2, '0')}s remaining`;
      secondsLeft--;
    }

    tick();
    countdownInterval = setInterval(tick, 1000);
  }

  // ── Show / hide active reservation banner ───────────────────────────────────
  function renderBanner(reservation) {
    if (!reservationBanner) return;

    if (!reservation) {
      reservationBanner.style.display = 'none';
      clearInterval(countdownInterval);
      return;
    }

    currentReservation = reservation;
    reservationBanner.style.display = 'flex';

    if (bannerRef)    bannerRef.textContent    = reservation.ref_number;
    if (bannerSlot)   bannerSlot.textContent   = reservation.slot_number + ' (' + reservation.location_area + ')';
    if (bannerExpiry) bannerExpiry.textContent = reservation.expiry_label;

    startCountdown(parseInt(reservation.seconds_until_expiry, 10));
  }

  // ── Render slot grid ─────────────────────────────────────────────────────────
  function renderSlots(slots, activeReservation) {
    if (!slotGrids) return;

    // Group by location_area
    const sections = {};
    slots.forEach(s => {
      if (!sections[s.location_area]) sections[s.location_area] = [];
      sections[s.location_area].push(s);
    });

    slotGrids.innerHTML = '';

    const sectionLabels = { A: 'Section A — Cars', B: 'Section B — Cars', C: 'Section C — Motorcycles' };

    Object.keys(sections).sort().forEach(area => {
      const wrapper = document.createElement('div');
      wrapper.style.marginBottom = '20px';
      wrapper.dataset.section = area;

      const header = document.createElement('div');
      header.className = 'slot-section-header';
      header.textContent = sectionLabels[area] || ('Section ' + area);
      wrapper.appendChild(header);

      const grid = document.createElement('div');
      grid.className = 'slot-grid';

      sections[area].forEach(slot => {
        const item = document.createElement('div');
        const statusClass = slot.status === 'available' ? 'available'
                          : slot.status === 'occupied'  ? 'occupied'
                          : slot.status === 'reserved'  ? 'reserved'
                          : 'maintenance';

        // Highlight the user's own reserved slot
        const isOwned = activeReservation && parseInt(slot.slot_id) === parseInt(activeReservation.slot_id);
        item.className = 'slot-item ' + statusClass + (isOwned ? ' my-slot' : '');
        item.dataset.slotId     = slot.slot_id;
        item.dataset.slotNumber = slot.slot_number;
        item.dataset.status     = slot.status;

        item.innerHTML = `
          <div class="slot-number">${slot.slot_number}</div>
          <div class="slot-status-label">${isOwned ? 'Mine' : ucfirst(slot.status)}</div>
        `;

        // Only available slots are clickable (and only if user has no active reservation)
        if (slot.status === 'available' && !activeReservation) {
          item.addEventListener('click', () => openReserveModal(slot));
        } else if (isOwned) {
          item.title = 'This is your reserved slot (' + activeReservation.ref_number + ')';
        }

        grid.appendChild(item);
      });

      wrapper.appendChild(grid);
      slotGrids.appendChild(wrapper);
    });

    // Re-apply section filter if active
    applySectionFilter();
  }

  function ucfirst(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
  }

  // ── Open reservation confirmation modal ──────────────────────────────────────
  function openReserveModal(slot) {
    selectedSlotId = slot.slot_id;
    if (modalTitle)      modalTitle.textContent      = `Reserve Slot ${slot.slot_number}`;
    if (modalSlotNum)    modalSlotNum.textContent    = slot.slot_number;
    if (modalSlotStatus) {
      modalSlotStatus.textContent = 'Available';
      modalSlotStatus.className   = 'badge badge-success';
    }
    hideFeedback(modalFeedback);
    if (confirmReserveBtn) {
      confirmReserveBtn.disabled    = false;
      confirmReserveBtn.textContent = 'Confirm Reservation';
    }
    if (slotModal) slotModal.classList.add('open');
  }

  // ── Submit reservation ───────────────────────────────────────────────────────
  async function submitReservation() {
    if (!selectedSlotId) return;

    confirmReserveBtn.disabled    = true;
    confirmReserveBtn.textContent = 'Reserving…';
    hideFeedback(modalFeedback);

    try {
      const res  = await fetch('backend/submit_reservation.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ slot_id: selectedSlotId }),
      });
      const data = await res.json();

      if (data.success) {
        showFeedback(modalFeedback, '✓ ' + data.message, false);
        setTimeout(() => {
          closeModal();
          loadSlots(); // refresh everything
        }, 1800);
      } else {
        showFeedback(modalFeedback, data.message, true);
        confirmReserveBtn.disabled    = false;
        confirmReserveBtn.textContent = 'Confirm Reservation';
      }
    } catch {
      showFeedback(modalFeedback, 'Network error. Please try again.', true);
      confirmReserveBtn.disabled    = false;
      confirmReserveBtn.textContent = 'Confirm Reservation';
    }
  }

  // ── Cancel reservation ───────────────────────────────────────────────────────
  async function cancelReservation() {
    if (!currentReservation) return;

    const confirmed = confirm(
      `Cancel reservation ${currentReservation.ref_number} for slot ${currentReservation.slot_number}?`
    );
    if (!confirmed) return;

    if (cancelBannerBtn) {
      cancelBannerBtn.disabled    = true;
      cancelBannerBtn.textContent = 'Cancelling…';
    }

    try {
      const res  = await fetch('backend/cancel_reservation.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ reservation_id: currentReservation.reservation_id }),
      });
      const data = await res.json();

      if (data.success) {
        currentReservation = null;
        loadSlots();
      } else {
        alert(data.message);
        if (cancelBannerBtn) {
          cancelBannerBtn.disabled    = false;
          cancelBannerBtn.textContent = 'Cancel Reservation';
        }
      }
    } catch {
      alert('Network error. Please try again.');
      if (cancelBannerBtn) {
        cancelBannerBtn.disabled    = false;
        cancelBannerBtn.textContent = 'Cancel Reservation';
      }
    }
  }

  // ── Section filter ───────────────────────────────────────────────────────────
  function applySectionFilter() {
    if (!sectionFilter) return;
    const val = sectionFilter.value.toUpperCase();

    document.querySelectorAll('#slotGrids > div[data-section]').forEach(wrapper => {
      wrapper.style.display = (!val || wrapper.dataset.section === val) ? '' : 'none';
    });
  }

  // ── Load everything from get_slots.php ──────────────────────────────────────
  async function loadSlots() {
    try {
      const res  = await fetch('backend/get_slots.php');
      const data = await res.json();

      if (!data.success) {
        console.error('get_slots:', data.message);
        return;
      }

      // Stat cards
      if (statAvailable)   statAvailable.textContent   = data.stats.available   ?? 0;
      if (statOccupied)    statOccupied.textContent    = data.stats.occupied    ?? 0;
      if (statReserved)    statReserved.textContent    = data.stats.reserved    ?? 0;
      if (statMaintenance) statMaintenance.textContent = data.stats.maintenance ?? 0;

      // Active reservation banner
      renderBanner(data.active_reservation);

      // Slot map
      renderSlots(data.slots, data.active_reservation);

    } catch (err) {
      console.error('Failed to load slots:', err);
    }
  }

  // ── Init ────────────────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', () => {
    loadSlots();

    // Auto-refresh slot map every 30 s
    refreshInterval = setInterval(loadSlots, 30_000);

    // Confirm reservation button
    if (confirmReserveBtn) {
      confirmReserveBtn.addEventListener('click', submitReservation);
    }

    // Cancel active reservation from banner
    if (cancelBannerBtn) {
      cancelBannerBtn.addEventListener('click', cancelReservation);
    }

    // Section filter dropdown
    if (sectionFilter) {
      sectionFilter.addEventListener('change', applySectionFilter);
    }

    // Modal close handlers (reuse existing .modal-close / data-close-modal pattern)
    document.querySelectorAll('.modal-close, [data-close-modal]').forEach(btn => {
      btn.addEventListener('click', closeModal);
    });
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    // Manual refresh button
    const refreshBtn = document.getElementById('refreshSlotsBtn');
    if (refreshBtn) refreshBtn.addEventListener('click', loadSlots);
  });

})();