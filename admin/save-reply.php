<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die('دسترسی غیرمجاز');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('reviews.php');
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    setErrorMessage('توکن امنیتی نامعتبر است.');
    redirect('reviews.php');
}

$review_id = (int)$_POST['review_id'];
$admin_reply = sanitize($_POST['admin_reply']);
$admin_id = $_SESSION['user_id'];

if (empty(trim($admin_reply))) {
    setErrorMessage('متن پاسخ نمی‌تواند خالی باشد.');
    redirect('reviews.php');
}

$stmt = mysqli_prepare($conn, 
    "UPDATE reviews SET admin_reply = ?, admin_reply_date = NOW(), replied_by = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "sii", $admin_reply, $admin_id, $review_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_affected_rows($conn) > 0) {
        setSuccessMessage('پاسخ شما با موفقیت ثبت شد.');
    } else {
        setErrorMessage('نظر مورد نظر یافت نشد.');
    }
} else {
    setErrorMessage('خطا در ثبت پاسخ: ' . mysqli_error($conn));
}

mysqli_stmt_close($stmt);
redirect('reviews.php');