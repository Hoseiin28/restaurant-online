<?php
$pageTitle = 'جزئیات سفارش';
require_once '../includes/header_admin.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    setErrorMessage('سفارش یافت نشد.');
    redirect(BASE_URL . 'admin/orders.php');
}

$order = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT o.*, u.full_name, u.phone, u.email 
     FROM orders o 
     JOIN users u ON o.user_id = u.id 
     WHERE o.id = $order_id"
));

if (!$order) {
    setErrorMessage('سفارش یافت نشد.');
    redirect(BASE_URL . 'admin/orders.php');
}

$items = mysqli_query(
    $conn,
    "SELECT oi.*, f.name as food_name, f.image as food_image 
     FROM order_items oi 
     JOIN foods f ON oi.food_id = f.id 
     WHERE oi.order_id = $order_id"
);

$status_config = [
    'pending' => ['text' => 'در انتظار', 'color' => '#f59e0b', 'bg' => '#fef3c7'],
    'confirmed' => ['text' => 'تأیید شده', 'color' => '#3b82f6', 'bg' => '#dbeafe'],
    'preparing' => ['text' => 'در حال آماده‌سازی', 'color' => '#8b5cf6', 'bg' => '#ede9fe'],
    'delivered' => ['text' => 'تحویل شده', 'color' => '#10b981', 'bg' => '#d1fae5'],
    'cancelled' => ['text' => 'لغو شده', 'color' => '#ef4444', 'bg' => '#fee2e2'],
];
$status = $status_config[$order['status']] ?? $status_config['pending'];
?>

<link rel="stylesheet" href="../assets/css/admin_order-detail.css">

<a href="orders.php" class="back-link">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
        <polyline points="15 18 9 12 15 6" />
    </svg>
    بازگشت به لیست سفارش‌ها
</a>

<div class="detail-card">
    <div class="detail-header">
        <h3>سفارش #<?php echo $order['id']; ?></h3>
        <span style="padding:6px 14px;border-radius:100px;font-size:12px;font-weight:600;
            background:<?php echo $status['bg']; ?>;color:<?php echo $status['color']; ?>;">
            <?php echo $status['text']; ?>
        </span>
    </div>

    <div class="detail-body">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-icon" style="background:#eff6ff;">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#3b82f6;stroke-width:2;">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                    </svg>
                </div>
                <div class="info-text">
                    <span>نام کاربر</span>
                    <span><?php echo $order['full_name']; ?></span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background:#f0fdf4;">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#22c55e;stroke-width:2;">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                    </svg>
                </div>
                <div class="info-text">
                    <span>تاریخ ثبت</span>
                    <span><?php echo fullJalaliDate($order['order_date']); ?></span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background:#fef3c7;">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#f59e0b;stroke-width:2;">
                        <line x1="12" y1="1" x2="12" y2="23" />
                        <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                    </svg>
                </div>
                <div class="info-text">
                    <span>مبلغ کل</span>
                    <span><?php echo number_format($order['total_price']); ?> تومان</span>
                </div>
            </div>
            <?php if ($order['phone']): ?>
                <div class="info-item">
                    <div class="info-icon" style="background:#fce7f3;">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#ec4899;stroke-width:2;">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" />
                        </svg>
                    </div>
                    <div class="info-text">
                        <span>تلفن</span>
                        <span dir="ltr"><?php echo $order['phone']; ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div style="overflow-x:auto;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="50">تصویر</th>
                        <th>نام غذا</th>
                        <th>قیمت واحد</th>
                        <th>تعداد</th>
                        <th>جمع</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($items)): ?>
                        <tr>
                            <td>
                                <div class="food-thumb">
                                    <?php
                                    $food_image_path = UPLOAD_URL . $item['food_image'];
                                    $default_food_image = BASE_URL . 'assets/images/foods/default-food.jpg';

                                    if (!empty($item['food_image']) && file_exists(UPLOAD_DIR . $item['food_image'])):
                                    ?>
                                        <img src="<?php echo $food_image_path; ?>"
                                            alt="<?php echo htmlspecialchars($item['food_name']); ?>"
                                            loading="lazy">
                                    <?php else: ?>
                                        <img src="<?php echo $default_food_image; ?>"
                                            alt="<?php echo htmlspecialchars($item['food_name']); ?>"
                                            loading="lazy">
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo $item['food_name']; ?></td>
                            <td><?php echo number_format($item['price_per_unit']); ?> ت</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['quantity'] * $item['price_per_unit']); ?> ت</td>
                        </tr>
                    <?php endwhile; ?>
                    <tr class="total-row">
                        <td colspan="4">جمع کل</td>
                        <td><?php echo number_format($order['total_price']); ?> تومان</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer_admin.php'; ?>