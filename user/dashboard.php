<?php

$page_title = 'داشبورد کاربری';
require_once '../includes/header.php';

checkAccess('user');

$user_id = $_SESSION['user_id'];

$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM orders WHERE user_id = $user_id) as total_orders,
    (SELECT COUNT(*) FROM orders WHERE user_id = $user_id AND status = 'pending') as pending_orders,
    (SELECT COUNT(*) FROM orders WHERE user_id = $user_id AND status = 'preparing') as preparing_orders,
    (SELECT COUNT(*) FROM orders WHERE user_id = $user_id AND status = 'delivered') as delivered_orders,
    (SELECT COUNT(*) FROM reviews WHERE user_id = $user_id) as total_reviews,
    (SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE user_id = $user_id AND status != 'cancelled') as total_spent,
    (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE user_id = $user_id) as avg_rating";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

$total_spent = $stats['total_spent'];

$latest_orders = mysqli_query(
    $conn,
    "SELECT o.*, 
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
     FROM orders o 
     WHERE o.user_id = $user_id 
     ORDER BY o.order_date DESC 
     LIMIT 3"
);

$latest_reviews = mysqli_query(
    $conn,
    "SELECT r.*, f.name as food_name 
     FROM reviews r 
     JOIN foods f ON r.food_id = f.id 
     WHERE r.user_id = $user_id 
     ORDER BY r.created_at DESC 
     LIMIT 3"
);

$suggested_foods = mysqli_query(
    $conn,
    "SELECT f.*, c.name as category_name,
            (SELECT AVG(rating) FROM reviews WHERE food_id = f.id) as avg_rating
     FROM foods f 
     JOIN categories c ON f.category_id = c.id 
     WHERE f.is_available = 1 
     ORDER BY RAND() 
     LIMIT 4"
);

$status_map = [
    'pending'   => ['text' => 'در انتظار', 'color' => '#E65100', 'bg' => '#FFF3E0'],
    'confirmed' => ['text' => 'تأیید شده', 'color' => '#1565C0', 'bg' => '#E3F2FD'],
    'preparing' => ['text' => 'در حال آماده‌سازی', 'color' => '#6A1B9A', 'bg' => '#EDE7F6'],
    'delivered' => ['text' => 'تحویل شده', 'color' => '#2E7D32', 'bg' => '#E8F5E9'],
    'cancelled' => ['text' => 'لغو شده', 'color' => '#C62828', 'bg' => '#FFEBEE'],
];

// تنظیم مسیر تصویر پیش‌فرض
$default_food_image = BASE_URL . 'assets/images/foods/default-food.jpg';
?>

<link rel="stylesheet" href="../assets/css/user_dashboard.css">

