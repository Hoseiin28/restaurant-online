<?php

$page_title = 'خانه';
require_once 'includes/header.php';

$categories_sql = "SELECT c.*, COUNT(f.id) as food_count 
                   FROM categories c 
                   LEFT JOIN foods f ON c.id = f.category_id AND f.is_available = 1 
                   GROUP BY c.id 
                   ORDER BY food_count DESC";
$categories_result = mysqli_query($conn, $categories_sql);

$featured_sql = "SELECT f.*, c.name as category_name,
                (SELECT AVG(rating) FROM reviews WHERE food_id = f.id) as avg_rating,
                (SELECT COUNT(*) FROM reviews WHERE food_id = f.id) as review_count
                FROM foods f 
                JOIN categories c ON f.category_id = c.id 
                WHERE f.is_available = 1 
                ORDER BY f.created_at DESC 
                LIMIT 8";
$featured_result = mysqli_query($conn, $featured_sql);

$popular_sql = "SELECT f.*, c.name as category_name,
               (SELECT AVG(rating) FROM reviews WHERE food_id = f.id) as avg_rating,
               (SELECT COUNT(*) FROM reviews WHERE food_id = f.id) as review_count
               FROM foods f 
               JOIN categories c ON f.category_id = c.id 
               WHERE f.is_available = 1 
               HAVING avg_rating IS NOT NULL
               ORDER BY avg_rating DESC, review_count DESC 
               LIMIT 4";
$popular_result = mysqli_query($conn, $popular_sql);

$stats = [
    'orders' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'] ?? 0,
    'users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='user'"))['count'] ?? 0,
    'foods' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM foods WHERE is_available=1"))['count'] ?? 0,
    'reviews' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews"))['count'] ?? 0,
];

$default_food_image = 'assets/images/foods/default-food.jpg';
$default_category_image = 'assets/images/foods/default-food.jpg';
?>

<link rel="stylesheet" href="assets/css/index.css">

<section class="hero">
    <div class="hero__container">
        <div class="hero__content">
            <div class="hero__badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                تحویل سریع زیر ۳۰ دقیقه
            </div>
            <h1 class="hero__title">
                طعم <span>واقعی</span> غذاهای ایرانی
            </h1>
            <p class="hero__desc">
                تجربه‌ای متفاوت از غذاهای سنتی و مدرن با بهترین کیفیت، تازه‌ترین مواد اولیه و ارسال سریع درب منزل
            </p>
            <div class="hero__actions">
                <a href="main/menu.php" class="btn btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    مشاهده منو
                </a>
                <a href="#categories" class="btn btn--outline-light">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    دسته‌بندی‌ها
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section section--light">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                        <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                        <line x1="6" y1="1" x2="6" y2="4"/>
                        <line x1="10" y1="1" x2="10" y2="4"/>
                        <line x1="14" y1="1" x2="14" y2="4"/>
                    </svg>
                </div>
                <h3>مواد اولیه تازه</h3>
                <p>روزانه از بازار تهیه می‌شود</p>
            </div>
            <div class="feature-card">
                <div class="feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <h3>تحویل سریع</h3>
                <p>کمتر از ۳۰ دقیقه درب منزل</p>
            </div>
            <div class="feature-card">
                <div class="feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <h3>قیمت مناسب</h3>
                <p>بهترین قیمت با تخفیف ویژه</p>
            </div>
            <div class="feature-card">
                <div class="feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2z"/>
                    </svg>
                </div>
                <h3>رضایت مشتریان</h3>
                <p><?php echo number_format($stats['reviews']); ?> نظر ثبت شده</p>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="categories">
    <div class="container">
        <div class="section__header">
            <span class="section__label">دسته‌بندی‌ها</span>
            <h2 class="section__title">غذای مورد علاقه‌ات رو پیدا کن</h2>
            <p class="section__subtitle">انواع غذاهای ایرانی، فست‌فود، نوشیدنی و دسر در یک جا</p>
        </div>

        <div class="categories-grid">
            <?php
            mysqli_data_seek($categories_result, 0);
            while ($cat = mysqli_fetch_assoc($categories_result)):
                $cat_image = $cat['image'];
                $has_cat_image = !empty($cat_image) && file_exists(UPLOAD_DIR . $cat_image);
            ?>
                <a href="main/menu.php?category=<?php echo $cat['id']; ?>" class="category-card">
                    <?php if ($has_cat_image): ?>
                        <img src="<?php echo UPLOAD_URL . $cat_image; ?>"
                            alt="<?php echo htmlspecialchars($cat['name']); ?>"
                            class="category-card__img"
                            loading="lazy">
                    <?php else: ?>
                        <img src="<?php echo $default_category_image; ?>"
                            alt="<?php echo htmlspecialchars($cat['name']); ?>"
                            class="category-card__img"
                            loading="lazy">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <span class="category-card__count"><?php echo $cat['food_count']; ?> غذا</span>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php if ($popular_result && mysqli_num_rows($popular_result) > 0): ?>
    <section class="section section--light">
        <div class="container">
            <div class="section__header">
                <span class="section__label">محبوب‌ترین‌ها</span>
                <h2 class="section__title">غذاهای پرطرفدار</h2>
                <p class="section__subtitle">برترین غذاها بر اساس امتیاز و نظرات کاربران</p>
            </div>

            <div class="foods-grid">
                <?php while ($food = mysqli_fetch_assoc($popular_result)):
                 
                    $food_image = $food['image'];
                    $has_food_image = !empty($food_image) && file_exists(UPLOAD_DIR . $food_image);
                    $display_image = $has_food_image ? UPLOAD_URL . $food_image : $default_food_image;
                ?>
                    <div class="food-card">
                        <div class="food-card__img-wrap">
                            <img src="<?php echo $display_image; ?>"
                                alt="<?php echo htmlspecialchars($food['name']); ?>"
                                loading="lazy">

                            <?php if ($food['avg_rating']): ?>
                                <div class="food-card__rating">
                                    <svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                    <?php echo number_format($food['avg_rating'], 1); ?>
                                </div>
                            <?php endif; ?>

                            <div class="food-card__overlay">
                                <a href="main/food-detail.php?id=<?php echo $food['id']; ?>" class="food-card__overlay-link">
                                    مشاهده و سفارش
                                </a>
                            </div>
                        </div>

                        <div class="food-card__body">
                            <span class="food-card__category"><?php echo htmlspecialchars($food['category_name']); ?></span>
                            <h3 class="food-card__title">
                                <a href="main/food-detail.php?id=<?php echo $food['id']; ?>">
                                    <?php echo htmlspecialchars($food['name']); ?>
                                </a>
                            </h3>
                            <p class="food-card__desc"><?php echo excerpt($food['description'], 55); ?></p>

                            <div class="food-card__footer">
                                <div class="food-card__price">
                                    <span class="food-card__price-value"><?php echo number_format($food['price']); ?></span>
                                    <span class="food-card__price-unit">تومان</span>
                                </div>
                                <button onclick="handleAddToCart(this, <?php echo $food['id']; ?>, '<?php echo addslashes(htmlspecialchars($food['name'])); ?>', <?php echo $food['price']; ?>)"
                                    class="btn btn--primary-fill btn--sm btn-add">
                                    <span class="btn-add__text">افزودن</span>
                                    <span class="btn-add__success">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        اضافه شد
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section section--alt">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--orders">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-card__number"><?php echo number_format($stats['orders']); ?></div>
                    <div class="stat-card__label">سفارش موفق</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--users">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-card__number"><?php echo number_format($stats['users']); ?></div>
                    <div class="stat-card__label">مشتری فعال</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--foods">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                        <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-card__number"><?php echo number_format($stats['foods']); ?></div>
                    <div class="stat-card__label">غذای متنوع</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--reviews">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-card__number"><?php echo number_format($stats['reviews']); ?></div>
                    <div class="stat-card__label">نظر و امتیاز</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($featured_result && mysqli_num_rows($featured_result) > 0):
    mysqli_data_seek($featured_result, 0);
