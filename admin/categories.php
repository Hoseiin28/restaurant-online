<?php
$pageTitle = 'مدیریت دسته‌بندی‌ها';
require_once '../includes/header_admin.php';

if (isset($_GET['delete']) && isset($_GET['token']) && $_GET['token'] === $_SESSION['csrf_token']) {
    $id = (int)$_GET['delete'];
    
    $check = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as count FROM foods WHERE category_id = $id"
    ));
    
    if ($check['count'] > 0) {
        setErrorMessage('این دسته‌بندی ' . $check['count'] . ' غذا دارد و قابل حذف نیست.');
    } else {
        $img_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM categories WHERE id = $id"));
        if (mysqli_query($conn, "DELETE FROM categories WHERE id = $id")) {
            if ($img_result['image'] && file_exists(UPLOAD_DIR . $img_result['image'])) {
                unlink(UPLOAD_DIR . $img_result['image']);
            }
            setSuccessMessage('دسته‌بندی با موفقیت حذف شد.');
        } else {
            setErrorMessage('خطا در حذف دسته‌بندی.');
        }
    }
    redirect(BASE_URL . 'admin/categories.php');
}

$categories = mysqli_query($conn, 
    "SELECT c.*, 
            COUNT(f.id) as food_count,
            SUM(CASE WHEN f.is_available = 1 THEN 1 ELSE 0 END) as available_count,
            MIN(f.price) as min_price,
            MAX(f.price) as max_price
     FROM categories c 
     LEFT JOIN foods f ON c.id = f.category_id 
     GROUP BY c.id 
     ORDER BY c.id DESC"
);

$total_categories = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM categories"));
$total_foods_in_cats = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM foods WHERE category_id IS NOT NULL"
))['count'];

generateCSRFToken();

$default_category_image = BASE_URL . 'assets/images/foods/default-food.jpg';

$card_colors = [
    ['bg' => '#eff6ff', 'border' => '#3b82f6', 'icon' => '#1d4ed8'],
    ['bg' => '#f0fdf4', 'border' => '#22c55e', 'icon' => '#15803d'],
    ['bg' => '#fef3c7', 'border' => '#f59e0b', 'icon' => '#b45309'],
    ['bg' => '#fce7f3', 'border' => '#ec4899', 'icon' => '#be185d'],
    ['bg' => '#ede9fe', 'border' => '#8b5cf6', 'icon' => '#5b21b6'],
    ['bg' => '#fef2f2', 'border' => '#ef4444', 'icon' => '#b91c1c'],
    ['bg' => '#ecfeff', 'border' => '#06b6d4', 'icon' => '#0e7490'],
    ['bg' => '#f0fdfa', 'border' => '#14b8a6', 'icon' => '#0f766e'],
];
?>

<link rel="stylesheet" href="../assets/css/admin_categories.css">

<div class="page-toolbar">
    <h2>مدیریت دسته‌بندی‌ها</h2>
    <a href="category-add.php" class="btn btn-primary">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        افزودن دسته‌بندی
    </a>
</div>

<div class="stats-mini-row">
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#eff6ff;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#3b82f6;stroke-width:2;">
                <rect x="3" y="3" width="8" height="8" rx="1"/>
                <rect x="13" y="3" width="8" height="8" rx="1"/>
                <rect x="3" y="13" width="8" height="8" rx="1"/>
                <rect x="13" y="13" width="8" height="8" rx="1"/>
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo $total_categories; ?></strong>
            <span>کل دسته‌بندی‌ها</span>
        </div>
    </div>
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#f0fdf4;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#22c55e;stroke-width:2;">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo $total_foods_in_cats; ?></strong>
            <span>غذای دسته‌بندی شده</span>
        </div>
    </div>
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#fef3c7;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#f59e0b;stroke-width:2;">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo $total_categories > 0 ? 'فعال' : '---'; ?></strong>
            <span>وضعیت</span>
        </div>
    </div>
</div>

<div class="categories-grid">
    <?php if (mysqli_num_rows($categories) > 0): 
        $color_index = 0;
        while ($cat = mysqli_fetch_assoc($categories)):
            $cat_image = $cat['image'];
            $has_cat_image = !empty($cat_image) && file_exists(UPLOAD_DIR . $cat_image);
            
            $color = $card_colors[$color_index % count($card_colors)];
            $color_index++;
        ?>
            <div class="cat-card">
                <div class="cat-card-header" style="background:<?php echo $color['border']; ?>"></div>
                <div class="cat-card-body">
                    <div class="cat-card-top">
                        <div class="cat-icon-box <?php echo $has_cat_image ? 'has-image' : ''; ?>" 
                             style="background:<?php echo $color['bg']; ?>;">
                            <?php if ($has_cat_image): ?>
                                <img src="<?php echo UPLOAD_URL . $cat_image; ?>" 
                                     alt="<?php echo htmlspecialchars($cat['name']); ?>" loading="lazy">
                            <?php else: ?>
                                <img src="<?php echo $default_category_image; ?>" 
                                     alt="<?php echo htmlspecialchars($cat['name']); ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="cat-info">
                            <h3><?php echo $cat['name']; ?></h3>
                            <?php if ($cat['description']): ?>
                                <p><?php echo $cat['description']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="cat-stats">
                        <div class="cat-stat-item">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            </svg>
                            <strong><?php echo $cat['food_count']; ?></strong> غذا
                        </div>
                        <div class="cat-stat-item">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <strong><?php echo $cat['available_count']; ?></strong> موجود
                        </div>
                    </div>
                    
                    <?php if ($cat['min_price'] && $cat['max_price']): ?>
                        <div class="cat-price-range">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                            </svg>
                            محدوده قیمت: 
                            <strong><?php echo number_format($cat['min_price']); ?></strong> 
                            تا 
                            <strong><?php echo number_format($cat['max_price']); ?></strong> تومان
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="cat-card-actions">
                    <a href="<?php echo BASE_URL; ?>main/menu.php?category=<?php echo $cat['id']; ?>" 
                       target="_blank" class="btn btn-outline-info" title="مشاهده در سایت">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        مشاهده
                    </a>
                    <a href="category-edit.php?id=<?php echo $cat['id']; ?>" class="btn btn-outline-primary" title="ویرایش">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        ویرایش
                    </a>
                    <button class="btn btn-outline-danger" 
                            onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo addslashes($cat['name']); ?>', <?php echo $cat['food_count']; ?>)" 
                            title="حذف">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                        </svg>
                        حذف
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state-full">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#94a3b8;stroke-width:1.5;">
                <rect x="3" y="3" width="8" height="8" rx="1"/>
                <rect x="13" y="3" width="8" height="8" rx="1"/>
                <rect x="3" y="13" width="8" height="8" rx="1"/>
                <rect x="13" y="13" width="8" height="8" rx="1"/>
            </svg>
            <h3>هیچ دسته‌بندی وجود ندارد</h3>
            <p>برای سازماندهی غذاها، <a href="category-add.php" class="cat-a">اولین دسته‌بندی</a>  را ایجاد کنید</p>
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
            <p>آیا از حذف <strong id="deleteCatName"></strong> اطمینان دارید؟</p>
            <p style="font-size:12px;color:#ef4444;">این عملیات قابل بازگشت نیست.</p>
            <p id="deleteWarning" style="font-size:12px;color:#f59e0b;display:none;"></p>
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
<script src="../assets/js/admin_categories.js"></script>

<?php require_once '../includes/footer_admin.php'; ?>