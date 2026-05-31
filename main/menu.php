<?php

$page_title = 'منو غذا';
require_once '../includes/header.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;

$where = ["f.is_available = 1"];

if ($category_id > 0) {
    $where[] = "f.category_id = $category_id";
}
if (!empty($search)) {
    $where[] = "(f.name LIKE '%$search%' OR f.description LIKE '%$search%')";
}
if ($min_price > 0) {
    $where[] = "f.price >= $min_price";
}
if ($max_price > 0) {
    $where[] = "f.price <= $max_price";
}

$where_clause = 'WHERE ' . implode(' AND ', $where);

$order_clause = 'ORDER BY f.created_at DESC';
switch ($sort) {
    case 'oldest':       $order_clause = 'ORDER BY f.created_at ASC'; break;
    case 'name_asc':     $order_clause = 'ORDER BY f.name ASC'; break;
    case 'name_desc':    $order_clause = 'ORDER BY f.name DESC'; break;
    case 'price_asc':    $order_clause = 'ORDER BY f.price ASC'; break;
    case 'price_desc':   $order_clause = 'ORDER BY f.price DESC'; break;
    case 'popular':      $order_clause = 'ORDER BY order_count DESC'; break;
    case 'rating':       $order_clause = 'ORDER BY avg_rating DESC'; break;
}

$count_sql = "SELECT COUNT(*) as total FROM foods f $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$total_foods = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_foods / $per_page);
$offset = ($page - 1) * $per_page;

$foods_sql = "SELECT f.*, c.name as category_name,
              (SELECT AVG(rating) FROM reviews WHERE food_id = f.id) as avg_rating,
              (SELECT COUNT(*) FROM reviews WHERE food_id = f.id) as review_count,
              (SELECT COUNT(*) FROM order_items WHERE food_id = f.id) as order_count
              FROM foods f 
              JOIN categories c ON f.category_id = c.id 
              $where_clause 
              $order_clause 
              LIMIT $offset, $per_page";
$foods_result = mysqli_query($conn, $foods_sql);

$categories_sql = "SELECT c.*, COUNT(f.id) as food_count 
                   FROM categories c 
                   LEFT JOIN foods f ON c.id = f.category_id AND f.is_available = 1 
                   GROUP BY c.id 
                   HAVING food_count > 0
                   ORDER BY food_count DESC";
$categories_result = mysqli_query($conn, $categories_sql);

$active_category = null;
if ($category_id > 0) {
    $cat_result = mysqli_query($conn, "SELECT * FROM categories WHERE id = $category_id");
    $active_category = mysqli_fetch_assoc($cat_result);
}

$price_range = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT MIN(price) as min_price, MAX(price) as max_price FROM foods WHERE is_available = 1"
));

// تنظیم مسیر تصاویر پیش‌فرض
$default_food_image = BASE_URL . 'assets/images/foods/default-food.jpg';
$default_category_image = BASE_URL . 'assets/images/foods/default-food.jpg';
?>

<link rel="stylesheet" href="../assets/css/menu.css">

<section class="menu-hero">
    <div class="menu-hero__content">
        <h1>
            <?php echo $active_category ? htmlspecialchars($active_category['name']) : 'منوی رستوران'; ?>
        </h1>
        <p>
            <?php echo $active_category && !empty($active_category['description']) 
                ? htmlspecialchars($active_category['description']) 
                : 'انواع غذاهای خوشمزه با بهترین کیفیت و تازه‌ترین مواد اولیه'; ?>
        </p>
        <div class="menu-hero__badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
            </svg>
            <?php echo number_format($total_foods); ?> غذای موجود
        </div>
    </div>
</section>

