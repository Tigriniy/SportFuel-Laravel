const delivery = document.querySelector('#delivery_method');
const fee = document.querySelector('#checkout-fee');
const total = document.querySelector('#checkout-total');
const rubles = new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' });

delivery.addEventListener('change', () => {
    const deliveryFee = Number(delivery.selectedOptions[0].dataset.fee);
    fee.textContent = rubles.format(deliveryFee / 100);
    total.textContent = rubles.format((Number(total.dataset.subtotal) + deliveryFee) / 100);
});
