/* ── USeP ePark — user.js ── */

document.addEventListener('DOMContentLoaded', () => {

  // Load session user into topbar
fetch('/Admin/backend/auth/session_user.php')
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            window.location.href = 'http://localhost:8000/Admin/login.html';
            return;
        }
        const nameEl   = document.querySelector('.topbar-user-name');
        const roleEl   = document.querySelector('.topbar-user-role');
        const avatarEl = document.querySelector('.topbar-avatar img');

        if (nameEl)   nameEl.textContent = data.firstname + ' ' + data.lastname;
        if (roleEl)   roleEl.textContent = data.role.charAt(0).toUpperCase() + data.role.slice(1);
        if (avatarEl) avatarEl.src = `/assets/avatars/avatar-${data.role}.svg`;
    })
    .catch(() => {
        window.location.href = 'http://localhost:8000/Admin/login.html';
    });

// Logout
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
        fetch('/Admin/backend/auth/logout.php')
            .then(() => {
                window.location.href = 'http://localhost:8000/Admin/login.html';
            });
    });
}

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

  /* ── SLOT ITEM CLICK → MODAL ── */
  const slotItems       = document.querySelectorAll('.slot-item');
  const slotModal       = document.getElementById('slotModal');
  const modalSlotNum    = document.getElementById('modalSlotNum');
  const modalSlotStatus = document.getElementById('modalSlotStatus');

  slotItems.forEach(slot => {
    slot.addEventListener('click', () => {
      if (!slotModal) return;

      // only available slots can be reserved
      if (!slot.classList.contains('available')) return;

      const number = slot.querySelector('.slot-number')?.textContent  || '';
      const status = slot.querySelector('.slot-status-label')?.textContent || '';

      // dynamic modal title
      const modalTitle = document.getElementById('modalTitle');
      if (modalTitle) modalTitle.textContent = `Reserve Slot ${number}`;

      if (modalSlotNum) modalSlotNum.textContent = number;
      if (modalSlotStatus) {
        modalSlotStatus.textContent = status;
        modalSlotStatus.className = 'badge';
        if (slot.classList.contains('available'))        modalSlotStatus.classList.add('badge-success');
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
    const dateInput = document.getElementById('ReserveDateInput');
    if (dateInput) dateInput.value = '';
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
        slot.style.display = (!val || num.startsWith(val)) ? '' : 'none';
      });

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
      console.log('Notifications clicked');
    });
  }

  /* ── DATE INPUT — disable past dates ── */
  const dateInput = document.getElementById('ReserveDateInput');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
  }

<<<<<<< HEAD
});

=======
  /* ── PASSWORD TOGGLE ── */
  window.togglePw = function(id) {
    const input = document.getElementById(id);
    if (input) {
      input.type = input.type === 'password' ? 'text' : 'password';
    }
  };

});

/* ── TRANSACTIONS ── */

const hourlyRate = 20;

function generateReceipt() {
  let entry = new Date();
  let exit  = new Date(entry.getTime() + 7200000); // demo: +2 hours
  let durationHours = Math.ceil((exit - entry) / 3600000);
  let total = durationHours * hourlyRate;

  const set = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };

  set('entryTime',  entry.toLocaleTimeString());
  set('exitTime',   exit.toLocaleTimeString());
  set('duration',   durationHours + ' hour(s)');
  set('totalFee',   '₱' + total);
  set('receiptFee', '₱' + total);

  let txnId = 'TXN-' + Math.floor(Math.random() * 1000000);
  set('txnId',   txnId);
  set('txnDate', new Date().toLocaleString());

  const qrCanvas = document.getElementById('qrCode');
  if (qrCanvas && typeof QRCode !== 'undefined') {
    QRCode.toCanvas(qrCanvas, txnId);
  }
}

if (document.getElementById('txnId')) generateReceipt();

function printReceipt() {
  window.print();
}
>>>>>>> 77b71522cb851370495146f0002e23b88bcb0197