<div class="menu-layout">
    
    <aside class="menu-sidebar">
        
        <div class="sidebar-card">
            <h4 class="sidebar-card__title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                جستجو
            </h4>
            <form method="GET" action="" class="sidebar-search">
                <input type="text" name="search" class="form-input" placeholder="نام غذا..." value="<?php echo htmlspecialchars($search); ?>">
                <?php if ($category_id > 0): ?>
                    <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                <?php endif; ?>
                <button type="submit" class="btn btn--primary btn--block btn--sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    جستجو
                </button>
            </form>
        </div>

        <div class="sidebar-card">
            <h4 class="sidebar-card__title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>
                دسته‌بندی‌ها
            </h4>
            <ul class="category-nav">
                <li>
                    <a href="menu.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>"
                        class="category-nav__link <?php echo $category_id === 0 ? 'category-nav__link--active' : ''; ?>">
                        <img src="<?php echo $default_category_image; ?>"
                            alt="همه غذاها"
                            class="category-nav__img" loading="lazy">
                        <span>همه غذاها</span>
                        <span class="category-nav__count"><?php echo $total_foods; ?></span>
                    </a>
                </li>
                <?php
                mysqli_data_seek($categories_result, 0);
                while ($cat = mysqli_fetch_assoc($categories_result)):
                    // بررسی وجود تصویر دسته‌بندی
                    $cat_image = $cat['image'];
                    $has_cat_image = !empty($cat_image) && file_exists(UPLOAD_DIR . $cat_image);
                ?>
                    <li>
                        <a href="menu.php?category=<?php echo $cat['id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                            class="category-nav__link <?php echo $category_id === $cat['id'] ? 'category-nav__link--active' : ''; ?>">
                            <?php if ($has_cat_image): ?>
                                <img src="<?php echo UPLOAD_URL . $cat_image; ?>"
                                    alt="<?php echo htmlspecialchars($cat['name']); ?>"
                                    class="category-nav__img" loading="lazy">
                            <?php else: ?>
                                <img src="<?php echo $default_category_image; ?>"
                                    alt="<?php echo htmlspecialchars($cat['name']); ?>"
                                    class="category-nav__img" loading="lazy">
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($cat['name']); ?></span>
                            <span class="category-nav__count"><?php echo $cat['food_count']; ?></span>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <div class="sidebar-card">
            <h4 class="sidebar-card__title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                محدوده قیمت (تومان)
            </h4>
            <form method="GET" action="">
                <?php if ($category_id > 0): ?>
                    <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                <?php endif; ?>
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>
                <div class="price-row">
                    <input type="number" name="min_price" class="form-input" placeholder="از" value="<?php echo $min_price > 0 ? $min_price : ''; ?>">
                    <span>تا</span>
                    <input type="number" name="max_price" class="form-input" placeholder="تا" value="<?php echo $max_price > 0 ? $max_price : ''; ?>">
                </div>
                <button type="submit" class="btn btn--outline btn--block btn--sm">اعمال فیلتر</button>
            </form>
        </div>

        <?php if (!empty($search) || $category_id > 0 || $min_price > 0 || $max_price > 0): ?>
            <a href="menu.php" class="btn btn--ghost btn--block btn--sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                حذف همه فیلترها
            </a>
        <?php endif; ?>
    </aside>

    <div class="menu-main">
        
        <div class="menu-toolbar">
            <?php if (!empty($search)): ?>
                <span class="menu-toolbar__text">
                    نتایج جستجو برای: <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                </span>
            <?php elseif ($category_id > 0): ?>
                <span class="menu-toolbar__text">
                    دسته‌بندی: <strong><?php echo htmlspecialchars($active_category['name']); ?></strong>
                </span>
            <?php else: ?>
                <span class="menu-toolbar__text">
                    <strong><?php echo number_format($total_foods); ?></strong> غذا
                </span>
            <?php endif; ?>

            <select class="sort-select" onchange="changeSort(this.value)" aria-label="مرتب‌سازی">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>جدیدترین</option>
                <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>محبوب‌ترین</option>
                <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>بیشترین امتیاز</option>
                <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>ارزان‌ترین</option>
                <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>گران‌ترین</option>
            </select>
        </div>

        <?php if (mysqli_num_rows($foods_result) > 0): ?>
            <div class="foods-grid">
                <?php while ($food = mysqli_fetch_assoc($foods_result)):
                    // بررسی وجود تصویر غذا
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
                                <a href="food-detail.php?id=<?php echo $food['id']; ?>" class="food-card__overlay-link">
                                    مشاهده و سفارش
                                </a>
                            </div>
                        </div>

                        <div class="food-card__body">
                            <span class="food-card__category"><?php echo htmlspecialchars($food['category_name']); ?></span>
                            <h3 class="food-card__title">
                                <a href="food-detail.php?id=<?php echo $food['id']; ?>">
                                    <?php echo htmlspecialchars($food['name']); ?>
                                </a>
                            </h3>
                            <p class="food-card__desc"><?php echo excerpt($food['description'], 60); ?></p>

                            <?php if ($food['order_count'] > 0): ?>
                                <span class="food-card__orders">
                                    <?php echo number_format($food['order_count']); ?> سفارش
                                </span>
                            <?php endif; ?>

                            <div class="food-card__footer">
                                <div class="food-card__price">
                                    <span class="food-card__price-value"><?php echo number_format($food['price']); ?></span>
                                    <span class="food-card__price-unit">تومان</span>
                                </div>
                                <button onclick="handleAddToCart(this, <?php echo $food['id']; ?>, '<?php echo addslashes(htmlspecialchars($food['name'])); ?>', <?php echo $food['price']; ?>)"
                                    class="btn btn--primary btn--sm btn-add">
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

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $query_params = $_GET;
                    unset($query_params['page']);
                    $base_url = 'menu.php?' . http_build_query($query_params);
                    $base_url = rtrim($base_url, '?');
                    if (empty($query_params)) $base_url = 'menu.php?';
                    if (strpos($base_url, '?') === false) $base_url .= '?';
                    $base_url .= (substr($base_url, -1) !== '?' && substr($base_url, -1) !== '&') ? '&' : '';
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="<?php echo $base_url; ?>page=<?php echo $page - 1; ?>" class="pagination__link" aria-label="صفحه قبل">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++):
                        if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <a href="<?php echo $base_url; ?>page=<?php echo $i; ?>"
                                class="pagination__link <?php echo $i === $page ? 'pagination__link--active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                            <span class="pagination__dots">...</span>
                        <?php endif;
                    endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="<?php echo $base_url; ?>page=<?php echo $page + 1; ?>" class="pagination__link" aria-label="صفحه بعد">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        <line x1="8" y1="11" x2="14" y2="11"/>
                    </svg>
                </div>
                <h3>غذایی یافت نشد</h3>
                <p>با فیلترهای انتخاب شده، هیچ غذایی پیدا نکردیم. فیلترها را تغییر دهید.</p>
                <a href="menu.php" class="btn btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    حذف فیلترها
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/menu.js"></script>

<?php require_once '../includes/footer.php'; ?>