
    function changeSort(value) {
        var url = new URL(window.location.href);
        url.searchParams.set('sort', value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    function handleAddToCart(btn, foodId, foodName, foodPrice) {
        btn.classList.add('btn-add--added');

        fetch('../main/add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'food_id=' + foodId + '&quantity=1'
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
                btn.classList.remove('btn-add--added');
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
