<?php

$page_title = 'جزئیات غذا';
require_once '../includes/header.php';

$food_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($food_id === 0) {
    redirect(BASE_URL . 'main/menu.php');
}

$sql = "SELECT f.*, c.name as category_name,
        (SELECT AVG(rating) FROM reviews WHERE food_id = f.id) as avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE food_id = f.id) as review_count,
        (SELECT COUNT(*) FROM order_items WHERE food_id = f.id) as order_count
        FROM foods f 
        JOIN categories c ON f.category_id = c.id 
        WHERE f.id = $food_id AND f.is_available = 1";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 0) {
    setErrorMessage('غذای مورد نظر یافت نشد.');
    redirect(BASE_URL . 'main/menu.php');
}

$food = mysqli_fetch_assoc($result);

$stars_sql = "SELECT 
    COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
    COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
    COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
    COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
    COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
    FROM reviews WHERE food_id = $food_id";
$stars_result = mysqli_query($conn, $stars_sql);
$stars_data = mysqli_fetch_assoc($stars_result);

$related_sql = "SELECT f.*, 
                (SELECT AVG(rating) FROM reviews WHERE food_id = f.id) as avg_rating
                FROM foods f 
                WHERE f.category_id = {$food['category_id']} 
                AND f.id != $food_id 
                AND f.is_available = 1 
                ORDER BY RAND() 
                LIMIT 4";
$related_result = mysqli_query($conn, $related_sql);

$review_page = isset($_GET['review_page']) ? max(1, (int)$_GET['review_page']) : 1;
$review_per_page = 5;
$review_offset = ($review_page - 1) * $review_per_page;

$review_count_sql = "SELECT COUNT(*) as total FROM reviews WHERE food_id = $food_id";
$review_total = mysqli_fetch_assoc(mysqli_query($conn, $review_count_sql))['total'];
$review_pages = ceil($review_total / $review_per_page);

$reviews_sql = "SELECT r.*, u.full_name 
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.food_id = $food_id 
                ORDER BY r.created_at DESC 
                LIMIT $review_offset, $review_per_page";
$reviews_result = mysqli_query($conn, $reviews_sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $user_id = $_SESSION['user_id'];

        $check_sql = "SELECT id FROM reviews WHERE user_id = $user_id AND food_id = $food_id";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            setErrorMessage('شما قبلاً برای این غذا نظر ثبت کرده‌اید.');
        } else {
            $insert_sql = "INSERT INTO reviews (user_id, food_id, rating, comment) 
                          VALUES ($user_id, $food_id, $rating, '$comment')";
            if (mysqli_query($conn, $insert_sql)) {
                setSuccessMessage('نظر شما با موفقیت ثبت شد.');
                redirect(BASE_URL . "main/food-detail.php?id=$food_id");
            } else {
                setErrorMessage('خطا در ثبت نظر.');
            }
        }
    } else {
        setErrorMessage('لطفاً امتیاز و نظر خود را وارد کنید.');
    }
}

$rating_percentages = [];
for ($i = 1; $i <= 5; $i++) {
    $key = $i . '_star';
    $rating_percentages[$i] = ($food['review_count'] > 0 && isset($stars_data[$key]))
        ? round(($stars_data[$key] / $food['review_count']) * 100)
        : 0;
}

// تنظیم مسیر تصویر پیش‌فرض
$default_food_image = BASE_URL . 'assets/images/foods/default-food.jpg';

// بررسی وجود تصویر غذای اصلی
$food_image = $food['image'];
$has_food_image = !empty($food_image) && file_exists(UPLOAD_DIR . $food_image);
$display_food_image = $has_food_image ? UPLOAD_URL . $food_image : $default_food_image;
?>

<link rel="stylesheet" href="../assets/css/food-detail.css">

<nav class="breadcrumb" aria-label="مسیر">
    <a href="<?php echo BASE_URL; ?>">خانه</a>
    <span class="breadcrumb__sep">/</span>
    <a href="menu.php">منو</a>
    <span class="breadcrumb__sep">/</span>
    <?php if ($food['category_name']): ?>
        <a href="menu.php?category=<?php echo $food['category_id']; ?>"><?php echo htmlspecialchars($food['category_name']); ?></a>
        <span class="breadcrumb__sep">/</span>
    <?php endif; ?>
    <span class="breadcrumb__current"><?php echo htmlspecialchars($food['name']); ?></span>
</nav>

