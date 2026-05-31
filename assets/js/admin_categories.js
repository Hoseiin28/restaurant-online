function deleteCategory(id, name, foodCount) {
    document.getElementById('deleteCatName').textContent = name;
    const warning = document.getElementById('deleteWarning');
    const confirmBtn = document.getElementById('deleteConfirmBtn');
    
    if (foodCount > 0) {
        warning.textContent = 'این دسته‌بندی ' + foodCount + ' غذا دارد و قابل حذف نیست. ابتدا غذاها را منتقل کنید.';
        warning.style.display = 'block';
        confirmBtn.style.display = 'none';
    } else {
        warning.style.display = 'none';
        confirmBtn.style.display = 'inline-flex';
        confirmBtn.href = 'categories.php?delete=' + id + '&token=' + CSRF_TOKEN;
    }
    
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
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