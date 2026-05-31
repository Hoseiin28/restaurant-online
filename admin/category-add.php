<?php
$pageTitle = 'افزودن دسته‌بندی';
require_once '../includes/header_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $image = '';
    
    $errors = [];
    
    if (empty($name)) $errors[] = 'نام دسته‌بندی الزامی است.';
    
    $check = mysqli_query($conn, "SELECT id FROM categories WHERE name = '$name'");
    if (mysqli_num_rows($check) > 0) {
        $errors[] = 'این نام قبلاً استفاده شده است.';
    }
    
    if (empty($errors)) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_result = uploadFile($_FILES['image'], UPLOAD_DIR, ['jpg', 'jpeg', 'png', 'webp']);
            if ($upload_result) {
                $image = $upload_result;
            }
        }
        
        $sql = "INSERT INTO categories (name, description, image) VALUES ('$name', '$description', '$image')";
        
        if (mysqli_query($conn, $sql)) {
            setSuccessMessage('دسته‌بندی با موفقیت اضافه شد.');
            redirect(BASE_URL . 'admin/categories.php');
        } else {
            setErrorMessage('خطا در ذخیره: ' . mysqli_error($conn));
        }
    } else {
        foreach ($errors as $error) setErrorMessage($error);
    }
}
?>

<link rel="stylesheet" href="../assets/css/admin_category-add.css">

<a href="categories.php" class="back-link">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
        <polyline points="15 18 9 12 15 6"/>
    </svg>
    بازگشت به لیست دسته‌بندی‌ها
</a>

<div class="form-card">
    <div class="form-card-header">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#1a73e8;stroke-width:2;">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        <h3>افزودن دسته‌بندی جدید</h3>
    </div>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-card-body">
            <div class="form-main-row">
                <div class="form-fields-col">
                    <div class="form-group">
                        <label class="form-label">نام دسته‌بندی <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="مثال: غذاهای ایرانی" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">توضیحات</label>
                        <textarea name="description" class="form-control" 
                                  placeholder="توضیحات کوتاه درباره این دسته‌بندی..."></textarea>
                    </div>
                </div>
                <div class="form-image-col">
                    <div class="form-group">
                        <label class="form-label">تصویر</label>
                        <div class="image-upload-box" onclick="document.getElementById('imageInput').click()">
                            <div class="image-upload-placeholder" id="placeholder">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:1.5;">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <path d="M21 15l-5-5L5 21"/>
                                </svg>
                                <span>برای آپلود کلیک کنید</span>
                                <span class="upload-hint">JPG, PNG, WebP</span>
                            </div>
                            <img id="previewImage" src="" alt="" style="display:none;">
                        </div>
                        <div class="image-file-input">
                            <input type="file" name="image" id="imageInput" accept="image/*" onchange="previewFile()">
                        </div>
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
                ذخیره
            </button>
        </div>
    </form>
</div>

<script src="../assets/js/admin_category-add.js"></script>

<?php require_once '../includes/footer_admin.php'; ?>