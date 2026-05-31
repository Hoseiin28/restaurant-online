<?php
$pageTitle = 'داشبورد';
require_once '../includes/header_admin.php';
require_once '../includes/functions.php';

if (isset($_POST['confirm_order_id']) && is_numeric($_POST['confirm_order_id'])) {
    $order_id_to_confirm = (int)$_POST['confirm_order_id'];

    $check_sql = "SELECT id FROM orders WHERE id = $order_id_to_confirm AND status = 'pending'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $update_sql = "UPDATE orders SET status = 'confirmed' WHERE id = $order_id_to_confirm";
        if (mysqli_query($conn, $update_sql)) {
            setSuccessMessage("سفارش #$order_id_to_confirm با موفقیت تایید شد.");
        } else {
            setErrorMessage("خطا در تایید سفارش. لطفا دوباره تلاش کنید.");
        }
    }
    redirect(BASE_URL . 'admin/dashboard.php');
}

$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status IN ('confirmed','preparing') THEN 1 ELSE 0 END) as active_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
    SUM(CASE WHEN status != 'cancelled' THEN total_price ELSE 0 END) as total_revenue,
    COALESCE(SUM(CASE WHEN DATE(order_date) = CURDATE() AND status != 'cancelled' THEN total_price ELSE 0 END), 0) as today_revenue
FROM orders";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

$users_count_query = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
$users_result = mysqli_query($conn, $users_count_query);
$total_users = mysqli_fetch_assoc($users_result)['total_users'];

$foods_count_query = "SELECT COUNT(*) as total_foods FROM foods WHERE is_available = 1";
$foods_result = mysqli_query($conn, $foods_count_query);
$total_foods = mysqli_fetch_assoc($foods_result)['total_foods'];

$reviews_count_query = "SELECT COUNT(*) as total_reviews FROM reviews";
$reviews_result = mysqli_query($conn, $reviews_count_query);
$total_reviews = mysqli_fetch_assoc($reviews_result)['total_reviews'];

$pending_orders_query = "SELECT o.id, o.total_price, o.order_date, u.full_name, u.phone
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        WHERE o.status = 'pending' 
                        ORDER BY o.order_date ASC LIMIT 5";
$pending_orders_result = mysqli_query($conn, $pending_orders_query);

$recent_orders_query = "SELECT o.id, o.total_price, o.status, o.order_date, u.full_name
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.order_date DESC LIMIT 5";
$recent_orders_result = mysqli_query($conn, $recent_orders_query);

$top_rated_foods_query = "SELECT f.name, 
                                 COUNT(r.id) as review_count, 
                                 ROUND(AVG(r.rating), 1) as avg_rating
                          FROM foods f
                          LEFT JOIN reviews r ON f.id = r.food_id
                          WHERE f.is_available = 1
                          GROUP BY f.id
                          HAVING review_count > 0
                          ORDER BY avg_rating DESC, review_count DESC
                          LIMIT 5";
$top_rated_foods_result = mysqli_query($conn, $top_rated_foods_query);

$latest_reviews_query = "SELECT r.id, r.rating, r.comment, r.created_at, r.admin_reply, r.admin_reply_date, 
                        u.full_name, f.name as food_name
                        FROM reviews r
                        JOIN users u ON r.user_id = u.id
                        JOIN foods f ON r.food_id = f.id
                        ORDER BY r.created_at DESC LIMIT 3";
$latest_reviews_result = mysqli_query($conn, $latest_reviews_query);

$chart_labels = [];
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $jDate = toJalali($date, 'Y/m/d');
    $chart_labels[] = $jDate;

    $day_query = "SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = '$date' AND status != 'cancelled'";
    $day_result = mysqli_query($conn, $day_query);
    $chart_data[] = mysqli_fetch_assoc($day_result)['count'];
}
?>

