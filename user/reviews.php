<?php

$page_title = 'نظرات من';
require_once '../includes/header.php';

checkAccess('user');

$user_id = $_SESSION['user_id'];

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 6;

$count_sql = "SELECT COUNT(*) as total FROM reviews WHERE user_id = $user_id";
$total_reviews = mysqli_fetch_assoc(mysqli_query($conn, $count_sql))['total'];
$total_pages = ceil($total_reviews / $per_page);
$offset = ($page - 1) * $per_page;

$reviews_sql = "SELECT r.*, f.name as food_name, f.id as food_id, f.image as food_image,
                c.name as category_name
                FROM reviews r 
                JOIN foods f ON r.food_id = f.id 
                LEFT JOIN categories c ON f.category_id = c.id 
                WHERE r.user_id = $user_id 
                ORDER BY r.created_at DESC 
                LIMIT $offset, $per_page";
$reviews_result = mysqli_query($conn, $reviews_sql);

$stats = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT 
        COUNT(*) as total,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as low_star
     FROM reviews WHERE user_id = $user_id"
));

// تنظیم مسیر تصویر پیش‌فرض
$default_food_image = BASE_URL . 'assets/images/foods/default-food.jpg';
?>

<link rel="stylesheet" href="../assets/css/user_reviews.css">

