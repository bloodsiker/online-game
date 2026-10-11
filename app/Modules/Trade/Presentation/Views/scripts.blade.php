<script>
    function updateTradeCounter(input) {
        const max = Number(input.dataset.max) || 1;
        const quantity = Math.min(max, Math.max(1, parseInt(input.value, 10) || 1));
        input.value = quantity;
        const controls = input.closest('.b-input__inner');
        controls.querySelector('.left').classList.toggle('left-disabled', quantity <= 1);
        controls.querySelector('.right').classList.toggle('right-disabled', quantity >= max);
        input.closest('.trade-row').querySelector('.trade-total').textContent = (quantity * Number(input.dataset.price)).toLocaleString('ru-RU');
    }
    document.querySelectorAll('.trade-count').forEach(input => {
        updateTradeCounter(input);
        input.addEventListener('input', () => updateTradeCounter(input));
        input.addEventListener('blur', () => updateTradeCounter(input));
    });
    document.querySelectorAll('[data-trade-step]').forEach(arrow => arrow.addEventListener('click', () => {
        const input = arrow.closest('.b-input__inner').querySelector('input');
        if (input.disabled) return;
        input.value = (parseInt(input.value, 10) || 1) + Number(arrow.dataset.tradeStep);
        updateTradeCounter(input);
    }));
    document.querySelectorAll('form').forEach(form => form.addEventListener('submit', () => {
        form.querySelector('input[type="submit"]').disabled = true;
    }));
    @if(session('trade_result'))
    try { window.top.sendToFrame?.('character-frame', {money: @json(session('trade_result.money'))}, window.location.origin); } catch (error) {}
    @endif
</script>
