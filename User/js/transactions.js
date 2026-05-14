const searchInput = document.getElementById('transactionSearch');
const statusFilter = document.getElementById('filterStatus');
const dateFrom = document.getElementById('filterDateFrom');
const dateTo = document.getElementById('filterDateTo');
const transactionList = document.getElementById('transactionList');
const transactionCount = document.getElementById('transactionCount');
const emptyState = document.getElementById('transactionEmpty');
const modalOverlay = document.getElementById('transactionModal');
const closeModalButton = document.getElementById('closeTransactionModal');
const closeModalFooter = document.getElementById('closeTransactionButton');
const printButton = document.getElementById('printReceiptButton');

const modalReceipt = document.getElementById('modalReceipt');
const modalStatus = document.getElementById('modalStatus');
const modalStatusText = document.getElementById('modalStatusText');
const modalDate = document.getElementById('modalDate');
const modalSlot = document.getElementById('modalSlot');
const modalTimeIn = document.getElementById('modalTimeIn');
const modalTimeOut = document.getElementById('modalTimeOut');
const modalDuration = document.getElementById('modalDuration');
const modalFee = document.getElementById('modalFee');

const transactionCards = Array.from(document.querySelectorAll('.transaction-card'));

function formatStatusLabel(status) {
  if (!status) return 'Unknown';
  if (status === 'out') return 'Completed';
  if (status === 'in') return 'Active';
  if (status === 'denied') return 'Denied';
  return status.charAt(0).toUpperCase() + status.slice(1);
}

function getStatusBadgeClass(status) {
  if (status === 'out') return 'badge-success';
  if (status === 'in') return 'badge-muted';
  if (status === 'denied') return 'badge-danger';
  return 'badge-muted';
}

function updateTransactionCount(count) {
  if (!transactionCount) return;
  const total = transactionCards.length;
  transactionCount.textContent = `${count} of ${total} records`;
}

function filterTransactions() {
  const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
  const status = statusFilter ? statusFilter.value : '';
  const fromDate = dateFrom && dateFrom.value ? dateFrom.value : '';
  const toDate = dateTo && dateTo.value ? dateTo.value : '';

  let visibleCount = 0;

  transactionCards.forEach(card => {
    const receipt = card.dataset.transactionId?.toLowerCase() || '';
    const slot = card.dataset.slot?.toLowerCase() || '';
    const statusValue = card.dataset.status?.toLowerCase() || '';
    const dateValue = card.dataset.date || '';
    const displayDate = card.dataset.createdDate?.toLowerCase() || '';
    const timeIn = card.dataset.timeIn?.toLowerCase() || '';
    const timeOut = card.dataset.timeOut?.toLowerCase() || '';
    const duration = card.dataset.duration?.toLowerCase() || '';

    const matchesQuery = query === '' || [receipt, slot, statusValue, timeIn, timeOut, duration, dateValue, displayDate].some(value => value.includes(query));
    const matchesStatus = status === '' || statusValue === status;
    const matchesFrom = !fromDate || (dateValue && dateValue >= fromDate);
    const matchesTo = !toDate || (dateValue && dateValue <= toDate);

    const visible = matchesQuery && matchesStatus && matchesFrom && matchesTo;
    card.style.display = visible ? '' : 'none';
    if (visible) visibleCount += 1;
  });

  if (emptyState) {
    emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
  }

  updateTransactionCount(visibleCount);
}

function populateModal(card) {
  if (!card) return;

  const statusValue = card.dataset.status || '';
  const statusLabel = formatStatusLabel(statusValue);

  modalReceipt.textContent = `#${card.dataset.transactionId || '—'}`;
  modalStatus.textContent = statusLabel;
  modalStatus.className = `badge ${getStatusBadgeClass(statusValue)}`;
  modalStatusText.textContent = statusLabel;
  modalDate.textContent = card.dataset.createdDate || '';
  modalSlot.textContent = card.dataset.slot || '—';
  modalTimeIn.textContent = card.dataset.timeIn || '—';
  modalTimeOut.textContent = card.dataset.timeOut || '—';
  modalDuration.textContent = `${card.dataset.duration || '—'} hour(s)`;
  modalFee.textContent = `₱${card.dataset.fee || '—'}`;
}

function openModal(card) {
  if (!card) return;
  populateModal(card);
  if (modalOverlay) {
    modalOverlay.classList.add('open');
    modalOverlay.setAttribute('aria-hidden', 'false');
  }
}

function closeModal() {
  if (modalOverlay) {
    modalOverlay.classList.remove('open');
    modalOverlay.setAttribute('aria-hidden', 'true');
  }
}

function openTransactionByQuery() {
  const params = new URLSearchParams(window.location.search);
  const transactionId = params.get('transaction');
  if (!transactionId) return;

  const targetCard = transactionCards.find(card => card.dataset.transactionId === transactionId);
  if (targetCard) {
    openModal(targetCard);
    targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

transactionCards.forEach(card => {
  card.addEventListener('click', (event) => {
    event.preventDefault();
    openModal(card);
  });
});

if (searchInput) searchInput.addEventListener('keydown', (event) => {
  if (event.key === 'Enter') {
    filterTransactions();
  }
});
if (statusFilter) statusFilter.addEventListener('change', filterTransactions);
if (dateFrom) dateFrom.addEventListener('change', filterTransactions);
if (dateTo) dateTo.addEventListener('change', filterTransactions);
if (closeModalButton) closeModalButton.addEventListener('click', closeModal);
if (closeModalFooter) closeModalFooter.addEventListener('click', closeModal);
if (modalOverlay) {
  modalOverlay.addEventListener('click', event => {
    if (event.target === modalOverlay) closeModal();
  });
}
if (printButton) {
  printButton.addEventListener('click', () => {
    window.print();
  });
}

document.addEventListener('keydown', event => {
  if (event.key === 'Escape') closeModal();
});

filterTransactions();
openTransactionByQuery();
