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

function initCreateUser() {
    const form = document.getElementById("createUserForm");
    if (!form) return;

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch("backend/users/create_user.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            console.log("Raw server response:", JSON.stringify(data));

            if (data.trim().startsWith("success")) {
                form.reset();
                document.getElementById("addUserModal").classList.remove("open");
                loadUsers();
            } else {
                console.error("Unexpected response:", data);
                alert("Error creating user: " + data);
            }
        })
        .catch(err => {
            console.error("Fetch error:", err);
        });
    });
}

function loadUsers() {
    const tbody = document.querySelector("#usersTable tbody");
    if (!tbody) return;

    fetch("backend/users/get_users.php")
        .then(res => res.json())
        .then(users => {
            tbody.innerHTML = ""; 

            if (users.error) {
                console.error("Error loading users:", users.error);
                return;
            }

            users.forEach(user => {
                const roleBadgeMap = {
                    student:    "badge-muted",
                    faculty:    "badge-info",
                    staff:      "badge-gold",
                    admin:      "badge-maroon",
                };
                const badgeClass = roleBadgeMap[user.role] || "badge-muted";
                const roleLabel  = user.role.charAt(0).toUpperCase() + user.role.slice(1);
                const fullName   = `${user.firstname} ${user.lastname}`;

                const joinedDate = user.created_at
                    ? new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
                    : "—";
                const lastLogin  = user.last_login
                    ? new Date(user.last_login).toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
                    : "—";

                const statusDot  = user.status === 'active'
                    ? `<span class="badge badge-success"><span class="badge-dot"></span>Active</span>`
                    : `<span class="badge badge-warning"><span class="badge-dot"></span>Suspended</span>`;

                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>
                        <div class="user-card">
                            <img src="../assets/avatars/avatar-${user.role}.svg" alt="${fullName}"
                                 style="width:38px;height:38px;border-radius:8px;object-fit:cover;flex-shrink:0;"
                                 onerror="this.src='../assets/avatars/avatar-student.svg'">
                            <div>
                                <div class="user-info-name">${fullName}</div>
                                <div class="user-info-sub">${user.email}</div>
                                <div class="user-info-sub">${user.contact_number}</div>
                            </div>
                        </div>
                    </td>
                    <td class="td-mono" style="font-size:11px;">${user.user_code}</td>
                    <td style="font-size:13px;color:var(--text-muted);">
                        ${user.vehicle_count > 0 
                            ? `${user.vehicle_count} vehicle${user.vehicle_count > 1 ? 's' : ''}` 
                            : '—'}
                    </td>
                    <td><span class="badge ${badgeClass}">${roleLabel}</span></td>
                    <td>${statusDot}</td>
                    <td style="font-size:12px;color:var(--text-muted);">${lastLogin}</td>
                    <td style="font-size:12px;color:var(--text-muted);">${joinedDate}</td>
                    <td>
                      <div style="display:flex;gap:4px;">
                              <!-- View -->
                              <button class="btn btn-outline btn-icon btn-sm"
                                  onclick='openViewModal(${JSON.stringify(user)})'>
                                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                              </button>

                              <!-- Edit -->
                              <button class="btn btn-outline btn-icon btn-sm"
                                  onclick='openEditModal(${JSON.stringify(user)})'>
                                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                              </button>

                              <!-- Suspend / Restore -->
                              ${user.status === 'active'
                                  ? `<button class="btn btn-outline btn-icon btn-sm" style="color:var(--warning);"
                                      onclick='openSuspendModal(${JSON.stringify(user)})'>
                                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    </button>`
                                  : `<button class="btn btn-sm" style="background:var(--success-bg);color:var(--success);border:1px solid var(--success);font-size:11px;padding:5px 10px;"
                                      onclick='openSuspendModal(${JSON.stringify(user)})'>
                                      Restore
                                    </button>`
                              }
                      </div>
                  </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(err => console.error("Fetch error:", err));
}

function loadStats() {
    if (!document.getElementById('usersTable')) return;

    fetch("backend/users/get_stats.php")
        .then(res => res.json())
        .then(stats => {
            if (stats.error) {
                console.error("Error loading stats:", stats.error);
                return;
            }

            const statMap = {
                total:     '[data-target="total"]',
                active:    '[data-target="active"]',
                suspended: '[data-target="suspended"]',
                admins:    '[data-target="admins"]',
            };

            document.querySelector('.stat-card:nth-child(1) .stat-value').setAttribute('data-target', stats.total);
            document.querySelector('.stat-card:nth-child(2) .stat-value').setAttribute('data-target', stats.active);
            document.querySelector('.stat-card:nth-child(3) .stat-value').setAttribute('data-target', stats.suspended);
            document.querySelector('.stat-card:nth-child(4) .stat-value').setAttribute('data-target', stats.admins);

            animateStats();
        })
        .catch(err => console.error("Stats fetch error:", err));
}

function initEditUser() {
    const form = document.getElementById("editUserForm");
    if (!form) return;

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(form);

        fetch("backend/users/update_user.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById("editUserModal").classList.remove("open");
                loadUsers();
                loadStats();
            } else {
                alert("Error updating user: " + data.message);
            }
        })
        .catch(err => console.error("Update error:", err));
    });

    document.getElementById("deleteUserBtn").addEventListener("click", function () {
        const name = document.getElementById("editFirstName").value
                   + " " + document.getElementById("editLastName").value;
        const userId = document.getElementById("editUserId").value;

        document.getElementById("deleteUserName").textContent = name;
        document.getElementById("deleteUserCode").textContent = "User ID: " + userId;
        document.getElementById("confirmDeleteBtn").dataset.userId = userId;

        document.getElementById("editUserModal").classList.remove("open");
        document.getElementById("deleteConfirmModal").classList.add("open");
    });

    document.getElementById("cancelDeleteBtn").addEventListener("click", function () {
        document.getElementById("deleteConfirmModal").classList.remove("open");
        document.getElementById("editUserModal").classList.add("open");
    });

    document.getElementById("confirmDeleteBtn").addEventListener("click", function () {
        const userId = this.dataset.userId;
        const formData = new FormData();
        formData.append("user_id", userId);

        fetch("backend/users/delete_user.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById("deleteConfirmModal").classList.remove("open");
                loadUsers();
                loadStats();
            } else {
                alert("Error deleting user: " + data.message);
            }
        })
        .catch(err => console.error("Delete error:", err));
    });
}

