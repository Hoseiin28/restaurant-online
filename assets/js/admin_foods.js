function deleteFood(id, name) {
    document.getElementById('deleteFoodName').textContent = name;
    document.getElementById('deleteConfirmBtn').href = 'foods.php?delete=' + id + '&token=' + CSRF_TOKEN;
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
}

function toggleAvailability(id, checkbox) {
    console.log('Toggle called with ID:', id);
    console.log('Checkbox checked:', checkbox.checked);
    const isAvailable = checkbox.checked ? 1 : 0;
    const row = checkbox.closest('tr');
    const statusLabel = row.querySelector('.status-label');
    
    if (statusLabel) {
        statusLabel.textContent = isAvailable ? 'موجود' : 'ناموجود';
        statusLabel.className = 'status-label ' + (isAvailable ? 'available' : 'unavailable');
    }
    
    checkbox.disabled = true;
    
    fetch('toggle-food.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'id=' + id + '&available=' + isAvailable + '&csrf_token=' + CSRF_TOKEN
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            checkbox.checked = !isAvailable;
            if (statusLabel) {
                const prevAvailable = !isAvailable;
                statusLabel.textContent = prevAvailable ? 'موجود' : 'ناموجود';
                statusLabel.className = 'status-label ' + (prevAvailable ? 'available' : 'unavailable');
            }
            console.warn('خطا:', data.message);
        }
    })
    .catch(error => {
        checkbox.checked = !isAvailable;
        if (statusLabel) {
            const prevAvailable = !isAvailable;
            statusLabel.textContent = prevAvailable ? 'موجود' : 'ناموجود';
            statusLabel.className = 'status-label ' + (prevAvailable ? 'available' : 'unavailable');
        }
        console.error('خطای شبکه:', error);
        location.reload();
    })
    .finally(() => {
        checkbox.disabled = false;
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        closeDeleteModal();
    }
});