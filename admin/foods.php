<?php
$pageTitle = 'مدیریت غذاها';
require_once '../includes/header_admin.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;

if (isset($_GET['delete']) && isset($_GET['token']) && $_GET['token'] === $_SESSION['csrf_token']) {
    $id = (int)$_GET['delete'];

    $img_result = mysqli_query($conn, "SELECT image FROM foods WHERE id = $id");
    $food_img = mysqli_fetch_assoc($img_result);

    if (mysqli_query($conn, "DELETE FROM foods WHERE id = $id")) {
        if ($food_img['image'] && file_exists(UPLOAD_DIR . $food_img['image'])) {
            unlink(UPLOAD_DIR . $food_img['image']);
        }
        setSuccessMessage('غذا با موفقیت حذف شد.');
    } else {
        setErrorMessage('خطا در حذف غذا.');
    }
    redirect(BASE_URL . 'admin/foods.php');
}

$where = [];
if (!empty($search)) {
    $where[] = "(f.name LIKE '%$search%' OR f.description LIKE '%$search%')";
}
if ($category_filter > 0) {
    $where[] = "f.category_id = $category_filter";
}
if ($status_filter === 'available') {
    $where[] = "f.is_available = 1";
} elseif ($status_filter === 'unavailable') {
    $where[] = "f.is_available = 0";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$order_clause = 'ORDER BY f.id DESC';
switch ($sort) {
    case 'oldest':
        $order_clause = 'ORDER BY f.id ASC';
        break;
    case 'name_asc':
        $order_clause = 'ORDER BY f.name ASC';
        break;
    case 'name_desc':
        $order_clause = 'ORDER BY f.name DESC';
        break;
    case 'price_asc':
        $order_clause = 'ORDER BY f.price ASC';
        break;
    case 'price_desc':
        $order_clause = 'ORDER BY f.price DESC';
        break;
}

$count_sql = "SELECT COUNT(*) as total FROM foods f $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$total_foods = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_foods / $per_page);
$offset = ($page - 1) * $per_page;

$foods_sql = "SELECT f.*, c.name as category_name,
              (SELECT COUNT(*) FROM order_items WHERE food_id = f.id) as order_count,
              (SELECT AVG(rating) FROM reviews WHERE food_id = f.id) as avg_rating
              FROM foods f 
              LEFT JOIN categories c ON f.category_id = c.id 
              $where_clause 
              $order_clause 
              LIMIT $offset, $per_page";
$foods_result = mysqli_query($conn, $foods_sql);

$categories_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$categories_all = [];
while ($cat = mysqli_fetch_assoc($categories_result)) {
    $categories_all[] = $cat;
}

$available_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM foods WHERE is_available = 1"))['count'];
$unavailable_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM foods WHERE is_available = 0"))['count'];

$total_orders_count = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as count FROM order_items"
))['count'];

generateCSRFToken();
?>

<link rel="stylesheet" href="../assets/css/admin_foods.css">

<div class="page-toolbar">
    <div>
        <h2>مدیریت غذاها</h2>
    </div>
    <div class="toolbar-right">
        <a href="food-add.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            افزودن غذا
        </a>
    </div>
</div>

<div class="stats-mini-row">
    <div class="stat-mini-card total">
        <div class="stat-mini-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                <path d="M12 2L2 7l10 5 10-5-10-5z" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($total_foods); ?></strong>
            <span>کل غذاها</span>
        </div>
    </div>

    <div class="stat-mini-card available">
        <div class="stat-mini-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                <polyline points="20 6 9 17 4 12" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($available_count); ?></strong>
            <span>غذاهای موجود</span>
        </div>
    </div>

    <div class="stat-mini-card unavailable">
        <div class="stat-mini-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($unavailable_count); ?></strong>
            <span>غذاهای ناموجود</span>
        </div>
    </div>
</div>

