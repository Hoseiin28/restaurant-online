<?php
$pageTitle = 'افزودن غذا';
require_once '../includes/header_admin.php';

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
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_result = uploadFile($_FILES['image'], UPLOAD_DIR, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            if ($upload_result) {
                $image = $upload_result;
            }
        }

        $sql = "INSERT INTO foods (name, description, price, category_id, image, is_available) 
                VALUES ('$name', '$description', $price, $category_id, '$image', $is_available)";

        if (mysqli_query($conn, $sql)) {
            setSuccessMessage('غذا با موفقیت اضافه شد.');
            redirect(BASE_URL . 'admin/foods.php');
        } else {
            setErrorMessage('خطا در ذخیره: ' . mysqli_error($conn));
        }
    } else {
        foreach ($errors as $error) setErrorMessage($error);
    }
}
?>

<link rel="stylesheet" href="../assets/css/admin_food-add.css">

<a href="foods.php" class="back-link">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
        <polyline points="15 18 9 12 15 6" />
    </svg>
    بازگشت به لیست غذاها
</a>

<div class="form-card">
    <div class="form-card-header">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#1a73e8;stroke-width:2;">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        <h3>افزودن غذای جدید</h3>
    </div>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-card-body">
            <div class="form-main-row">
                <div class="form-fields-col">
                    <div class="form-inline-row">
                        <div class="form-group">
                            <label class="form-label">
                                نام غذا <span class="required">*</span>
                            </label>
                            <input type="text" name="name" class="form-control"
                                placeholder="مثال: قرمه سبزی" required
                                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                قیمت (تومان) <span class="required">*</span>
                            </label>
                            <input type="number" name="price" class="form-control"
                                placeholder="مثال: ۹۵,۰۰۰" min="1000" required
                                value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            دسته‌بندی <span class="required">*</span>
                        </label>
                        <select name="category_id" class="form-control" required>
                            <option value="">انتخاب دسته‌بندی...</option>
                            <?php foreach ($categories_all as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo $cat['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">توضیحات</label>
                        <textarea name="description" class="form-control"
                            placeholder="مواد تشکیل‌دهنده، نحوه سرو، یا هر توضیح دیگری..."
                            rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="is_available" id="available" checked>
                        <label for="available">این غذا در منو نمایش داده شود</label>
                    </div>
                </div>

                <div class="form-image-col">
                    <div class="image-upload-area">
                        <label class="form-label">تصویر غذا</label>
                        <div class="image-upload-box" id="imageUploadBox" onclick="document.getElementById('imageInput').click()">
                            <div class="image-upload-placeholder" id="uploadPlaceholder">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:1.5;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                    <path d="M21 15l-5-5L5 21" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                </svg>
                                <span>برای آپلود تصویر کلیک کنید</span>
                                <span class="upload-hint">JPG, PNG, WebP (حداکثر ۲ مگابایت)</span>
                            </div>
                            <img id="previewImage" src="" alt="پیش‌نمایش" style="display:none;">
                        </div>
                        <div class="image-file-input">
                            <input type="file" name="image" id="imageInput"
                                accept="image/*" onchange="previewFile()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="foods.php" class="btn btn-light">انصراف</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2;">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
                ذخیره غذا
            </button>
        </div>
    </form>
</div>

<script src="../assets/js/admin_food-add.js"></script>

<?php require_once '../includes/footer_admin.php'; ?>