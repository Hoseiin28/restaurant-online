
    document.addEventListener('DOMContentLoaded', function() {
        var toast = document.getElementById('cartToast');
        if (toast) {
            setTimeout(function() {
                toast.classList.remove('cart-toast--visible');
                setTimeout(function() {
                    if (toast.parentNode) toast.parentNode.removeChild(toast);
                }, 300);
            }, 2500);
        }
    });

    function changeQty(btn, delta) {
        var form = btn.closest('.qty-form');
        var input = form.querySelector('.qty-control__input');
        var value = parseInt(input.value) + delta;
        if (value < 1) value = 1;
        if (value > 99) value = 99;
        input.value = value;
        form.submit();
    }