function openEditModal(user) {
    document.getElementById("editUserId").value    = user.user_id;
    document.getElementById("editFirstName").value = user.firstname;
    document.getElementById("editLastName").value  = user.lastname;
    document.getElementById("editEmail").value     = user.email;
    document.getElementById("editPhone").value     = user.contact_number;
    document.getElementById("editRole").value      = user.role;
    document.getElementById("editPassword").value  = "";

    document.getElementById("editUserModal").classList.add("open");
}

function openViewModal(user) {
    const roleBadgeMap = {
        student: "badge-muted",
        faculty: "badge-info",
        staff:   "badge-gold",
        admin:   "badge-maroon",
    };

    document.getElementById("viewAvatar").src =
        `../assets/avatars/avatar-${user.role}.svg`;
    document.getElementById("viewFullName").textContent =
        `${user.firstname} ${user.lastname}`;
    document.getElementById("viewUserCode").textContent = user.user_code;

    const statusBadge = user.status === 'active'
        ? `<span class="badge badge-success"><span class="badge-dot"></span>Active</span>`
        : `<span class="badge badge-danger"><span class="badge-dot"></span>Suspended</span>`;
    document.getElementById("viewStatusBadge").innerHTML = statusBadge;

    const roleLabel = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    const badgeClass = roleBadgeMap[user.role] || "badge-muted";
    document.getElementById("viewRoleBadge").innerHTML =
        `<span class="badge ${badgeClass}">${roleLabel}</span>`;

    document.getElementById("viewEmail").textContent     = user.email;
    document.getElementById("viewPhone").textContent     = user.contact_number;
    document.getElementById("viewVehicles").textContent  = user.vehicle_count
        ? `${user.vehicle_count} vehicle${user.vehicle_count > 1 ? 's' : ''}`
        : '—';

    document.getElementById("viewJoined").textContent = user.created_at
        ? new Date(user.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
        : '—';
    document.getElementById("viewLastLogin").textContent = user.last_login
        ? new Date(user.last_login).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
        : 'Never';

    document.getElementById("viewUserModal").classList.add("open");
}

function initSuspendUser() {
     const cancelBtn = document.getElementById("cancelSuspendBtn");
    if (!cancelBtn) return;

    document.getElementById("cancelSuspendBtn").addEventListener("click", () => {
        document.getElementById("suspendConfirmModal").classList.remove("open");
    });

    document.getElementById("confirmSuspendBtn").addEventListener("click", function () {
        const userId    = this.dataset.userId;
        const newStatus = this.dataset.status;
        const formData  = new FormData();
        formData.append("user_id", userId);
        formData.append("status", newStatus);

        fetch("backend/users/update_status.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById("suspendConfirmModal").classList.remove("open");
                loadUsers();
                loadStats();
            } else {
                alert("Error updating status: " + data.message);
            }
        })
        .catch(err => console.error("Status update error:", err));
    });
}