<div class="dashboard-page">
    <div class="dashboard-wrapper">

        <aside class="dash-sidebar">
            <div class="profile-card-mini">
                <div class="profile-card-mini__avatar">
                    <?php echo mb_substr($_SESSION['full_name'], 0, 1, 'UTF-8'); ?>
                    <span class="profile-card-mini__dot"></span>
                </div>
                <div>
                    <h2><?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>
                    <span class="profile-card-mini__email"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    <span class="profile-card-mini__date"><?php echo fullJalaliDate(date('Y-m-d')); ?></span>
                </div>
            </div>

            <div class="quick-links-card">
                <div class="quick-links-card__header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    دسترسی سریع
                </div>
                <div class="quick-links-list">
                    <a href="profile.php" class="quick-link-item">
                        <span class="quick-link-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </span>
                        <span class="quick-link-item__text">
                            <span class="quick-link-item__title">ویرایش پروفایل</span>
                            <span class="quick-link-item__sub">بروزرسانی اطلاعات</span>
                        </span>
                    </a>
                    <a href="orders.php" class="quick-link-item">
                        <span class="quick-link-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                        </span>
                        <span class="quick-link-item__text">
                            <span class="quick-link-item__title">سفارش‌های من</span>
                            <span class="quick-link-item__sub">پیگیری سفارش‌ها</span>
                        </span>
                    </a>
                    <a href="reviews.php" class="quick-link-item">
                        <span class="quick-link-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                        </span>
                        <span class="quick-link-item__text">
                            <span class="quick-link-item__title">نظرات من</span>
                            <span class="quick-link-item__sub"><?php echo number_format($stats['total_reviews']); ?> نظر ثبت شده</span>
                        </span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>main/cart.php" class="quick-link-item">
                        <span class="quick-link-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1" />
                                <circle cx="20" cy="21" r="1" />
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                            </svg>
                        </span>
                        <span class="quick-link-item__text">
                            <span class="quick-link-item__title">سبد خرید</span>
                            <span class="quick-link-item__sub"><?php echo getCartCount(); ?> آیتم</span>
                        </span>
                        <?php if (getCartCount() > 0): ?>
                            <span class="quick-link-item__badge"><?php echo getCartCount(); ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </aside>

        <div class="dash-content">

            <a href="<?php echo BASE_URL; ?>main/menu.php" class="btn btn--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                سفارش جدید
            </a>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-card__icon stat-icon--all">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                    </div>
                    <div>
                        <div class="stat-card__value"><?php echo number_format($stats['total_orders']); ?></div>
                        <div class="stat-card__label">کل سفارش‌ها</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon stat-icon--pending">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <div>
                        <div class="stat-card__value"><?php echo number_format($stats['pending_orders']); ?></div>
                        <div class="stat-card__label">در انتظار</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon stat-icon--preparing">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                        </svg>
                    </div>
                    <div>
                        <div class="stat-card__value"><?php echo number_format($stats['preparing_orders']); ?></div>
                        <div class="stat-card__label">در حال آماده‌سازی</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon stat-icon--delivered">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </div>
                    <div>
                        <div class="stat-card__value"><?php echo number_format($stats['delivered_orders']); ?></div>
                        <div class="stat-card__label">تحویل شده</div>
                    </div>
                </div>
            </div>

            <div class="sec-stats-row">
                <div class="sec-stat-card">
                    <div class="sec-stat-card__icon sec-stat-card__icon--spent">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <div>
                        <div class="sec-stat-card__value"><?php echo number_format($total_spent); ?> تومان</div>
                        <div class="sec-stat-card__label">کل خرید</div>
                    </div>
                </div>

                <div class="sec-stat-card">
                    <div class="sec-stat-card__icon sec-stat-card__icon--reviews">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="sec-stat-card__value"><?php echo number_format($stats['total_reviews']); ?></div>
                        <div class="sec-stat-card__label">نظر ثبت شده</div>
                    </div>
                </div>

                <div class="sec-stat-card">
                    <div class="sec-stat-card__icon sec-stat-card__icon--rating">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="sec-stat-card__value"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                        <div class="sec-stat-card__label">میانگین امتیاز</div>
                    </div>
                </div>
            </div>

            <div class="content-grid">

                <div class="dash-card">
                    <div class="dash-card__header">
                        <h3 class="dash-card__title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                            سفارش‌های اخیر
                        </h3>
                        <a href="orders.php" class="dash-card__link">مشاهده همه</a>
                    </div>
                    <div class="dash-card__body dash-card__body--scroll">
                        <?php if (mysqli_num_rows($latest_orders) > 0): ?>
                            <?php while ($order = mysqli_fetch_assoc($latest_orders)):
                                $s = $status_map[$order['status']] ?? $status_map['pending'];
                            ?>
                                <div class="order-row">
                                    <span class="order-row__id">#<?php echo $order['id']; ?></span>
                                    <span class="order-row__items"><?php echo $order['item_count']; ?> آیتم</span>
                                    <span class="order-row__price"><?php echo number_format($order['total_price']); ?> ت</span>
                                    <span class="order-row__date"><?php echo toJalali($order['order_date'], 'Y/m/d'); ?></span>
                                    <span class="status-badge" style="background:<?php echo $s['bg']; ?>;color:<?php echo $s['color']; ?>;">
                                        <?php echo $s['text']; ?>
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state__icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <circle cx="9" cy="21" r="1" />
                                        <circle cx="20" cy="21" r="1" />
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                                    </svg>
                                </div>
                                <p>هنوز سفارشی ثبت نکرده‌اید</p>
                                <a href="<?php echo BASE_URL; ?>main/menu.php" class="btn btn--primary btn--xs">مشاهده منو</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dash-card">
                    <div class="dash-card__header">
                        <h3 class="dash-card__title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                            نظرات اخیر
                        </h3>
                        <a href="reviews.php" class="dash-card__link">مشاهده همه</a>
                    </div>
                    <div class="dash-card__body dash-card__body--scroll">
                        <?php if (mysqli_num_rows($latest_reviews) > 0): ?>
                            <?php while ($review = mysqli_fetch_assoc($latest_reviews)): ?>
                                <div class="review-row">
                                    <div class="review-row__stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg viewBox="0 0 24 24" fill="<?php echo $i <= $review['rating'] ? '#FFD700' : '#DDD'; ?>" stroke="none">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="review-row__info">
                                        <span class="review-row__food"><?php echo htmlspecialchars($review['food_name']); ?></span>
                                        <p class="review-row__text"><?php echo excerpt($review['comment'], 45); ?></p>
                                        <span class="review-row__date"><?php echo toJalali($review['created_at'], 'Y/m/d'); ?></span>
                                    </div>

                                    <?php if (!empty($review['admin_reply'])): ?>
                                        <div class="review-row__admin-reply">
                                            <div class="admin-reply-mini-header">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                                </svg>
                                                پاسخ ادمین
                                            </div>
                                            <p class="admin-reply-mini-text"><?php echo excerpt($review['admin_reply'], 60); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state__icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                        <line x1="8" y1="10" x2="16" y2="10" />
                                    </svg>
                                </div>
                                <p>هنوز نظری ثبت نکرده‌اید</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dash-card" style="grid-column: 1 / -1;">
                    <div class="dash-card__header">
                        <h3 class="dash-card__title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2z" />
                            </svg>
                            پیشنهاد ویژه برای شما
                        </h3>
                    </div>
                    <div class="dash-card__body">
                        <?php if (mysqli_num_rows($suggested_foods) > 0): ?>
                            <div class="suggested-list" style="display:grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                                <?php while ($food = mysqli_fetch_assoc($suggested_foods)):
                                    // بررسی وجود تصویر غذا
                                    $food_image = $food['image'];
                                    $has_food_image = !empty($food_image) && file_exists(UPLOAD_DIR . $food_image);
                                ?>
                                    <a href="<?php echo BASE_URL; ?>main/food-detail.php?id=<?php echo $food['id']; ?>" class="suggested-item">
                                        <div class="suggested-item__thumb">
                                            <?php if ($has_food_image): ?>
                                                <img src="<?php echo UPLOAD_URL . $food_image; ?>" 
                                                     alt="<?php echo htmlspecialchars($food['name']); ?>" loading="lazy">
                                            <?php else: ?>
                                                <img src="<?php echo $default_food_image; ?>" 
                                                     alt="<?php echo htmlspecialchars($food['name']); ?>" loading="lazy">
                                            <?php endif; ?>
                                        </div>
                                        <div class="suggested-item__info">
                                            <span class="suggested-item__name"><?php echo htmlspecialchars($food['name']); ?></span>
                                            <span class="suggested-item__cat"><?php echo htmlspecialchars($food['category_name']); ?></span>
                                        </div>
                                        <span class="suggested-item__price"><?php echo number_format($food['price']); ?> ت</span>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>main/menu.php" class="btn btn--outline-sm btn--block" style="margin-top:12px;">
                            مشاهده منوی کامل
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>