<link rel="stylesheet" href="../assets/css/admin_dashboard.css">

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($stats['pending_orders']); ?></h3>
            <p>در انتظار تایید</p>
        </div>
        <div class="stat-icon" style="background: #fef3c7;"><svg viewBox="0 0 24 24" style="fill:none;stroke:#f59e0b;stroke-width:2;">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo formatPrice($stats['today_revenue']); ?></h3>
            <p>درآمد امروز</p>
            <span class="stat-detail">کل: <?php echo formatPrice($stats['total_revenue']); ?></span>
        </div>
        <div class="stat-icon" style="background: #d1fae5;"><svg viewBox="0 0 24 24" style="fill:none;stroke:#10b981;stroke-width:2;">
                <line x1="12" y1="1" x2="12" y2="23" />
                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
            </svg></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($total_users); ?></h3>
            <p>کاربران</p>
        </div>
        <div class="stat-icon" style="background: #dbeafe;"><svg viewBox="0 0 24 24" style="fill:none;stroke:#3b82f6;stroke-width:2;">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
            </svg></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo $total_foods; ?></h3>
            <p>غذای فعال</p>
            <span class="stat-detail"><?php echo $total_reviews; ?> نظر</span>
        </div>
        <div class="stat-icon" style="background: #fce7f3;"><svg viewBox="0 0 24 24" style="fill:none;stroke:#ec4899;stroke-width:2;">
                <path d="M18 8h1a4 4 0 010 8h-1" />
                <path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z" />
            </svg></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>سفارشات در انتظار تایید</h2>
        <?php if (mysqli_num_rows($pending_orders_result) > 0): ?>
            <span style="font-size: 12px; background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; white-space: nowrap;"><?php echo mysqli_num_rows($pending_orders_result); ?> مورد</span>
        <?php endif; ?>
    </div>
    <div class="card-body no-padding" style="overflow-x: auto;">
        <?php if (mysqli_num_rows($pending_orders_result) > 0): ?>
            <table class="mini-table">
                <thead>
                    <tr>
                        <th>مشتری</th>
                        <th>مبلغ</th>
                        <th>زمان ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($pending_orders_result)): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                                <div style="font-size: 11px; color: #64748b;"><?php echo htmlspecialchars($order['phone']); ?></div>
                            </td>
                            <td><?php echo formatPrice($order['total_price']); ?></td>
                            <td style="white-space: nowrap;"><?php echo toJalali($order['order_date'], 'H:i Y/m/d'); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="confirm_order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="btn-confirm">
                                        <svg viewBox="0 0 24 24" style="fill:none;stroke:currentColor;stroke-width:2;">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        تایید
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 40px; text-align: center; color: #64748b;">
                <svg viewBox="0 0 24 24" style="width: 40px; height: 40px; fill: none; stroke: #10b981; stroke-width: 1.5; margin-bottom: 10px;">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="9 12 11 14 15 10" />
                </svg>
                <p>هیچ سفارش در انتظاری وجود ندارد.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="double-col">
    <div class="card">
        <div class="card-header">
            <h2>آخرین سفارشات</h2>
            <a href="orders.php" style="font-size: 12px; color: #3b82f6; text-decoration: none; white-space: nowrap;">نمایش همه</a>
        </div>
        <div class="card-body no-padding" style="overflow-x: auto;">
            <table class="mini-table">
                <thead>
                    <tr>
                        <th>مشتری</th>
                        <th>مبلغ</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recent_orders_result) > 0): ?>
                        <?php while ($order = mysqli_fetch_assoc($recent_orders_result)):
                            switch ($order['status']) {
                                case 'pending':
                                    $status_badge = 'badge-pending';
                                    $status_text = 'در انتظار تایید';
                                    break;
                                case 'confirmed':
                                    $status_badge = 'badge-confirmed';
                                    $status_text = 'تایید شده';
                                    break;
                                case 'preparing':
                                    $status_badge = 'badge-preparing';
                                    $status_text = 'در حال آماده‌سازی';
                                    break;
                                case 'delivered':
                                    $status_badge = 'badge-delivered';
                                    $status_text = 'تحویل داده شده';
                                    break;
                                case 'cancelled':
                                    $status_badge = 'badge-cancelled';
                                    $status_text = 'لغو شده';
                                    break;
                                default:
                                    $status_badge = 'badge-unknown';
                                    $status_text = 'نامشخص';
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                <td><?php echo formatPrice($order['total_price']); ?></td>
                                <td><span class="badge <?php echo $status_badge; ?>"><?php echo $status_text; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 30px;">سفارشی یافت نشد.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>فروش ۷ روز گذشته</h2>
            <span style="font-size: 12px; color: #94a3b8;">تعداد سفارشات موفق</span>
        </div>
        <div class="card-body">
            <div class="chart-placeholder">
                <?php foreach ($chart_data as $index => $value):
                    $max = max($chart_data) ?: 1;
                    $height = ($value / $max) * 140;
                ?>
                    <div class="chart-bar" style="height: <?php echo max($height, 4); ?>px;">
                        <?php if ($value > 0): ?><span class="value"><?php echo $value; ?></span><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="chart-axis">
                <?php foreach ($chart_labels as $label):
                    $parts = explode('/', $label);
                    echo '<span>' . $parts[2] . ' ' . jMonthName((int)$parts[1]) . '</span>';
                endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="double-col">
    <div class="card">
        <div class="card-header">
            <h2>محبوب‌ترین غذاها</h2>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($top_rated_foods_result) > 0): ?>
                <?php $rank = 1;
                while ($food = mysqli_fetch_assoc($top_rated_foods_result)):
                    $rank_class = '';
                    if ($rank === 1) $rank_class = 'gold';
                    elseif ($rank === 2) $rank_class = 'silver';
                    elseif ($rank === 3) $rank_class = 'bronze';
                ?>
                    <div class="top-item">
                        <div style="display: flex; align-items: center; flex: 1; min-width: 0;">
                            <div class="top-rank <?php echo $rank_class; ?>"><?php echo $rank++; ?></div>
                            <div class="top-info">
                                <span class="top-name"><?php echo htmlspecialchars($food['name']); ?></span>
                                <span class="top-meta"><?php echo $food['review_count']; ?> نظر ثبت شده</span>
                            </div>
                        </div>
                        <div class="top-rating">
                            ★ <?php echo $food['avg_rating']; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #64748b; padding: 20px;">هنوز امتیازی ثبت نشده است.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>نظرات جدید کاربران</h2>
            <a href="reviews.php" style="font-size: 12px; color: #3b82f6; text-decoration: none;">همه نظرات</a>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($latest_reviews_result) > 0): ?>
                <?php while ($review = mysqli_fetch_assoc($latest_reviews_result)): ?>
                    <div class="review-item">
                        <div class="review-avatar"><?php echo mb_substr($review['full_name'], 0, 1, 'UTF-8'); ?></div>
                        <div class="review-content">
                            <div class="review-header">
                                <span class="review-author"><?php echo htmlspecialchars($review['full_name']); ?></span>
                                <span class="review-food"><?php echo htmlspecialchars($review['food_name']); ?></span>
                            </div>
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                                <?php endfor; ?>
                            </div>
                            <?php if (!empty($review['comment'])): ?>
                                <div class="review-text">
                                    <?php echo htmlspecialchars(mb_strlen($review['comment'], 'UTF-8') > 100 ? mb_substr($review['comment'], 0, 100, 'UTF-8') . '...' : $review['comment']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($review['admin_reply'])): ?>
                                <div class="admin-reply-mini">
                                    <div class="admin-reply-mini__header">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;width:12px;height:12px;">
                                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                                        </svg>
                                        پاسخ ادمین
                                        <span class="admin-reply-mini__date"><?php echo toJalali($review['admin_reply_date'], 'Y/m/d'); ?></span>
                                    </div>
                                    <p class="admin-reply-mini__text">
                                        <?php echo htmlspecialchars(mb_strlen($review['admin_reply'], 'UTF-8') > 80 ? mb_substr($review['admin_reply'], 0, 80, 'UTF-8') . '...' : $review['admin_reply']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="review-date"><?php echo toJalali($review['created_at'], 'Y/m/d H:i'); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #64748b; padding: 20px;">هنوز نظری ثبت نشده است.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer_admin.php'; ?>