<div class="food-detail-page">
    <div class="food-detail-wrapper">

        <div class="food-detail__visual">
            <div class="food-detail__image-wrap">
                <img src="<?php echo $display_food_image; ?>"
                    alt="<?php echo htmlspecialchars($food['name']); ?>">

                <?php if ($food['order_count'] > 50): ?>
                    <div class="food-detail__popular-badge">
                        <svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                        پرطرفدار
                    </div>
                <?php endif; ?>
            </div>

            <div class="food-detail__features-side">
                <div class="feature-mini">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    تحویل سریع
                </div>
                <div class="feature-mini">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    تضمین کیفیت
                </div>
                <div class="feature-mini">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13" />
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
                    </svg>
                    بسته‌بندی بهداشتی
                </div>
                <div class="feature-mini">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z" />
                    </svg>
                    مواد اولیه تازه
                </div>
            </div>
        </div>

        <div class="food-detail__content">

            <div class="food-detail__header">
                <span class="food-detail__category-tag">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                    </svg>
                    <?php echo htmlspecialchars($food['category_name']); ?>
                </span>
                <h1><?php echo htmlspecialchars($food['name']); ?></h1>

                <div class="food-detail__rating-row">
                    <?php if ($food['avg_rating']): ?>
                        <div class="stars-row">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg viewBox="0 0 24 24" fill="<?php echo $i <= round($food['avg_rating']) ? '#FFD700' : '#DDD'; ?>" stroke="none">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-num"><?php echo number_format($food['avg_rating'], 1); ?></span>
                        <span class="rating-total">(<?php echo $food['review_count']; ?> نظر)</span>
                    <?php else: ?>
                        <span class="rating-total">بدون امتیاز</span>
                    <?php endif; ?>
                    <span class="order-count-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        <?php echo number_format($food['order_count']); ?> سفارش
                    </span>
                </div>
            </div>

            <div class="food-detail__desc-card">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                    توضیحات
                </h3>
                <p><?php echo nl2br(htmlspecialchars($food['description'] ?: 'توضیحاتی ثبت نشده است.')); ?></p>
            </div>

            <div class="food-detail__purchase-card">
                <div class="food-detail__price-block">
                    <span class="food-detail__price-value"><?php echo number_format($food['price']); ?></span>
                    <span class="food-detail__price-unit">تومان</span>
                </div>

                <div class="qty-control">
                    <button type="button" class="qty-control__btn" onclick="changeQty(-1)" aria-label="کاهش تعداد">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                    </button>
                    <input type="number" id="quantity" class="qty-control__input" value="1" min="1" max="99" readonly>
                    <button type="button" class="qty-control__btn" onclick="changeQty(1)" aria-label="افزایش تعداد">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                    </button>
                </div>

                <button class="btn btn--primary btn-add-cart"
                    onclick="handleAddToCartDetail(this, <?php echo $food['id']; ?>, '<?php echo addslashes(htmlspecialchars($food['name'])); ?>', <?php echo $food['price']; ?>)">
                    <span class="btn-add-cart__text">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                        </svg>
                        افزودن به سبد
                    </span>
                    <span class="btn-add-cart__success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        اضافه شد
                    </span>
                </button>
            </div>

            <?php if ($food['review_count'] > 0): ?>
                <div class="rating-summary-row">
                    <div class="rating-summary-row__score">
                        <span class="rating-summary-row__number"><?php echo number_format($food['avg_rating'], 1); ?></span>
                        <span class="rating-summary-row__label">از ۵</span>
                    </div>
                    <div class="rating-summary-row__stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg viewBox="0 0 24 24" fill="<?php echo $i <= round($food['avg_rating']) ? '#FFD700' : '#DDD'; ?>" stroke="none">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        <?php endfor; ?>
                    </div>
                    <div class="rating-summary-row__bars">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <div class="rating-mini-row">
                                <span class="rating-mini-row__label"><?php echo $i; ?></span>
                                <div class="rating-mini-row__bar">
                                    <div class="rating-mini-row__fill" style="width: <?php echo $rating_percentages[$i]; ?>%"></div>
                                </div>
                                <span class="rating-mini-row__percent"><?php echo $rating_percentages[$i]; ?>%</span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isLoggedIn() && !isAdmin()): ?>
                <div class="review-form-card">
                    <h3>نظر خود را ثبت کنید</h3>
                    <form method="POST" action="">
                        <div class="star-input-row">
                            <span class="star-input-row__label">امتیاز:</span>
                            <div class="star-input-row__btns" id="starInput">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <button type="button" class="star-btn" data-value="<?php echo $i; ?>"
                                        onclick="setRating(<?php echo $i; ?>)" aria-label="<?php echo $i; ?> ستاره">
                                        <svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                    </button>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" required>
                        </div>
                        <textarea name="comment" placeholder="نظر خود را بنویسید..." required></textarea>
                        <button type="submit" class="btn btn--primary btn--sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                                <line x1="22" y1="2" x2="11" y2="13" />
                                <polygon points="22 2 15 22 11 13 2 9 22 2" />
                            </svg>
                            ثبت نظر
                        </button>
                    </form>
                </div>
            <?php elseif (!isLoggedIn()): ?>
                <div class="login-to-review">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18" style="vertical-align:middle;margin-left:4px;">
                        <rect x="3" y="11" width="18" height="11" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    برای ثبت نظر باید <a href="<?php echo BASE_URL; ?>authentication/login.php">وارد حساب کاربری</a> خود شوید.
                </div>
            <?php endif; ?>

            <h3 class="reviews-section-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                </svg>
                نظرات کاربران
                <span style="font-weight:400;color:var(--text-muted);font-size:0.75rem;">(<?php echo $food['review_count']; ?>)</span>
            </h3>

            <?php if ($reviews_result && mysqli_num_rows($reviews_result) > 0): ?>
                <div class="reviews-list">
                    <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                        <div class="review-card">
                            <div class="review-card__avatar">
                                <?php echo mb_substr($review['full_name'], 0, 1, 'UTF-8'); ?>
                            </div>
                            <div class="review-card__body">
                                <div class="review-card__meta">
                                    <span class="review-card__name"><?php echo htmlspecialchars($review['full_name']); ?></span>
                                    <span class="review-card__stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg viewBox="0 0 24 24" fill="<?php echo $i <= $review['rating'] ? '#FFD700' : '#DDD'; ?>" stroke="none">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                            </svg>
                                        <?php endfor; ?>
                                    </span>
                                    <span class="review-card__date"><?php echo toJalali($review['created_at'], 'Y/m/d'); ?></span>
                                </div>
                                <p class="review-card__text"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>

                                <?php if (!empty($review['admin_reply'])): ?>
                                    <div class="admin-reply">
                                        <div class="admin-reply__header">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                            </svg>
                                            پاسخ ادمین
                                            <span class="admin-reply__date"><?php echo toJalali($review['admin_reply_date'], 'Y/m/d'); ?></span>
                                        </div>
                                        <p class="admin-reply__text"><?php echo nl2br(htmlspecialchars($review['admin_reply'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($review_pages > 1): ?>
                    <div class="pagination-row">
                        <?php for ($i = 1; $i <= $review_pages; $i++): ?>
                            <a href="?id=<?php echo $food_id; ?>&review_page=<?php echo $i; ?>"
                                class="pagination-row__link <?php echo $i === $review_page ? 'pagination-row__link--active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-reviews-yet">
                    <div class="no-reviews-yet__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            <line x1="8" y1="10" x2="16" y2="10" />
                            <line x1="12" y1="6" x2="12" y2="14" />
                        </svg>
                    </div>
                    <p>هنوز نظری ثبت نشده. اولین نفری باشید که نظر می‌دهد!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($related_result && mysqli_num_rows($related_result) > 0): ?>
        <div class="related-section">
            <div class="container" style="max-width:var(--container);margin:0 auto;padding:0;">
                <h3 class="related-section__title">غذاهای مشابه</h3>
                <div class="related-grid">
                    <?php while ($related = mysqli_fetch_assoc($related_result)):
                        // بررسی وجود تصویر غذای مشابه
                        $related_image = $related['image'];
                        $has_related_image = !empty($related_image) && file_exists(UPLOAD_DIR . $related_image);
                        $display_related_image = $has_related_image ? UPLOAD_URL . $related_image : $default_food_image;
                    ?>
                        <a href="food-detail.php?id=<?php echo $related['id']; ?>" class="related-card">
                            <div class="related-card__img">
                                <img src="<?php echo $display_related_image; ?>"
                                    alt="<?php echo htmlspecialchars($related['name']); ?>" loading="lazy">
                            </div>
                            <div class="related-card__body">
                                <h4 class="related-card__title"><?php echo htmlspecialchars($related['name']); ?></h4>
                                <div class="related-card__footer">
                                    <span class="related-card__price"><?php echo number_format($related['price']); ?> ت</span>
                                    <?php if ($related['avg_rating']): ?>
                                        <span class="related-card__rating">
                                            <svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                            </svg>
                                            <?php echo number_format($related['avg_rating'], 1); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="../assets/js/food-detail.js">
</script>

<?php require_once '../includes/footer.php'; ?>