const reservationTabs = document.querySelectorAll('.reservation-tab');

const detailSlot = document.getElementById('detailSlot');
const detailStatus = document.getElementById('detailStatus');
const detailReserved = document.getElementById('detailReserved');
const detailTransaction = document.getElementById('detailTransaction');
const detailTimeIn = document.getElementById('detailTimeIn');
const detailTimeOut = document.getElementById('detailTimeOut');
const detailDuration = document.getElementById('detailDuration');
const detailFee = document.getElementById('detailFee');

const transactionCard = document.querySelector('.transaction-link');

let currentTransactionId = null;


reservationTabs.forEach(tab => {
    tab.addEventListener('click', () => {

        reservationTabs.forEach(item => item.classList.remove('active'));
        tab.classList.add('active');

        detailSlot.textContent = tab.dataset.slot;
        detailStatus.textContent = tab.dataset.status;
        detailReserved.textContent = tab.dataset.reserved;
        detailTimeIn.textContent = tab.dataset.timein;
        detailTimeOut.textContent = tab.dataset.timeout;
        detailDuration.textContent = tab.dataset.duration;
        detailFee.textContent = tab.dataset.fee;

        currentTransactionId = tab.dataset.transactionId || '';

        if (currentTransactionId) {
            detailTransaction.textContent = `Receipt #${currentTransactionId}`;
            detailTransaction.classList.remove('disabled');
        } else {
            detailTransaction.textContent = 'No receipt available';
            detailTransaction.classList.add('disabled');
        }

        console.log("Selected transaction:", currentTransactionId);
    });
});


transactionCard.addEventListener('click', () => {

    if (!currentTransactionId) {
        console.error("No transaction selected");
        return;
    }

    window.location.href =
        `transactions.php?transaction=${encodeURIComponent(currentTransactionId)}`;
});


if (reservationTabs.length > 0) {
    reservationTabs[0].click();
}