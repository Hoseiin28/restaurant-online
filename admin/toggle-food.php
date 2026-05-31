<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'توکن امنیتی نامعتبر است']);
    exit;
}

if (!isset($_POST['id']) || !isset($_POST['available'])) {
    echo json_encode(['success' => false, 'message' => 'پارامترهای ناقص']);
    exit;
}

$food_id = (int)$_POST['id'];
$is_available = (int)$_POST['available'] ? 1 : 0;

$stmt = mysqli_prepare($conn, "UPDATE foods SET is_available = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "ii", $is_available, $food_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_affected_rows($conn) > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'وضعیت غذا با موفقیت تغییر کرد',
            'is_available' => $is_available
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'غذای مورد نظر یافت نشد'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'خطا در به‌روزرسانی: ' . mysqli_error($conn)
    ]);
}
?>