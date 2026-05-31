<?php

$page_title = 'ویرایش پروفایل';
require_once '../includes/header.php';

checkAccess('user');

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

$errors = [];
$success_message = '';
$active_tab = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'] ?? 'info';
    $active_tab = $form_type;

    if ($form_type === 'info') {
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);

        if (empty($full_name) || mb_strlen($full_name) < 3) {
            $errors[] = 'نام و نام خانوادگی معتبر وارد کنید (حداقل ۳ کاراکتر).';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'ایمیل معتبر وارد کنید.';
        }

        $email_check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $user_id");
        if (mysqli_num_rows($email_check) > 0) {
            $errors[] = 'این ایمیل قبلاً توسط کاربر دیگری ثبت شده است.';
        }

        if (!empty($phone) && !preg_match('/^09[0-9]{9}$/', $phone)) {
            $errors[] = 'شماره موبایل معتبر وارد کنید (مثال: ۰۹۱۲۳۴۵۶۷۸۹).';
        }

        if (!empty($phone)) {
            $phone_check = mysqli_query($conn, "SELECT id FROM users WHERE phone = '$phone' AND id != $user_id");
            if (mysqli_num_rows($phone_check) > 0) {
                $errors[] = 'این شماره موبایل قبلاً ثبت شده است.';
            }
        }

        if (empty($errors)) {
            $update_sql = "UPDATE users SET full_name = '$full_name', email = '$email', phone = '$phone' WHERE id = $user_id";

            if (mysqli_query($conn, $update_sql)) {
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $success_message = 'اطلاعات شخصی با موفقیت بروزرسانی شد.';
                $active_tab = 'info';
                $result = mysqli_query($conn, $sql);
                $user = mysqli_fetch_assoc($result);
            } else {
                $errors[] = 'خطا در بروزرسانی اطلاعات.';
            }
        }
    } elseif ($form_type === 'password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password)) {
            $errors[] = 'رمز عبور فعلی الزامی است.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = 'رمز عبور فعلی اشتباه است.';
        }

        if (strlen($new_password) < 6) {
            $errors[] = 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.';
        }

        if ($new_password !== $confirm_password) {
            $errors[] = 'رمز عبور جدید و تکرار آن مطابقت ندارند.';
        }

        if (!empty($current_password) && $current_password === $new_password) {
            $errors[] = 'رمز عبور جدید نمی‌تواند با رمز فعلی یکسان باشد.';
        }

        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            if (mysqli_query($conn, "UPDATE users SET password = '$hashed_password' WHERE id = $user_id")) {
                $success_message = 'رمز عبور با موفقیت تغییر کرد.';
                $active_tab = 'info';
            } else {
                $errors[] = 'خطا در تغییر رمز عبور.';
            }
        }
    }
}

$user_stats = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT 
        COUNT(DISTINCT o.id) as total_orders,
        COUNT(DISTINCT r.id) as total_reviews,
        COALESCE(AVG(r.rating), 0) as avg_rating
     FROM users u 
     LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'
     LEFT JOIN reviews r ON u.id = r.user_id 
     WHERE u.id = $user_id"
));
?>

<link rel="stylesheet" href="../assets/css/user_profile.css">


