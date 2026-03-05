const sidebar = document.getElementById('sidebar');
const mainArea = document.getElementById('mainArea');
const toggleBtn = document.getElementById('sidebarToggle');

function initSidebar() {
  if (!sidebar) return;
  const collapsed = localStorage.getItem('eparkSidebarCollapsed') === 'true';
  if (collapsed) {
    sidebar.classList.add('collapsed');
    mainArea && mainArea.classList.add('expanded');
  }
  toggleBtn && toggleBtn.addEventListener('click', () => {
    const isCollapsed = sidebar.classList.toggle('collapsed');
    mainArea && mainArea.classList.toggle('expanded', isCollapsed);
    localStorage.setItem('eparkSidebarCollapsed', isCollapsed);
  });
}

function initModals() {
  document.querySelectorAll('[data-open-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-open-modal');
      const modal = document.getElementById(id);
      if (modal) modal.classList.add('open');
    });
  });

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) overlay.classList.remove('open');
    });
    const closeBtn = overlay.querySelector('.modal-close, [data-close-modal]');
    closeBtn && closeBtn.addEventListener('click', () => overlay.classList.remove('open'));
  });
}

function initTooltips() {
  document.querySelectorAll('[data-tooltip]').forEach(el => {
    const isSidebarItem = el.closest('.sidebar');
    if (!isSidebarItem) return;
    el.addEventListener('mouseenter', () => {
      if (!sidebar.classList.contains('collapsed')) return;
    });
  });
}

function animateStats() {
  document.querySelectorAll('.stat-value[data-target]').forEach(el => {
    const target = parseInt(el.getAttribute('data-target'));
    const prefix = el.getAttribute('data-prefix') || '';
    const suffix = el.getAttribute('data-suffix') || '';
    let current = 0;
    const step = Math.ceil(target / 40);
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = prefix + current.toLocaleString() + suffix;
      if (current >= target) clearInterval(timer);
    }, 25);
  });
}

function initFadeUp() {
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animationPlayState = 'running';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.fade-up').forEach(el => {
    el.style.animationPlayState = 'paused';
    observer.observe(el);
  });
}

function initSearch() {
  const searchInputs = document.querySelectorAll('[data-search-table]');
  searchInputs.forEach(input => {
    const tableId = input.getAttribute('data-search-table');
    const table = document.getElementById(tableId);
    if (!table) return;
    input.addEventListener('input', () => {
      const q = input.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });
}

function initFilterSelect() {
  document.querySelectorAll('[data-filter-col]').forEach(select => {
    const tableId = select.getAttribute('data-filter-table');
    const col = parseInt(select.getAttribute('data-filter-col'));
    const table = document.getElementById(tableId);
    if (!table) return;
    select.addEventListener('change', () => {
      const val = select.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(row => {
        const cell = row.cells[col];
        if (!cell) return;
        row.style.display = (!val || cell.textContent.toLowerCase().includes(val)) ? '' : 'none';
      });
    });
  });
}

function initSlots() {
  document.querySelectorAll('.slot-item').forEach(slot => {
    slot.addEventListener('click', () => {
      const modal = document.getElementById('slotModal');
      if (!modal) return;
      const number = slot.querySelector('.slot-number')?.textContent;
      const status = slot.className.includes('occupied') ? 'Occupied'
        : slot.className.includes('reserved') ? 'Reserved'
        : slot.className.includes('maintenance') ? 'Under Maintenance'
        : 'Available';
      const numEl = document.getElementById('modalSlotNum');
      const statEl = document.getElementById('modalSlotStatus');
      if (numEl) numEl.textContent = number;
      if (statEl) {
        statEl.textContent = status;
        statEl.className = 'badge ' + (
          status === 'Available' ? 'badge-success' :
          status === 'Occupied' ? 'badge-danger' :
          status === 'Reserved' ? 'badge-gold' : 'badge-muted'
        );
      }
      modal.classList.add('open');
    });
  });
}

function initCharts() {
  const donutEl = document.getElementById('donutSvg');
  if (donutEl) {
    const data = [
      { val: 24, color: '#2D7A4F', label: 'Available' },
      { val: 18, color: '#C0392B', label: 'Occupied' },
      { val:  6, color: '#C9A84C', label: 'Reserved' },
      { val:  2, color: '#A0A0A0', label: 'Maintenance' },
    ];
    const total = data.reduce((a, b) => a + b.val, 0);
    const cx = 60, cy = 60, r = 48, strokeW = 14;
    const circ = 2 * Math.PI * r;
    let offset = 0;
    donutEl.innerHTML = data.map(d => {
      const pct = d.val / total;
      const dash = pct * circ;
      const gap = circ - dash;
      const el = `<circle cx="${cx}" cy="${cy}" r="${r}" fill="none" stroke="${d.color}" stroke-width="${strokeW}" stroke-dasharray="${dash} ${gap}" stroke-dashoffset="${-offset * circ / 1 + circ * 0.25}" transform="rotate(-90 ${cx} ${cy})"/>`;
      offset += pct;
      return el;
    }).join('');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initModals();
  initTooltips();
  initSearch();
  initFilterSelect();
  initSlots();
  initCharts();
  setTimeout(animateStats, 200);
  initFadeUp();
});
