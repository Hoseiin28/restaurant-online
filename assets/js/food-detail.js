
    var selectedRating = 0;

    function setRating(rating) {
        selectedRating = rating;
        document.getElementById('ratingValue').value = rating;

        var buttons = document.querySelectorAll('#starInput .star-btn');
        buttons.forEach(function(btn) {
            var val = parseInt(btn.getAttribute('data-value'));
            if (val <= rating) {
                btn.classList.add('star-btn--active');
            } else {
                btn.classList.remove('star-btn--active');
            }
        });
    }

    function changeQty(delta) {
        var input = document.getElementById('quantity');
        var value = parseInt(input.value) + delta;
        if (value < 1) value = 1;
        if (value > 99) value = 99;
        input.value = value;
    }

    function handleAddToCartDetail(btn, foodId, foodName, foodPrice) {
        var qty = parseInt(document.getElementById('quantity').value);

        btn.classList.add('btn-add-cart--added');

        fetch('../main/add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'food_id=' + foodId + '&quantity=' + qty
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && data.cart_count !== undefined) {
                updateCartBadges(data.cart_count);
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
        })
        .finally(function() {
            setTimeout(function() {
                btn.classList.remove('btn-add-cart--added');
            }, 1500);
        });
    }

    function updateCartBadges(count) {
        var badges = document.querySelectorAll('.cart-badge');
        for (var i = 0; i < badges.length; i++) {
            badges[i].textContent = count;
            badges[i].style.display = count > 0 ? 'flex' : 'none';
        }
    }