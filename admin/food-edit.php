<?php
$pageTitle = 'ویرایش غذا';
require_once '../includes/header_admin.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setErrorMessage('غذا یافت نشد.');
    redirect(BASE_URL . 'admin/foods.php');
}

$food_result = mysqli_query($conn, "SELECT * FROM foods WHERE id = $id");
if (mysqli_num_rows($food_result) === 0) {
    setErrorMessage('غذا یافت نشد.');
    redirect(BASE_URL . 'admin/foods.php');
}
$food = mysqli_fetch_assoc($food_result);

$categories_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$categories_all = [];
while ($cat = mysqli_fetch_assoc($categories_result)) {
    $categories_all[] = $cat;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    $errors = [];
    
    if (empty($name)) $errors[] = 'نام غذا الزامی است.';
    if ($price <= 0) $errors[] = 'قیمت باید بیشتر از صفر باشد.';
    if ($category_id <= 0) $errors[] = 'دسته‌بندی را انتخاب کنید.';
    
    if (empty($errors)) {
        $image = $_POST['current_image'] ?? $food['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_result = uploadFile($_FILES['image'], UPLOAD_DIR, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            if ($upload_result) {
                if (!empty($food['image']) && file_exists(UPLOAD_DIR . $food['image'])) {
                    unlink(UPLOAD_DIR . $food['image']);
                }
                $image = $upload_result;
            }
        }
        
        $sql = "UPDATE foods SET 
                name = '$name', 
                description = '$description', 
                price = $price, 
                category_id = $category_id, 
                is_available = $is_available,
                image = '$image'
                WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            setSuccessMessage('غذا با موفقیت بروزرسانی شد.');
            redirect(BASE_URL . 'admin/foods.php');
        } else {
            setErrorMessage('خطا در ذخیره: ' . mysqli_error($conn));
        }
    } else {
        foreach ($errors as $error) setErrorMessage($error);
    }
}

// تنظیم مسیر تصویر پیش‌فرض و تصویر فعلی
$current_image = $food['image'];
$default_food_image = BASE_URL . 'assets/images/foods/default-food.jpg';
$has_image = !empty($current_image) && file_exists(UPLOAD_DIR . $current_image);
?>

<link rel="stylesheet" href="../assets/css/admin_food-edit.css">

<a href="foods.php" class="back-link">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
        <polyline points="15 18 9 12 15 6"/>
    </svg>
    بازگشت به لیست غذاها
</a>

<div class="form-card">
    <div class="form-card-header">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#1a73e8;stroke-width:2;">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
        </svg>
        <h3>ویرایش: <?php echo $food['name']; ?></h3>
        <span class="food-id">کد #<?php echo $food['id']; ?></span>
    </div>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-card-body">
            <input type="hidden" name="current_image" value="<?php echo $food['image']; ?>">
            
            <div class="form-main-row">
                <div class="form-fields-col">
                    <div class="food-meta-info">
                        <div class="meta-item">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            تاریخ ثبت: <strong><?php echo toJalali($food['created_at']); ?></strong>
                        </div>
                        <div class="meta-item">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            وضعیت فعلی: 
                            <strong style="color:<?php echo $food['is_available'] ? '#059669' : '#dc2626'; ?>;">
                                <?php echo $food['is_available'] ? 'موجود' : 'ناموجود'; ?>
                            </strong>
                        </div>
                    </div>
                    
                    <div class="form-inline-row">
                        <div class="form-group">
                            <label class="form-label">
                                نام غذا <span class="required">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($food['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                قیمت (تومان) <span class="required">*</span>
                            </label>
                            <input type="number" name="price" class="form-control" 
                                   value="<?php echo $food['price']; ?>" min="1000" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            دسته‌بندی <span class="required">*</span>
                        </label>
                        <select name="category_id" class="form-control" required>
                            <?php foreach ($categories_all as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php echo $food['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">توضیحات</label>
                        <textarea name="description" class="form-control" 
                                  placeholder="مواد تشکیل‌دهنده، نحوه سرو، یا هر توضیح دیگری..."
                                  rows="3"><?php echo htmlspecialchars($food['description']); ?></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="is_available" id="available" 
                               <?php echo $food['is_available'] ? 'checked' : ''; ?>>
                        <label for="available">این غذا در منو نمایش داده شود</label>
                    </div>
                </div>
                
                <div class="form-image-col">
                    <div class="image-upload-area">
                        <label class="form-label">تصویر غذا</label>
                        <div class="image-upload-box <?php echo $has_image ? 'has-image' : ''; ?>" 
                             id="imageUploadBox" 
                             onclick="document.getElementById('imageInput').click()">
                            <?php if ($has_image): ?>
                                <img id="previewImage" src="<?php echo UPLOAD_URL . $current_image; ?>" 
                                     alt="<?php echo htmlspecialchars($food['name']); ?>">
                                <div class="image-overlay">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    تغییر تصویر
                                </div>
                                <div class="image-upload-placeholder" id="uploadPlaceholder" style="display:none;">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:1.5;">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <path d="M21 15l-5-5L5 21"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                    </svg>
                                    <span>برای آپلود کلیک کنید</span>
                                    <span class="upload-hint">JPG, PNG, WebP</span>
                                </div>
                            <?php else: ?>
                                <div class="image-upload-placeholder" id="uploadPlaceholder">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:1.5;">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <path d="M21 15l-5-5L5 21"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                    </svg>
                                    <span>برای آپلود تصویر کلیک کنید</span>
                                    <span class="upload-hint">JPG, PNG, WebP (حداکثر ۲ مگابایت)</span>
                                </div>
                                <img id="previewImage" src="<?php echo $default_food_image; ?>" 
                                     alt="<?php echo htmlspecialchars($food['name']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="image-file-input">
                            <input type="file" name="image" id="imageInput" 
                                   accept="image/*" onchange="previewFile()">
                        </div>
                        <div class="current-image-name">
                            تصویر فعلی: 
                            <?php if ($has_image): ?>
                                <?php echo htmlspecialchars($current_image); ?>
                            <?php else: ?>
                                default-food.jpg (پیش‌فرض)
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <a href="foods.php" class="btn btn-light">انصراف</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2;">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                بروزرسانی غذا
            </button>
        </div>
    </form>
</div>

<script src="../assets/js/admin_food-edit.js"></script>

<?php require_once '../includes/footer_admin.php'; ?>