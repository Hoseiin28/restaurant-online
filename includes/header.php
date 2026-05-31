<?php
if (!isset($conn)) {
    require_once __DIR__ . '/../config/database.php';
}
if (!function_exists('checkAccess')) {
    require_once __DIR__ . '/functions.php';
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="رستوران آنلاین - سفارش غذای ایرانی و فست فود">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>رستوران آنلاین</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
</head>

<body>

    <div class="top-bar">
        <div class="container">
            <div class="top-bar__inner">
                <div class="top-bar__contact">
                    <div class="top-bar__phone">
                        <svg class="icon icon--xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        <span>021-12345678</span>
                    </div>
                    <span class="top-bar__sep">|</span>
                    <span class="top-bar__hours">همه روزه 10 صبح تا 11 شب</span>
                </div>
                <div class="top-bar__user-area">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-dropdown" id="userDropdown">
                            <button class="user-dropdown__toggle" id="userDropdownToggle" type="button" aria-haspopup="true" aria-expanded="false">
                                <svg class="icon icon--xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                <span class="user-dropdown__name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'کاربر'); ?></span>
                                <svg class="icon icon--xs user-dropdown__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </button>
                            <div class="user-dropdown__menu" id="userDropdownMenu">
                                <?php if (isAdmin()): ?>
                                    <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="user-dropdown__link">
                                        <svg class="icon icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="7" height="7" rx="1" />
                                            <rect x="14" y="3" width="7" height="7" rx="1" />
                                            <rect x="14" y="14" width="7" height="7" rx="1" />
                                            <rect x="3" y="14" width="7" height="7" rx="1" />
                                        </svg>
                                        <span>داشبورد ادمین</span>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>user/dashboard.php" class="user-dropdown__link">
                                        <svg class="icon icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                            <polyline points="9 22 9 12 15 12 15 22" />
                                        </svg>
                                        <span>داشبورد کاربری</span>
                                    </a>
                                <?php endif; ?>

                                <a href="<?php echo BASE_URL; ?>user/orders.php" class="user-dropdown__link">
                                    <svg class="icon icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                    </svg>
                                    <span>سفارش‌های من</span>
                                </a>

                                <a href="<?php echo BASE_URL; ?>user/reviews.php" class="user-dropdown__link">
                                    <svg class="icon icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                        <line x1="9" y1="10" x2="15" y2="10" />
                                        <line x1="12" y1="7" x2="12" y2="13" />
                                    </svg>
                                    <span>نظرات من</span>
                                </a>

                                <a href="<?php echo BASE_URL; ?>user/profile.php" class="user-dropdown__link">
                                    <svg class="icon icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                        <path d="M17 8l2 2-4 4-3 1 1-3 4-4z" />
                                    </svg>
                                    <span>ویرایش پروفایل</span>
                                </a>

                                <div class="user-dropdown__divider"></div>

                                <a href="<?php echo BASE_URL; ?>authentication/logout.php" class="user-dropdown__link user-dropdown__link--logout">
                                    <svg class="icon icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                        <polyline points="16 17 21 12 16 7" />
                                        <line x1="21" y1="12" x2="9" y2="12" />
                                    </svg>
                                    <span>خروج</span>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>authentication/login.php" class="btn-top btn-top--outline">ورود</a>
                        <a href="<?php echo BASE_URL; ?>authentication/signup.php" class="btn-top btn-top--fill">ثبت‌نام</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <header class="header" id="header">
        <div class="container">
            <div class="header__inner">
                <a href="<?php echo BASE_URL; ?>" class="header__logo">
                    <img
                        src="<?php echo BASE_URL; ?>assets/images/logo/logo.jpg"
                        alt="رستوران آنلاین"
                        class="logo-img"
                        width="500"
                        height="465">
                    <div class="logo-text">
                        <span class="logo-text__title">رستوران آنلاین</span>
                        <span class="logo-text__subtitle">طعم واقعی غذاهای خانگی</span>
                    </div>
                </a>

                <nav class="nav" id="navMenu">
                    <ul class="nav__list">
                        <li class="nav__item">
                            <a href="<?php echo BASE_URL; ?>" class="nav__link <?php echo ($current_page == 'index.php' || $current_page == '') ? 'nav__link--active' : ''; ?>">خانه</a>
                        </li>
                        <li class="nav__item">
                            <a href="<?php echo BASE_URL; ?>main/menu.php" class="nav__link <?php echo $current_page == 'menu.php' ? 'nav__link--active' : ''; ?>">منو غذا</a>
                        </li>
                        <li class="nav__item">
                            <a href="<?php echo BASE_URL; ?>main/about.php" class="nav__link <?php echo $current_page == 'about.php' ? 'nav__link--active' : ''; ?>">درباره ما</a>
                        </li>
                        <li class="nav__item">
                            <a href="#contact" class="nav__link">تماس با ما</a>
                        </li>
                    </ul>
                </nav>

                <div class="header__actions">
                    <a href="<?php echo BASE_URL; ?>main/cart.php" class="cart-btn" aria-label="سبد خرید">
                        <svg class="icon icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                        </svg>
                        <?php if (getCartCount() > 0): ?>
                            <span class="cart-badge"><?php echo getCartCount(); ?></span>
                        <?php endif; ?>
                    </a>

                    <button class="menu-toggle" id="menuToggle" type="button" aria-label="منوی اصلی" aria-expanded="false">
                        <span class="menu-toggle__bar"></span>
                        <span class="menu-toggle__bar"></span>
                        <span class="menu-toggle__bar"></span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="mobile-overlay" id="mobileOverlay"></div>

    <main class="main-content">