function openSuspendModal(user) {
    const isSuspended = user.status === 'suspended';
    const newStatus   = isSuspended ? 'active' : 'suspended';

    document.getElementById("suspendModalTitle").textContent =
        isSuspended ? "Restore User" : "Suspend User";
    document.getElementById("suspendActionLabel").textContent =  
        isSuspended ? "You are about to restore access for:" : "You are about to suspend:";
    document.getElementById("suspendUserName").textContent =
        `${user.firstname} ${user.lastname}`;
    document.getElementById("suspendUserCode").textContent =
        `${user.user_code}`;

    const confirmBtn = document.getElementById("confirmSuspendBtn");
    confirmBtn.dataset.userId = user.user_id;
    confirmBtn.dataset.status = newStatus;

    if (isSuspended) {
        confirmBtn.textContent = "Yes, restore user";
        confirmBtn.style.cssText =
            "background:var(--success-bg);color:var(--success);border:1px solid var(--success);";
        document.querySelector("#suspendConfirmModal .modal-body p:last-child").textContent =
            "This user will regain full access to the system.";
        document.querySelector("#suspendConfirmModal .modal-body p:last-child").style.cssText =
            "font-size:13px;color:var(--success);background:var(--success-bg);padding:10px 14px;border-radius:8px;";
    } else {
        confirmBtn.textContent = "Yes, suspend user";
        confirmBtn.style.cssText =
            "background:var(--warning-bg);color:var(--warning);border:1px solid var(--warning);";
        document.querySelector("#suspendConfirmModal .modal-body p:last-child").textContent =
            "This user will lose access to the system until their account is restored.";
        document.querySelector("#suspendConfirmModal .modal-body p:last-child").style.cssText =
            "font-size:13px;color:var(--warning);background:var(--warning-bg);padding:10px 14px;border-radius:8px;";
    }

    document.getElementById("suspendConfirmModal").classList.add("open");
}

function loadSessionUser() {
    fetch("/Login/backend/auth/session_user.php")
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                window.location.href = "http://localhost:8000/Login/login.html";
                return;
            }
            const nameEl   = document.querySelector(".topbar-user-name");
            const roleEl   = document.querySelector(".topbar-user-role");
            const avatarEl = document.querySelector(".topbar-avatar");

            if (nameEl)   nameEl.textContent = data.firstname + " " + data.lastname;
            if (roleEl)   roleEl.textContent = data.role.charAt(0).toUpperCase() + data.role.slice(1);
            if (avatarEl) avatarEl.src = `../assets/avatars/avatar-${data.role}.svg`;
        })
        .catch(() => {
            window.location.href = "http://localhost:8000/Login/login.html";
        });
}

function initLogout() {
    const logoutBtn = document.getElementById("logoutBtn");
    if (!logoutBtn) return;

    logoutBtn.addEventListener("click", () => {
        fetch("/Login/backend/auth/logout.php")
            .then(() => {
                window.location.href = "http://localhost:8000/Login/login.html";
            })
            .catch(() => {
                window.location.href = "http://localhost:8000/Login/login.html";
            });
    });
}

