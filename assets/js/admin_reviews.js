function deleteReview(id, userName) {
    document.getElementById('deleteReviewUser').textContent = userName;
    
    const currentUrl = new URL(window.location.href);
    const params = new URLSearchParams(currentUrl.search);
    
    params.delete('delete');
    params.delete('token');
    
    params.set('delete', id);
    params.set('token', CSRF_TOKEN);
    
    document.getElementById('deleteConfirmBtn').href = 'reviews.php?' + params.toString();
    
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
}

function openReplyModal(reviewId, userName) {
    document.getElementById('replyUserName').textContent = userName;
    document.getElementById('replyReviewId').value = reviewId;
    document.getElementById('replyModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        document.getElementById('replyText').focus();
    }, 100);
}

function closeReplyModal() {
    document.getElementById('replyModal').classList.remove('active');
    document.body.style.overflow = '';
}

function editReply(reviewId, currentReply) {
    openReplyModal(reviewId, '');
    document.getElementById('replyText').value = currentReply;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
        closeReplyModal();
    }
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        closeDeleteModal();
        closeReplyModal();
    }
});