<?php
$pageTitle = 'ویرایش دسته‌بندی';
require_once '../includes/header_admin.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setErrorMessage('دسته‌بندی یافت نشد.');
    redirect(BASE_URL . 'admin/categories.php');
}

$cat_result = mysqli_query($conn, "SELECT * FROM categories WHERE id = $id");
if (mysqli_num_rows($cat_result) === 0) {
    setErrorMessage('دسته‌بندی یافت نشد.');
    redirect(BASE_URL . 'admin/categories.php');
}
$category = mysqli_fetch_assoc($cat_result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);

    $errors = [];

    if (empty($name)) $errors[] = 'نام دسته‌بندی الزامی است.';

    $check = mysqli_query($conn, "SELECT id FROM categories WHERE name = '$name' AND id != $id");
    if (mysqli_num_rows($check) > 0) {
        $errors[] = 'این نام قبلاً استفاده شده است.';
    }

    if (empty($errors)) {
        $image = $_POST['current_image'] ?? $category['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_result = uploadFile($_FILES['image'], UPLOAD_DIR, ['jpg', 'jpeg', 'png', 'webp']);
            if ($upload_result) {
                if (!empty($category['image']) && file_exists(UPLOAD_DIR . $category['image'])) {
                    unlink(UPLOAD_DIR . $category['image']);
                }
                $image = $upload_result;
            }
        }

        $sql = "UPDATE categories SET name='$name', description='$description', image='$image' WHERE id=$id";

        if (mysqli_query($conn, $sql)) {
            setSuccessMessage('دسته‌بندی با موفقیت بروزرسانی شد.');
            redirect(BASE_URL . 'admin/categories.php');
        } else {
            setErrorMessage('خطا در ذخیره: ' . mysqli_error($conn));
        }
    } else {
        foreach ($errors as $error) setErrorMessage($error);
    }
}

$current_image = $category['image'];
$default_category_image = BASE_URL . 'assets/images/foods/default-food.jpg';
$has_image = !empty($current_image) && file_exists(UPLOAD_DIR . $current_image);
?>

<link rel="stylesheet" href="../assets/css/admin_category-edit.css">

<a href="categories.php" class="back-link">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
        <polyline points="15 18 9 12 15 6" />
    </svg>
    بازگشت به لیست دسته‌بندی‌ها
</a>

<div class="form-card">
    <div class="form-card-header">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#1a73e8;stroke-width:2;">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
        <h3>ویرایش: <?php echo htmlspecialchars($category['name']); ?></h3>
    </div>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-card-body">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($category['image']); ?>">

            <div class="form-main-row">
                <div class="form-fields-col">
                    <div class="form-group">
                        <label class="form-label">نام دسته‌بندی <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control"
                            value="<?php echo htmlspecialchars($category['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">توضیحات</label>
                        <textarea name="description" class="form-control"
                            placeholder="توضیحات کوتاه..."><?php echo htmlspecialchars($category['description']); ?></textarea>
                    </div>
                </div>
                <div class="form-image-col">
                    <div class="form-group">
                        <label class="form-label">تصویر</label>
                        <div class="image-upload-box <?php echo $has_image ? 'has-image' : ''; ?>" 
                             onclick="document.getElementById('imageInput').click()">
                            <?php if ($has_image): ?>
                                <img id="previewImage" src="<?php echo UPLOAD_URL . $current_image; ?>" 
                                     alt="<?php echo htmlspecialchars($category['name']); ?>">
                                <div class="image-overlay">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                    تغییر تصویر
                                </div>
                                <div class="image-upload-placeholder" id="placeholder" style="display:none;">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:1.5;">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <path d="M21 15l-5-5L5 21" />
                                    </svg>
                                    <span>برای آپلود کلیک کنید</span>
                                    <span class="upload-hint">JPG, PNG, WebP</span>
                                </div>
                            <?php else: ?>
                                <div class="image-upload-placeholder" id="placeholder">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:1.5;">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <path d="M21 15l-5-5L5 21" />
                                    </svg>
                                    <span>برای آپلود کلیک کنید</span>
                                    <span class="upload-hint">JPG, PNG, WebP</span>
                                </div>
                                <img id="previewImage" src="<?php echo $default_category_image; ?>" 
                                     alt="<?php echo htmlspecialchars($category['name']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="image-file-input">
                            <input type="file" name="image" id="imageInput" accept="image/*" onchange="previewFile()">
                        </div>
                        <span class="current-image-info">
                            تصویر فعلی: 
                            <?php if ($has_image): ?>
                                <?php echo htmlspecialchars($current_image); ?>
                            <?php else: ?>
                                default-food.jpg (پیش‌فرض)
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <a href="categories.php" class="btn btn-light">انصراف</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2;">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
                بروزرسانی
            </button>
        </div>
    </form>
</div>

<script src="../assets/js/admin_category-edit.js"></script>

<?php require_once '../includes/footer_admin.php'; ?>