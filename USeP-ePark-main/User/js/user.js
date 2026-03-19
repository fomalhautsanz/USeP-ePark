/* ── USeP ePark — user.js ── */

document.addEventListener('DOMContentLoaded', () => {

  /* ── SIDEBAR TOGGLE ── */
  const sidebar   = document.getElementById('sidebar');
  const mainArea  = document.getElementById('mainArea');
  const toggleBtn = document.getElementById('sidebarToggle');

  if (toggleBtn && sidebar && mainArea) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      mainArea.classList.toggle('expanded');
    });
  }

  /* ── STAT COUNTER ANIMATION ── */
  const statValues = document.querySelectorAll('.stat-value[data-target]');
  statValues.forEach(el => {
    const target   = parseInt(el.getAttribute('data-target'), 10);
    const duration = 800;
    const step     = Math.ceil(target / (duration / 16));
    let current    = 0;
    const timer = setInterval(() => {
      current += step;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      el.textContent = current;
    }, 16);
  });

  /* ── SLOT ITEM CLICK → MODAL (view-only for users) ── */
  const slotItems   = document.querySelectorAll('.slot-item');
  const slotModal   = document.getElementById('slotModal');
  const modalSlotNum    = document.getElementById('modalSlotNum');
  const modalSlotStatus = document.getElementById('modalSlotStatus');

  slotItems.forEach(slot => {
    slot.addEventListener('click', () => {
      if (!slotModal) return;

      // para d maka access si user sa reserved, maint, and occupied 
      if (!slot.classList.contains('available')) return;

      const number = slot.querySelector('.slot-number')?.textContent  || '';
      const status = slot.querySelector('.slot-status-label')?.textContent || '';

      // dynamic modal title
      const modalTitle = document.getElementById('modalTitle');
      if (modalTitle) modalTitle.textContent = `Reserve Slot ${number}`;

      if (modalSlotNum)    modalSlotNum.textContent    = number;
      if (modalSlotStatus) {
        modalSlotStatus.textContent = status;
        // swap badge colour based on status
        modalSlotStatus.className = 'badge';
        if (slot.classList.contains('available'))   modalSlotStatus.classList.add('badge-success');
        else if (slot.classList.contains('occupied'))    modalSlotStatus.classList.add('badge-danger');
        else if (slot.classList.contains('reserved'))    modalSlotStatus.classList.add('badge-gold');
        else if (slot.classList.contains('maintenance')) modalSlotStatus.classList.add('badge-muted');
      }

      slotModal.classList.add('open');
    });
  });

  /* ── MODAL CLOSE ── */
  const closeModal = () => {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
    // reset date input
    const dateInput = document.getElementById('ReserveDateInput');
    if (dateInput) dateInput.value = '';
    // reset type of visit
    const VisitType = document.getElementById('VisitType');
    if (VisitType) VisitType.selectedIndex = 0;
  };

  document.querySelectorAll('.modal-close, [data-close-modal]').forEach(btn => {
    btn.addEventListener('click', closeModal);
  });

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) closeModal();
    });
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
  });

  /* ── SECTION FILTER ── */
  const sectionFilter = document.getElementById('sectionFilter');
  if (sectionFilter) {
    sectionFilter.addEventListener('change', () => {
      const val = sectionFilter.value.toUpperCase();
      document.querySelectorAll('.slot-item').forEach(slot => {
        const num = slot.querySelector('.slot-number')?.textContent || '';
        if (!val || num.startsWith(val)) {
          slot.closest('[style*="margin-bottom"]') 
            ? slot.style.display = ''
            : slot.style.display = '';
          slot.style.display = '';
        } else {
          slot.style.display = 'none';
        }
      });

      // also show/hide section headers
      document.querySelectorAll('.slot-grid').forEach(grid => {
        const sectionHeader = grid.previousElementSibling;
        if (!val) {
          grid.style.display = '';
          if (sectionHeader) sectionHeader.style.display = '';
          if (grid.parentElement) grid.parentElement.style.display = '';
        } else {
          const firstSlot = grid.querySelector('.slot-number');
          const match = firstSlot?.textContent.startsWith(val);
          grid.style.display = match ? '' : 'none';
          if (sectionHeader) sectionHeader.style.display = match ? '' : 'none';
          if (grid.parentElement) grid.parentElement.style.display = match ? '' : 'none';
        }
      });
    });
  }

  /* ── NOTIFICATION BUTTON ── */
  const notifBtn = document.getElementById('notifBtn');
  if (notifBtn) {
    notifBtn.addEventListener('click', () => {
      // placeholder — hook up to real notification panel later
      console.log('Notifications clicked');
    });
  }

  /* ── DATE INPUT — disable past dates ── */
  const dateInput = document.getElementById('ReserveDateInput');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
  }

});