?>
    <section class="section section--light">
        <div class="container">
            <div class="section__header">
                <span class="section__label">جدیدترین‌ها</span>
                <h2 class="section__title">غذاهای تازه اضافه شده</h2>
                <p class="section__subtitle">آخرین غذاهایی که به منوی ما اضافه شده‌اند</p>
            </div>

            <div class="foods-grid">
                <?php while ($food = mysqli_fetch_assoc($featured_result)):
                
                    $food_image = $food['image'];
                    $has_food_image = !empty($food_image) && file_exists(UPLOAD_DIR . $food_image);
                    $display_image = $has_food_image ? UPLOAD_URL . $food_image : $default_food_image;
                ?>
                    <div class="food-card">
                        <div class="food-card__img-wrap">
                            <img src="<?php echo $display_image; ?>"
                                alt="<?php echo htmlspecialchars($food['name']); ?>"
                                loading="lazy">

                            <?php if ($food['avg_rating']): ?>
                                <div class="food-card__rating">
                                    <svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                    <?php echo number_format($food['avg_rating'], 1); ?>
                                </div>
                            <?php endif; ?>

                            <div class="food-card__overlay">
                                <a href="main/food-detail.php?id=<?php echo $food['id']; ?>" class="food-card__overlay-link">
                                    مشاهده و سفارش
                                </a>
                            </div>
                        </div>

                        <div class="food-card__body">
                            <span class="food-card__category"><?php echo htmlspecialchars($food['category_name']); ?></span>
                            <h3 class="food-card__title">
                                <a href="main/food-detail.php?id=<?php echo $food['id']; ?>">
                                    <?php echo htmlspecialchars($food['name']); ?>
                                </a>
                            </h3>
                            <p class="food-card__desc"><?php echo excerpt($food['description'], 55); ?></p>

                            <div class="food-card__footer">
                                <div class="food-card__price">
                                    <span class="food-card__price-value"><?php echo number_format($food['price']); ?></span>
                                    <span class="food-card__price-unit">تومان</span>
                                </div>
                                <button onclick="handleAddToCart(this, <?php echo $food['id']; ?>, '<?php echo addslashes(htmlspecialchars($food['name'])); ?>', <?php echo $food['price']; ?>)"
                                    class="btn btn--primary-fill btn--sm btn-add">
                                    <span class="btn-add__text">افزودن</span>
                                    <span class="btn-add__success">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        اضافه شد
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="cta">
    <div class="cta__content">
        <h2 class="cta__title">آماده‌ای یه غذای عالی سفارش بدی؟</h2>
        <p class="cta__desc">همین حالا منو رو ببین، غذای مورد علاقه‌ات رو انتخاب کن و کمتر از نیم ساعت درب منزل تحویل بگیر</p>
        <a href="main/menu.php" class="btn btn--primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <rect x="1" y="3" width="15" height="13"/>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
            سفارش آنلاین غذا
        </a>
    </div>
</section>

<script src="assets/js/index.js"></script>

<?php require_once 'includes/footer.php'; ?>