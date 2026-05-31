<?php

$page_title = 'ثبت‌نام';
require_once '../includes/header.php';

if (isLoggedIn()) {
    redirect(BASE_URL . 'index.php');
}

$errors = [];
$formData = ['full_name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = sanitize($_POST['full_name']);
    $formData['email'] = sanitize($_POST['email']);
    $formData['phone'] = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($formData['full_name']) || mb_strlen($formData['full_name']) < 3) {
        $errors[] = 'نام و نام خانوادگی معتبر وارد کنید (حداقل ۳ کاراکتر).';
    }

    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'ایمیل معتبر وارد کنید.';
    }

    if (empty($formData['phone']) || !preg_match('/^09[0-9]{9}$/', $formData['phone'])) {
        $errors[] = 'شماره موبایل معتبر وارد کنید (مثال: ۰۹۱۲۳۴۵۶۷۸۹).';
    }

    if (strlen($password) < 6) {
        $errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'رمز عبور و تکرار آن مطابقت ندارند.';
    }

    if (!isFieldUnique('email', $formData['email'])) {
        $errors[] = 'این ایمیل قبلاً ثبت شده است.';
    }

    if (!isFieldUnique('phone', $formData['phone'])) {
        $errors[] = 'این شماره موبایل قبلاً ثبت شده است.';
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (full_name, email, phone, password) 
                VALUES ('{$formData['full_name']}', '{$formData['email']}', '{$formData['phone']}', '$hashed_password')";

        if (mysqli_query($conn, $sql)) {
            setSuccessMessage('ثبت‌نام با موفقیت انجام شد. اکنون می‌توانید وارد شوید.');
            redirect(BASE_URL . 'authentication/login.php');
        } else {
            $errors[] = 'خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.';
        }
    }
}

$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='user'"))['count'] ?? 0;
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'] ?? 0;
?>
<link rel="stylesheet" href="../assets/css/signup.css">

<div class="auth-page">
    <div class="auth-wrapper">

        <div class="auth-visual">
            <div class="auth-visual__inner">
                <div class="auth-visual__brand">
                    <svg viewBox="0 0 44 44" fill="none">
                        <circle cx="22" cy="22" r="21" stroke="currentColor" stroke-width="2"/>
                        <path d="M13 22C13 22 17.5 13 22 13C26.5 13 31 22 31 22C31 22 26.5 31 22 31C17.5 31 13 22 13 22Z" stroke="currentColor" stroke-width="1.5"/>
                        <circle cx="22" cy="22" r="3" fill="currentColor"/>
                    </svg>
                    <div class="auth-visual__brand-name">
                       رستوران آنلاین
                        <span class="auth-visual__brand-sub"> طعم واقعی غذاهای خانگی</span>
                    </div>
                </div>

                <h2>عضو جدید  رستوران آنلاین شوید</h2>
                <p class="auth-visual__desc">
                    با ایجاد حساب کاربری، از سفارش سریع، پیگیری لحظه‌ای و تخفیف‌های ویژه اعضا بهره‌مند شوید.
                </p>

                <div class="auth-perks">
                    <div class="auth-perk">
                        <div class="auth-perk__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <span>سفارش سریع و بدون پیچیدگی</span>
                    </div>
                    <div class="auth-perk">
                        <div class="auth-perk__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2z"/>
                            </svg>
                        </div>
                        <span>امتیازدهی و ثبت نظرات</span>
                    </div>
                    <div class="auth-perk">
                        <div class="auth-perk__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="3" width="15" height="13"/>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                        </div>
                        <span>تحویل سریع درب منزل</span>
                    </div>
                    <div class="auth-perk">
                        <div class="auth-perk__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 6v6l4 2"/>
                            </svg>
                        </div>
                        <span>پیگیری لحظه‌ای سفارش</span>
                    </div>
                </div>

                <div class="auth-stats">
                    <div>
                        <span class="auth-stat__number"><?php echo number_format($totalUsers); ?>+</span>
                        <span class="auth-stat__label">کاربر فعال</span>
                    </div>
                    <div>
                        <span class="auth-stat__number"><?php echo number_format($totalOrders); ?>+</span>
                        <span class="auth-stat__label">سفارش موفق</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-form-side">
            <div class="auth-form-side__header">
                <h1>ایجاد حساب کاربری</h1>
                <p>کمتر از ۲ دقیقه، کاملاً رایگان</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                </svg>
                                <?php echo $error; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate autocomplete="off">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="fullName">
                            <span class="required">*</span> نام کامل
                        </label>
                        <input type="text" name="full_name" id="fullName" class="form-input"
                            placeholder="علی محمدی" maxlength="100"
                            value="<?php echo htmlspecialchars($formData['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">
                            <span class="required">*</span> شماره موبایل
                        </label>
                        <input type="tel" name="phone" id="phone" class="form-input"
                            placeholder="۰۹۱۲۳۴۵۶۷۸۹" pattern="09[0-9]{9}" maxlength="11"
                            value="<?php echo htmlspecialchars($formData['phone']); ?>" required
                            dir="ltr">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">
                        <span class="required">*</span> ایمیل
                    </label>
                    <input type="email" name="email" id="email" class="form-input"
                        placeholder="example@email.com"
                        value="<?php echo htmlspecialchars($formData['email']); ?>" required
                        dir="ltr">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="signupPassword">
                            <span class="required">*</span> رمز عبور
                        </label>
                        <input type="password" name="password" id="signupPassword" class="form-input"
                            placeholder="حداقل ۶ کاراکتر" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">
                            <span class="required">*</span> تکرار رمز عبور
                        </label>
                        <input type="password" name="confirm_password" id="confirmPassword" class="form-input"
                            placeholder="تکرار رمز عبور" minlength="6" required>
                    </div>
                </div>

                <div class="password-strength">
                    <div class="password-strength__bar">
                        <div class="password-strength__fill" id="strengthFill"></div>
                    </div>
                    <span class="password-strength__text" id="strengthText">قدرت رمز عبور</span>
                </div>

                <label class="form-check">
                    <input type="checkbox" required>
                    <span>با ایجاد حساب، <a href="#">قوانین و مقررات</a> و <a href="#">حریم خصوصی</a> رستوران آنلاین را می‌پذیرم.</span>
                </label>

                <button type="submit" class="btn-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                    ایجاد حساب کاربری
                </button>
            </form>

            <div class="auth-switch">
                قبلاً ثبت‌نام کرده‌اید؟
                <a href="login.php">وارد حساب خود شوید</a>
            </div>
        </div>

    </div>
</div>

<script src="../assets/js/signup.js"></script>

<?php require_once '../includes/footer.php'; ?>