<div class="profile-page">
    <div class="profile-wrapper">

        <aside class="profile-sidebar">
            <div class="profile-user-card">
                <div class="profile-avatar">
                    <?php echo mb_substr($user['full_name'], 0, 1, 'UTF-8'); ?>
                    <span class="profile-avatar__dot"></span>
                </div>
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <span class="profile-user-card__email"><?php echo htmlspecialchars($user['email']); ?></span>
                <span class="profile-user-card__join">عضو از <?php echo toJalali($user['created_at'], 'Y/m/d'); ?></span>
            </div>

            <div class="profile-stats">
                <div class="profile-stat-item">
                    <span class="profile-stat-item__value"><?php echo number_format($user_stats['total_orders']); ?></span>
                    <span class="profile-stat-item__label">سفارش</span>
                </div>
                <div class="profile-stat-item">
                    <span class="profile-stat-item__value"><?php echo number_format($user_stats['total_reviews']); ?></span>
                    <span class="profile-stat-item__label">نظر</span>
                </div>
                <div class="profile-stat-item">
                    <span class="profile-stat-item__value"><?php echo number_format($user_stats['avg_rating'], 1); ?></span>
                    <span class="profile-stat-item__label">میانگین امتیاز</span>
                </div>
            </div>

            <nav class="profile-nav">
                <button class="profile-nav__btn <?php echo $active_tab === 'info' ? 'profile-nav__btn--active' : ''; ?>"
                    id="navBtnInfo" onclick="switchTab('info')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <span class="nav-text">اطلاعات شخصی</span>
                </button>
                <button class="profile-nav__btn <?php echo $active_tab === 'password' ? 'profile-nav__btn--active' : ''; ?>"
                    id="navBtnPassword" onclick="switchTab('password')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    <span class="nav-text">تغییر رمز عبور</span>
                </button>
            </nav>
        </aside>

        <div class="profile-content">

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" id="successAlert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="15" y1="9" x2="9" y2="15" />
                                    <line x1="9" y1="9" x2="15" y2="15" />
                                </svg>
                                <?php echo $error; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="profile-card" id="tabInfo" <?php echo $active_tab !== 'info' ? 'style="display:none;"' : ''; ?>>
                <div class="profile-card__header">
                    <h3>اطلاعات شخصی</h3>
                    <p>اطلاعات حساب کاربری خود را ویرایش کنید</p>
                </div>
                <div class="profile-card__body">
                    <form method="POST" action="" novalidate>
                        <input type="hidden" name="form_type" value="info">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="fullName">
                                    <span class="required">*</span> نام و نام خانوادگی
                                </label>
                                <input type="text" name="full_name" id="fullName" class="form-input"
                                    value="<?php echo htmlspecialchars($user['full_name']); ?>" required maxlength="100">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="phone">شماره موبایل</label>
                                <input type="tel" name="phone" id="phone" class="form-input"
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                    placeholder="۰۹۱۲۳۴۵۶۷۸۹" pattern="09[0-9]{9}" maxlength="11" dir="ltr">
                                <span class="form-hint">اختیاری | برای اطلاع‌رسانی سفارش‌ها</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">
                                <span class="required">*</span> ایمیل
                            </label>
                            <input type="email" name="email" id="email" class="form-input"
                                value="<?php echo htmlspecialchars($user['email']); ?>" required dir="ltr">
                            <span class="form-hint">این ایمیل برای ورود به حساب استفاده می‌شود</span>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                ذخیره تغییرات
                            </button>
                            <a href="dashboard.php" class="btn-cancel">بازگشت به پیشخوان</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="profile-card" id="tabPassword" <?php echo $active_tab !== 'password' ? 'style="display:none;"' : ''; ?>>
                <div class="profile-card__header">
                    <h3>تغییر رمز عبور</h3>
                    <p>برای امنیت بیشتر، رمز عبور قوی انتخاب کنید</p>
                </div>
                <div class="profile-card__body">
                    <form method="POST" action="" novalidate>
                        <input type="hidden" name="form_type" value="password">

                        <div class="form-group">
                            <label class="form-label" for="currentPassword">
                                <span class="required">*</span> رمز عبور فعلی
                            </label>
                            <div class="password-wrapper">
                                <input type="password" name="current_password" id="currentPassword"
                                    class="form-input" placeholder="رمز عبور فعلی" required>
                                <button type="button" class="toggle-pass" id="toggleCurrent" aria-label="نمایش رمز">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                        <line x1="1" y1="1" x2="23" y2="23" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="newPassword">
                                    <span class="required">*</span> رمز عبور جدید
                                </label>
                                <input type="password" name="new_password" id="newPassword"
                                    class="form-input" placeholder="حداقل ۶ کاراکتر" minlength="6" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="confirmPassword">
                                    <span class="required">*</span> تکرار رمز جدید
                                </label>
                                <input type="password" name="confirm_password" id="confirmPassword"
                                    class="form-input" placeholder="تکرار رمز عبور" minlength="6" required>
                            </div>
                        </div>

                        <div class="pass-strength">
                            <div class="pass-strength__bar">
                                <div class="pass-strength__fill" id="strengthFill"></div>
                            </div>
                            <span class="pass-strength__text" id="strengthText">قدرت رمز عبور</span>
                        </div>

                        <div class="security-tips">
                            <div class="security-tips__title">نکات امنیتی برای رمز عبور قوی:</div>
                            <ul class="security-tips__list">
                                <li>حداقل ۶ کاراکتر</li>
                                <li>ترکیب حروف بزرگ و کوچک</li>
                                <li>استفاده از اعداد</li>
                                <li>استفاده از علائم خاص (#@!%)</li>
                            </ul>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                تغییر رمز عبور
                            </button>
                            <a href="dashboard.php" class="btn-cancel">بازگشت به پیشخوان</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="../assets/js/user_profile.js"></script>

<?php require_once '../includes/footer.php'; ?>