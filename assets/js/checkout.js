document.addEventListener('DOMContentLoaded', function() {
    var toast = document.getElementById('checkoutToast');
    if (toast) {
        setTimeout(function() {
            toast.classList.remove('checkout-toast--visible');
            setTimeout(function() {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 300);
        }, 3000);
    }
});

function formatCardNumber(input) {
    var value = input.value.replace(/\D/g, '');
    value = value.substring(0, 16);
    var formatted = '';
    for (var i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) formatted += ' ';
        formatted += value[i];
    }
    input.value = formatted;
}