<div class="filters-card">
    <form method="GET" action="" id="filterForm">
        <div class="filters-row">
            <div class="search-wrap">
                <span class="search-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </span>
                <input type="text" name="search" placeholder="جستجوی نام یا توضیحات..."
                    value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="">همه دسته‌بندی‌ها</option>
                <?php foreach ($categories_all as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo $cat['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">همه وضعیت‌ها</option>
                <option value="available" <?php echo $status_filter === 'available' ? 'selected' : ''; ?>>
                    موجود (<?php echo $available_count; ?>)
                </option>
                <option value="unavailable" <?php echo $status_filter === 'unavailable' ? 'selected' : ''; ?>>
                    ناموجود (<?php echo $unavailable_count; ?>)
                </option>
            </select>

            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>جدیدترین</option>
                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>قدیمی‌ترین</option>
                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>نام (الفبا)</option>
                <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>نام (معکوس)</option>
                <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>قیمت (صعودی)</option>
                <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>قیمت (نزولی)</option>
            </select>

            <?php if (!empty($search) || $category_filter > 0 || $status_filter !== ''): ?>
                <a href="foods.php" class="clear-filters">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:12px;height:12px;fill:none;stroke:currentColor;stroke-width:2;">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                    حذف فیلترها
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="60">تصویر</th>
                    <th>نام غذا</th>
                    <th>دسته‌بندی</th>
                    <th>قیمت</th>
                    <th>سفارش</th>
                    <th>امتیاز</th>
                    <th>وضعیت</th>
                    <th width="120">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($foods_result) > 0): ?>
                    <?php while ($food = mysqli_fetch_assoc($foods_result)): ?>
                        <tr>
                            <td>
                                <div class="food-thumb">
                                    <?php
                                    $image_path = UPLOAD_URL . $food['image'];
                                    $default_image = BASE_URL . 'assets/images/foods/default-food.jpg';

                                    if (!empty($food['image']) && file_exists(UPLOAD_DIR . $food['image'])):
                                    ?>
                                        <img src="<?php echo $image_path; ?>"
                                            alt="<?php echo htmlspecialchars($food['name']); ?>" loading="lazy">
                                    <?php else: ?>
                                        <img src="<?php echo $default_image; ?>"
                                            alt="<?php echo htmlspecialchars($food['name']); ?>" loading="lazy">
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="food-name"><?php echo $food['name']; ?></span>
                                <?php if ($food['description']): ?>
                                    <span class="food-desc"><?php echo excerpt($food['description'], 35); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="category-badge">
                                    <?php echo $food['category_name'] ?? '---'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="price-cell">
                                    <?php echo number_format($food['price']); ?>
                                    <small> تومان</small>
                                </span>
                            </td>
                            <td>
                                <?php if ($food['order_count'] > 0): ?>
                                    <span class="badge badge-info" style="font-size:11px;">
                                        <?php echo $food['order_count']; ?> بار
                                    </span>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:12px;">---</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($food['avg_rating']): ?>
                                    <span style="color:#f59e0b;font-size:12px;font-weight:600;">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:12px;height:12px;fill:#f59e0b;vertical-align:-1px;margin-left:2px;">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                        <?php echo number_format($food['avg_rating'], 1); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:12px;">---</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="toggle-wrap">
                                    <label class="toggle-switch">
                                        <input type="checkbox"
                                            <?php echo $food['is_available'] ? 'checked' : ''; ?>
                                            onchange="toggleAvailability(<?php echo $food['id']; ?>, this)">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="status-label <?php echo $food['is_available'] ? 'available' : 'unavailable'; ?>">
                                        <?php echo $food['is_available'] ? 'موجود' : 'ناموجود'; ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="action-cell">
                                    <a href="food-edit.php?id=<?php echo $food['id']; ?>"
                                        class="btn btn-sm btn-outline-primary" title="ویرایش" style="padding:6px 11px;font-size:11px;">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2;">
                                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                        ویرایش
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteFood(<?php echo $food['id']; ?>, '<?php echo addslashes($food['name']); ?>')"
                                        title="حذف" style="padding:6px 11px;font-size:11px;">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2;">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                        </svg>
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#94a3b8;stroke-width:1.5;">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z" />
                                    <path d="M2 17l10 5 10-5" />
                                    <path d="M2 12l10 5 10-5" />
                                </svg>
                                <h4>هیچ غذایی یافت نشد</h4>
                                <p>
                                    <?php if (!empty($search) || $category_filter > 0): ?>
                                        موردی با این شرایط پیدا نشد. <a href="foods.php">نمایش همه</a>
                                    <?php else: ?>
                                        برای شروع <a href="food-add.php">اولین غذا</a> را اضافه کنید.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination-row">
            <div class="pagination-info">
                نمایش <?php echo min(($page - 1) * $per_page + 1, $total_foods); ?>
                تا <?php echo min($page * $per_page, $total_foods); ?>
                از <?php echo number_format($total_foods); ?> غذا
            </div>
            <div class="pagination">
                <?php
                $query_params = $_GET;
                unset($query_params['page']);
                $base_url = 'foods.php?' . http_build_query($query_params);
                ?>

                <?php if ($page > 1): ?>
                    <a href="<?php echo $base_url; ?>&page=<?php echo $page - 1; ?>" class="page-link">قبلی</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++):
                    if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <a href="<?php echo $base_url; ?>&page=<?php echo $i; ?>"
                            class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <span class="page-dots">...</span>
                <?php endif;
                endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="<?php echo $base_url; ?>&page=<?php echo $page + 1; ?>" class="page-link">بعدی</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal" id="deleteModal">
    <div class="modal-overlay" onclick="closeDeleteModal()"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>تأیید حذف</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>آیا از حذف <strong id="deleteFoodName"></strong> اطمینان دارید؟</p>
            <p style="font-size:12px;color:#ef4444;">این عملیات قابل بازگشت نیست.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-light" onclick="closeDeleteModal()">انصراف</button>
            <a href="#" id="deleteConfirmBtn" class="btn btn-danger">حذف</a>
        </div>
    </div>
</div>

<script>
    const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token']; ?>';
</script>
<script src="../assets/js/admin_foods.js"></script>

<?php require_once '../includes/footer_admin.php'; ?>