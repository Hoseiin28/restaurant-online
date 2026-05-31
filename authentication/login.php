<?php
$page_title = 'ورود';
require_once '../includes/header.php';

if (isLoggedIn()) {
    redirect(BASE_URL . 'index.php');
}

$errors = [];
$loginInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = sanitize($_POST['login_input']);
    $password = $_POST['password'];

    if (empty($loginInput)) {
        $errors[] = 'ایمیل یا شماره موبایل الزامی است.';
    }

    if (empty($password)) {
        $errors[] = 'رمز عبور الزامی است.';
    }

    if (empty($errors)) {
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $sql = "SELECT * FROM users WHERE $field = '$loginInput' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                setSuccessMessage('خوش آمدید ' . $user['full_name'] . '!');

                if ($user['role'] === 'admin') {
                    redirect(BASE_URL . 'admin/dashboard.php');
                } else {
                    redirect(BASE_URL . 'index.php');
                }
            } else {
                $errors[] = 'ایمیل/موبایل یا رمز عبور اشتباه است.';
            }
        } else {
            $errors[] = 'ایمیل/موبایل یا رمز عبور اشتباه است.';
        }
    }
}

$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'] ?? 0;
$totalFoods = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM foods WHERE is_available=1"))['count'] ?? 0;
?>

<link rel="stylesheet" href="../assets/css/login.css">

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
                        <span class="auth-visual__brand-sub">طعم واقعی غذاهای خانگی </span>
                    </div>
                </div>

                <h2>خوش برگشتید!</h2>
                <p class="auth-visual__desc">
                    با ورود به حساب کاربری، از پیگیری سفارش‌ها، تخفیف‌های ویژه و امتیازات منحصر‌به‌فرد اعضا بهره‌مند شوید.
                </p>

                <div class="auth-perks">
                    <div class="auth-perk">
                        <div class="auth-perk__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <span>سفارش سریع و آسان</span>
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
                    <div class="auth-perk">
                        <div class="auth-perk__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2z"/>
                            </svg>
                        </div>
                        <span>تخفیف‌های ویژه اعضا</span>
                    </div>
                </div>

                <div class="auth-stats">
                    <div>
                        <span class="auth-stat__number"><?php echo number_format($totalUsers); ?>+</span>
                        <span class="auth-stat__label">کاربر</span>
                    </div>
                    <div>
                        <span class="auth-stat__number"><?php echo number_format($totalFoods); ?>+</span>
                        <span class="auth-stat__label">غذای فعال</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-form-side">
            <div class="auth-form-side__header">
                <h1>ورود به حساب کاربری</h1>
                <p>برای ادامه، اطلاعات خود را وارد کنید</p>
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
                <div class="form-group">
                    <label class="form-label" for="loginInput">ایمیل یا شماره موبایل</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input type="text" name="login_input" id="loginInput" class="form-input"
                            placeholder="example@email.com یا ۰۹۱۲۳۴۵۶۷۸۹"
                            value="<?php echo htmlspecialchars($loginInput); ?>" required
                            dir="ltr">
                    </div>
                </div>

                <div class="form-group password-group">
                    <label class="form-label" for="loginPassword">رمز عبور</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input type="password" name="password" id="loginPassword" class="form-input"
                            placeholder="رمز عبور خود را وارد کنید" required>
                        <button type="button" class="toggle-password" id="togglePasswordBtn" aria-label="نمایش رمز عبور">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>مرا به خاطر بسپار</span>
                    </label>
                    <a href="#" class="forgot-link">بازیابی رمز عبور</a>
                </div>

                <button type="submit" class="btn-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    ورود به حساب کاربری
                </button>
            </form>

            <div class="divider">یا</div>

            <div class="auth-switch">
                حساب کاربری ندارید؟
                <a href="signup.php">همین حالا بسازید</a>
            </div>
        </div>

    </div>
</div>

<script src="../assets/js/login.js"></script>

<?php require_once '../includes/footer.php'; ?>