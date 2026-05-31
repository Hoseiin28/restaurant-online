function toggleRole(userId, currentRole, name) {
    const newRole = currentRole === 'admin' ? 'کاربر عادی' : 'مدیر';
    if (confirm('آیا نقش "' + name + '" به "' + newRole + '" تغییر کند؟')) {
        window.location.href = 'users.php?toggle_role=' + userId + '&token=' + CSRF_TOKEN;
    }
}

function deleteUser(userId, name, orderCount) {
    document.getElementById('deleteUserName').textContent = name;
    const warning = document.getElementById('deleteWarning');
    const confirmBtn = document.getElementById('deleteConfirmBtn');

    if (orderCount > 0) {
        warning.textContent = 'این کاربر ' + orderCount + ' سفارش دارد و قابل حذف نیست.';
        warning.style.display = 'block';
        confirmBtn.style.display = 'none';
    } else {
        warning.style.display = 'none';
        confirmBtn.style.display = 'inline-flex';
        confirmBtn.href = 'users.php?delete=' + userId + '&token=' + CSRF_TOKEN;
    }

    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});