function loadVehicles() {
    const tbody = document.querySelector("#vehicleTable tbody");
    if (!tbody) return;

    fetch("backend/vehicles/get_vehicles.php")
        .then(res => res.json())
        .then(vehicles => {
            tbody.innerHTML = "";

            if (vehicles.error) {
                console.error("Error loading vehicles:", vehicles.error);
                return;
            }

            vehicles.forEach((v, index) => {
                const fullName   = `${v.firstname} ${v.lastname}`;
                const typeLabel  = v.vehicle_type.charAt(0).toUpperCase() + v.vehicle_type.slice(1);
                const typeBadge  = v.vehicle_type === 'car' ? 'badge-info' : 'badge-gold';
                const isInside   = v.parking_status === 'inside';
                const statusBadge = isInside
                    ? `<span class="badge badge-success"><span class="badge-dot"></span>Inside</span>`
                    : `<span class="badge badge-muted"><span class="badge-dot"></span>Outside</span>`;
                const lastSeen = v.last_seen
                    ? new Date(v.last_seen).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
                    : '—';

                const row = document.createElement("tr");
                row.innerHTML = `
                    <td style="color:var(--text-muted);font-size:12px;">${String(index + 1).padStart(3, '0')}</td>
                    <td><span class="td-mono">${v.plate_number}</span></td>
                    <td>
                        <div class="user-card">
                            <img src="../assets/avatars/avatar-${v.role}.svg"
                                 style="width:32px;height:32px;border-radius:6px;object-fit:cover;"
                                 onerror="this.src='../assets/avatars/avatar-guest.svg'">
                            <div>
                                <div class="user-info-name">${fullName}</div>
                                <div class="user-info-sub">${v.role.charAt(0).toUpperCase() + v.role.slice(1)}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge ${typeBadge}">${typeLabel}</span></td>
                    <td style="font-size:13px;">—</td>
                    <td>${statusBadge}</td>
                    <td style="font-size:12px;color:var(--text-muted);">${lastSeen}</td>
                    <td><div class="qr-placeholder" style="width:36px;height:36px;font-size:0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                        </svg>
                    </div></td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <button class="btn btn-outline btn-icon btn-sm"
                                onclick='openViewVehicleModal(${JSON.stringify(v)})'>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="btn btn-outline btn-icon btn-sm"
                                onclick='openEditVehicleModal(${JSON.stringify(v)})'>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button class="btn btn-outline btn-icon btn-sm" style="color:var(--danger);"
                                onclick='openDeleteVehicleModal(${JSON.stringify(v)})'>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(err => console.error("Fetch error:", err));
}

function loadVehicleStats() {
    if (!document.getElementById('vehicleTable')) return;

    fetch("backend/vehicles/get_vehicle_stats.php")
        .then(res => res.json())
        .then(stats => {
            if (stats.error) return;

            const statCards = document.querySelectorAll('.stat-card .stat-value');
            if (statCards.length >= 4) {
                statCards[0].setAttribute('data-target', stats.total);
                statCards[1].setAttribute('data-target', stats.inside);
                statCards[2].setAttribute('data-target', stats.cars);
                statCards[3].setAttribute('data-target', stats.motorcycles);
                animateStats();
            }
        })
        .catch(err => console.error("Stats error:", err));
}

function initCreateVehicle() {
    const form = document.getElementById("createVehicleForm");
    if (!form) return;

    form.addEventListener("submit", function(e) {
        e.preventDefault();

        const userId = document.getElementById('resolvedUserId').value;
        if (!userId) {
            alert('Please look up a valid user code first.');
            return;
        }

        const formData = new FormData(form);

        fetch("backend/vehicles/create_vehicle.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                form.reset();
                document.getElementById('ownerLookupResult').style.display = 'none';
                document.getElementById('resolvedUserId').value = '';
                document.getElementById("addVehicleModal").classList.remove("open");
                loadVehicles();
                loadVehicleStats();
                loadUsers(); 
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => console.error("Error:", err));
    });
}

function openEditVehicleModal(v) {
    document.getElementById("editVehicleId").value    = v.vehicle_id;
    document.getElementById("editPlateNumber").value  = v.plate_number;
    document.getElementById("editVehicleType").value  = v.vehicle_type;
    document.getElementById("editVehicleUserId").value = v.user_id;
    document.getElementById("editVehicleModal").classList.add("open");
}

function initEditVehicle() {
    const form = document.getElementById("editVehicleForm");
    if (!form) return;

    form.addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(form);

        fetch("backend/vehicles/update_vehicle.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById("editVehicleModal").classList.remove("open");
                loadVehicles();
                loadVehicleStats();
                loadUsers(); 
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => console.error("Error:", err));
    });
}

function openDeleteVehicleModal(v) {
    document.getElementById("deleteVehiclePlate").textContent = v.plate_number;
    document.getElementById("deleteVehicleOwner").textContent = v.firstname + " " + v.lastname;
    document.getElementById("confirmDeleteVehicleBtn").dataset.vehicleId = v.vehicle_id;
    document.getElementById("deleteVehicleModal").classList.add("open");
}

function initDeleteVehicle() {
    const btn = document.getElementById("confirmDeleteVehicleBtn");
    if (!btn) return;

    btn.addEventListener("click", function() {
        const vehicleId = this.dataset.vehicleId;
        const formData = new FormData();
        formData.append("vehicle_id", vehicleId);

        fetch("backend/vehicles/delete_vehicle.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById("deleteVehicleModal").classList.remove("open");
                loadVehicles();
                loadVehicleStats();
                 loadUsers(); 
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => console.error("Error:", err));
    });
}

function openViewVehicleModal(v) {
    document.getElementById('viewVehiclePlate').textContent = v.plate_number;

    const typeLabel = v.vehicle_type.charAt(0).toUpperCase() + v.vehicle_type.slice(1);
    document.getElementById('viewVehicleType').textContent = typeLabel;

    // Parking status badge
    const isInside = v.parking_status === 'inside';
    const statusBadge = isInside
        ? `<span class="badge badge-success"><span class="badge-dot"></span>Inside</span>`
        : `<span class="badge badge-muted"><span class="badge-dot"></span>Outside</span>`;
    document.getElementById('viewVehicleStatusBadge').innerHTML = statusBadge;

    // Owner info
    const fullName = `${v.firstname} ${v.lastname}`;
    document.getElementById('viewVehicleOwnerAvatar').src = `../assets/avatars/avatar-${v.role}.svg`;
    document.getElementById('viewVehicleOwnerName').textContent = fullName;
    document.getElementById('viewVehicleOwnerCode').textContent = v.user_code ?? '—';
    document.getElementById('viewVehicleOwnerRole').textContent =
        v.role.charAt(0).toUpperCase() + v.role.slice(1);

    // Parking status text
    document.getElementById('viewVehicleParkingStatus').textContent =
        isInside ? 'Currently inside the parking lot' : 'Not currently parked';

    // Last seen
    document.getElementById('viewVehicleLastSeen').textContent = v.last_seen
        ? new Date(v.last_seen).toLocaleString('en-PH', {
            month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
          })
        : '—';

    // Current slot
    document.getElementById('viewVehicleSlot').textContent =
        isInside && v.slot_number ? v.slot_number : '—';

    document.getElementById('viewVehicleModal').classList.add('open');
}

function initUserCodeLookup() {
    const lookupBtn = document.getElementById('lookupUserBtn');
    if (!lookupBtn) return;

    lookupBtn.addEventListener('click', () => {
        const code    = document.getElementById('ownerCodeInput').value.trim();
        const result  = document.getElementById('ownerLookupResult');
        const hidden  = document.getElementById('resolvedUserId');

        if (!code) {
            result.style.display = 'block';
            result.style.cssText = 'margin-top:8px;display:block;padding:10px 14px;border-radius:8px;background:var(--warning-bg);border:1px solid var(--warning);font-size:13px;color:var(--warning);';
            result.textContent = 'Please enter a user code.';
            return;
        }

        lookupBtn.textContent = 'Looking up...';
        lookupBtn.disabled = true;

        fetch(`backend/vehicles/lookup_user.php?user_code=${encodeURIComponent(code)}`)
            .then(res => res.json())
            .then(data => {
                result.style.display = 'block';
                if (data.error) {
                    result.style.cssText = 'margin-top:8px;display:block;padding:10px 14px;border-radius:8px;background:var(--danger-bg);border:1px solid var(--danger);font-size:13px;color:var(--danger);';
                    result.textContent = '✗ ' + data.error;
                    hidden.value = '';
                } else {
                    result.style.cssText = 'margin-top:8px;display:block;padding:10px 14px;border-radius:8px;background:var(--success-bg);border:1px solid var(--success);font-size:13px;color:var(--success);';
                    result.innerHTML = `✓ <strong>${data.fullname}</strong> — ${data.role.charAt(0).toUpperCase() + data.role.slice(1)}`;
                    hidden.value = data.user_id;
                }
            })
            .catch(() => {
                result.style.display = 'block';
                result.style.cssText = 'margin-top:8px;display:block;padding:10px 14px;border-radius:8px;background:var(--danger-bg);border:1px solid var(--danger);font-size:13px;color:var(--danger);';
                result.textContent = '✗ Server error. Try again.';
            })
            .finally(() => {
                lookupBtn.textContent = 'Look Up';
                lookupBtn.disabled = false;
            });
    });

    // Also allow pressing Enter in the input to trigger lookup
    document.getElementById('ownerCodeInput').addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            lookupBtn.click();
        }
    });
}

function loadSlots() {
    if (!document.getElementById('slotGrid')) return;

    fetch("backend/slots/get_slots.php")
        .then(res => res.json())
        .then(slots => {
            if (slots.error) {
                console.error("Error loading slots:", slots.error);
                return;
            }

            // Group by full location_area
            const sections = {};
            slots.forEach(slot => {
                const area = slot.location_area;
                if (!sections[area]) sections[area] = [];
                sections[area].push(slot);
            });

            // Sort slots within each section numerically
            Object.keys(sections).forEach(area => {
                sections[area].sort((a, b) => {
                    const numA = parseInt(a.slot_number.split('-')[1]);
                    const numB = parseInt(b.slot_number.split('-')[1]);
                    return numA - numB;
                });
            });

            const container = document.getElementById('slotGrid');
            container.innerHTML = '';
            const sectionFilter = document.getElementById('sectionFilter');
            if (sectionFilter) {
                sectionFilter.addEventListener('change', () => {
                    const val = sectionFilter.value.toUpperCase();
                    document.querySelectorAll('#slotGrid > div').forEach(section => {
                        const sectionKey = section.querySelector('.slot-grid')?.dataset.section || '';
                        section.style.display = (!val || sectionKey === val) ? '' : 'none';
                    });
                });
            }

            // Sort sections alphabetically
            Object.keys(sections).sort().forEach(area => {
                const div = document.createElement('div');
                div.style.marginBottom = '20px';
                div.innerHTML = `
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid var(--border);">
                        Section ${area}
                    </div>
                    <div class="slot-grid" data-section="${area}"></div>
                `;

                const grid = div.querySelector('.slot-grid');
                sections[area].forEach(slot => {
                    const statusClass = slot.status === 'available'  ? 'available'
                        : slot.status === 'occupied'   ? 'occupied'
                        : slot.status === 'reserved'   ? 'reserved'
                        : 'maintenance';

                    const statusLabel = slot.status === 'available'  ? 'Open'
                        : slot.status === 'occupied'   ? 'Occupied'
                        : slot.status === 'reserved'   ? 'Reserved'
                        : 'Maint.';

                    const item = document.createElement('div');
                    item.className = `slot-item ${statusClass}`;
                    item.dataset.slotId = slot.slot_id;
                    item.innerHTML = `
                        <div class="slot-number">${slot.slot_number}</div>
                        <div class="slot-status-label">${statusLabel}</div>
                    `;
                    item.addEventListener('click', () => openSlotModal(slot));
                    grid.appendChild(item);
                });

                container.appendChild(div);
            });
        })
        .catch(err => console.error("Slots fetch error:", err));
}

function loadSlotStats() {
    if (!document.getElementById('slotGrid')) return;

    fetch("backend/slots/get_slot_stats.php")
        .then(res => res.json())
        .then(stats => {
            if (stats.error) return;

            const total = parseInt(stats.available)    + parseInt(stats.occupied)
                        + parseInt(stats.reserved) + parseInt(stats.maintenance);

            const statCards = document.querySelectorAll('.stat-card');
            if (statCards.length < 4) return;

            const values = [
                stats.available,
                stats.occupied,
                stats.reserved,
                stats.maintenance
            ];

            statCards.forEach((card, i) => {
                const valEl  = card.querySelector('.stat-value');
                const fillEl = card.querySelector('.progress-fill');

                if (valEl) {
                    valEl.textContent = '0';               // reset to 0 first
                    valEl.setAttribute('data-target', values[i]); // set target
                }

                if (fillEl && total > 0) {
                    const pct = Math.round((parseInt(values[i]) / total) * 100);
                    fillEl.style.width = pct + '%';
                }
            });

            animateStats(); // now data-target exists so this will work
        })
        .catch(err => console.error("Slot stats error:", err));
}

function openSlotModal(slot) {
    const modal = document.getElementById('slotModal');
    if (!modal) return;

    document.getElementById('modalSlotNum').textContent  = slot.slot_number;
    document.getElementById('editSlotId').value          = slot.slot_id;
    document.getElementById('editSlotNumber').value      = slot.slot_number;
    document.getElementById('editLocationArea').value    = slot.location_area;
    document.getElementById('editSlotStatus').value      = slot.status;

    const statusEl = document.getElementById('modalSlotStatus');
    statusEl.textContent = slot.status.charAt(0).toUpperCase() + slot.status.slice(1);
    statusEl.className = 'badge ' + (
        slot.status === 'available'   ? 'badge-success' :
        slot.status === 'occupied'    ? 'badge-danger'  :
        slot.status === 'reserved'    ? 'badge-gold'    : 'badge-muted'
    );

    // Show occupant info if occupied
    const occupantDiv = document.getElementById('slotOccupantInfo');
    if (slot.plate_number && occupantDiv) {
        occupantDiv.style.display = 'block';
        document.getElementById('slotOccupantPlate').textContent = slot.plate_number;
        document.getElementById('slotOccupantName').textContent  = slot.occupant_name;
        document.getElementById('slotTimeIn').textContent = slot.time_in
            ? new Date(slot.time_in).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' })
            : '—';
    } else if (occupantDiv) {
        occupantDiv.style.display = 'none';
    }

    modal.classList.add('open');
}

function initUpdateSlot() {
    const form = document.getElementById('updateSlotForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);

        fetch("backend/slots/update_slot.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('slotModal').classList.remove('open');
                loadSlots();
                loadSlotStats();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => console.error("Update slot error:", err));
    });
}

function initCreateSlot() {
    const form = document.getElementById('createSlotForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);

        fetch("backend/slots/create_slot.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                form.reset();
                document.getElementById('addSlotModal').classList.remove('open');
                loadSlots();
                loadSlotStats();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => console.error("Create slot error:", err));
    });
}

function initDeleteSlot() {
    const btn = document.getElementById('deleteSlotBtn');
    if (!btn) return;

    btn.addEventListener('click', function() {
        const slotId     = document.getElementById('editSlotId').value;
        const slotNumber = document.getElementById('modalSlotNum').textContent;

        if (!confirm(`Are you sure you want to delete slot ${slotNumber}? This cannot be undone.`)) return;

        const formData = new FormData();
        formData.append('slot_id', slotId);

        fetch("backend/slots/delete_slot.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('slotModal').classList.remove('open');
                loadSlots();
                loadSlotStats();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => console.error("Delete slot error:", err));
    });
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
  initCreateUser();
  initEditUser();
  initSuspendUser();
  loadUsers();
  loadStats();
  loadSessionUser();
  initLogout();

  loadVehicles();
  loadVehicleStats();
  initCreateVehicle();
  initEditVehicle();
  initDeleteVehicle();
  initUserCodeLookup();

  loadSlots();
  loadSlotStats();
  initUpdateSlot();
  initCreateSlot();
  initDeleteSlot();
});