<div class="reviews-page">
    <div class="reviews-wrapper">

        <aside class="reviews-sidebar">
            <?php if ($total_reviews > 0): ?>
                <div class="stats-card">
                    <div class="stats-card__header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2z" />
                        </svg>
                        <h3>خلاصه امتیازات</h3>
                    </div>
                    <div class="stats-list">
                        <div class="stat-row">
                            <div class="stat-row__icon stat-row__icon--avg">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="stat-row__value"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                                <div class="stat-row__label">میانگین امتیاز</div>
                            </div>
                        </div>
                        <div class="stat-row">
                            <div class="stat-row__icon stat-row__icon--five">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <div>
                                <div class="stat-row__value"><?php echo $stats['five_star']; ?></div>
                                <div class="stat-row__label">امتیاز عالی (۵ ستاره)</div>
                            </div>
                        </div>
                        <div class="stat-row">
                            <div class="stat-row__icon stat-row__icon--four">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H7" />
                                </svg>
                            </div>
                            <div>
                                <div class="stat-row__value"><?php echo $stats['four_star']; ?></div>
                                <div class="stat-row__label">خوب (۴ ستاره)</div>
                            </div>
                        </div>
                        <div class="stat-row">
                            <div class="stat-row__icon stat-row__icon--other">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="8" y1="15" x2="16" y2="15" />
                                    <line x1="9" y1="9" x2="9.01" y2="9" />
                                    <line x1="15" y1="9" x2="15.01" y2="9" />
                                </svg>
                            </div>
                            <div>
                                <div class="stat-row__value"><?php echo ($stats['three_star'] + $stats['low_star']); ?></div>
                                <div class="stat-row__label">سایر</div>
                            </div>
                        </div>
                    </div>

                    <div class="rating-dist">
                        <?php for ($star = 5; $star >= 1; $star--):
                            $count = 0;
                            if ($star == 5) $count = $stats['five_star'];
                            elseif ($star == 4) $count = $stats['four_star'];
                            elseif ($star == 3) $count = $stats['three_star'];
                            else $count = $stats['low_star'];
                            $percent = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
                        ?>
                            <div class="rating-bar-row">
                                <span class="rating-bar-row__label"><?php echo $star; ?> ستاره</span>
                                <div class="rating-bar-row__bar">
                                    <div class="rating-bar-row__fill" style="width:<?php echo $percent; ?>%;"></div>
                                </div>
                                <span class="rating-bar-row__count"><?php echo $count; ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>

        <div class="reviews-content">

            <div class="reviews-header">
                <div class="reviews-header__info">
                    <h1>نظرات من</h1>
                    <span><?php echo number_format($total_reviews); ?> نظر ثبت شده</span>
                </div>
                <a href="<?php echo BASE_URL; ?>main/menu.php" class="btn btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        <line x1="12" y1="8" x2="12" y2="14" />
                        <line x1="9" y1="11" x2="15" y2="11" />
                    </svg>
                    ثبت نظر جدید
                </a>
            </div>

            <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                <div class="reviews-list">
                    <?php while ($review = mysqli_fetch_assoc($reviews_result)):
                        // بررسی وجود تصویر غذا
                        $review_food_image = $review['food_image'];
                        $has_review_image = !empty($review_food_image) && file_exists(UPLOAD_DIR . $review_food_image);
                        $display_review_image = $has_review_image ? UPLOAD_URL . $review_food_image : $default_food_image;
                    ?>
                        <div class="review-card">
                            <div class="review-card__inner">
                                <a href="<?php echo BASE_URL; ?>main/food-detail.php?id=<?php echo $review['food_id']; ?>" class="review-card__thumb">
                                    <img src="<?php echo $display_review_image; ?>"
                                        alt="<?php echo htmlspecialchars($review['food_name']); ?>" loading="lazy">
                                </a>

                                <div class="review-card__content">
                                    <div class="review-card__top">
                                        <div>
                                            <a href="<?php echo BASE_URL; ?>main/food-detail.php?id=<?php echo $review['food_id']; ?>" class="review-card__food">
                                                <?php echo htmlspecialchars($review['food_name']); ?>
                                            </a>
                                            <?php if ($review['category_name']): ?>
                                                <span class="review-card__category"><?php echo htmlspecialchars($review['category_name']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="review-card__stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <svg viewBox="0 0 24 24" fill="<?php echo $i <= $review['rating'] ? '#FFD700' : '#DDD'; ?>" stroke="none">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                    </div>

                                    <p class="review-card__text">
                                        <?php echo !empty($review['comment']) ? nl2br(htmlspecialchars($review['comment'])) : '<span style="color:#999;">بدون توضیح</span>'; ?>
                                    </p>

                                    <?php if (!empty($review['admin_reply'])): ?>
                                        <div class="review-card__admin-reply">
                                            <div class="admin-reply-header">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                                </svg>
                                                پاسخ ادمین
                                                <span class="admin-reply-date"><?php echo toJalali($review['admin_reply_date'], 'Y/m/d'); ?></span>
                                            </div>
                                            <p class="admin-reply-text"><?php echo nl2br(htmlspecialchars($review['admin_reply'])); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="review-card__footer">
                                        <span class="review-card__date"><?php echo toJalali($review['created_at'], 'Y/m/d'); ?></span>
                                        <span class="review-card__rating-badge"><?php echo $review['rating']; ?> از ۵</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="reviews.php?page=<?php echo $page - 1; ?>" class="pagination__link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                    <polyline points="15 18 9 12 15 6" />
                                </svg>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++):
                            if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                <a href="reviews.php?page=<?php echo $i; ?>"
                                    class="pagination__link <?php echo $i === $page ? 'pagination__link--active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                <span class="pagination__dots">...</span>
                        <?php endif;
                        endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="reviews.php?page=<?php echo $page + 1; ?>" class="pagination__link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            <line x1="8" y1="10" x2="16" y2="10" />
                            <line x1="12" y1="6" x2="12" y2="14" />
                        </svg>
                    </div>
                    <h2>هنوز نظری ثبت نکرده‌اید</h2>
                    <p>بعد از سفارش غذا، می‌توانید نظر و امتیاز خود را ثبت کنید و به دیگران در انتخاب بهتر کمک کنید.</p>
                    <a href="<?php echo BASE_URL; ?>main/menu.php" class="btn btn--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                        </svg>
                        مشاهده منو و سفارش
                    </a>
                </div>
            <?php endif; ?>

            <a href="dashboard.php" class="btn btn--ghost" style="margin-top:16px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                    <line x1="19" y1="12" x2="5" y2="12" />
                    <polyline points="12 19 5 12 12 5" />
                </svg>
                بازگشت به داشبورد
            </a>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>