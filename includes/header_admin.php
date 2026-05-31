<?php
if (!isset($conn)) {
    require_once __DIR__ . '/../config/database.php';
}
if (!function_exists('checkAccess')) {
    require_once __DIR__ . '/functions.php';
}

checkAccess('admin');

if (!isset($pageTitle)) {
    $pageTitle = 'داشبورد';
}

$adminName = $_SESSION['full_name'] ?? 'مدیر سیستم';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | پنل مدیریت</title>
    <link rel="stylesheet" href="../assets/css/header_admin.css">
</head>

<body>
    <div class="admin-layout">
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="brand-link">
                    <img
                        src="<?php echo BASE_URL; ?>assets/images/logo/logo.jpg"
                        alt="پنل مدیریت رستوران آنلاین"
                        class="sidebar-brand-logo"
                        width="500"
                        height="465">
                    <div class="brand-text">
                        <h3>پنل مدیریت</h3>
                        <span>رستوران آنلاین</span>
                    </div>
                </a>
                <button class="sidebar-close-btn" onclick="closeSidebar()" aria-label="بستن منو">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <?php
                    $currentPage = basename($_SERVER['PHP_SELF']);
                    $menuItems = [
                        'dashboard.php'  => ['title' => 'داشبورد', 'icon' => '<svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>'],
                        'foods.php'      => ['title' => 'مدیریت غذاها', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>'],
                        'categories.php' => ['title' => 'دسته‌بندی‌ها', 'icon' => '<svg viewBox="0 0 24 24"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="4" rx="1"/><rect x="13" y="9" width="8" height="4" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/></svg>'],
                        'orders.php'     => ['title' => 'سفارش‌ها', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>', 'badge' => $pending_orders_count ?? 0],
                        'users.php'      => ['title' => 'کاربران', 'icon' => '<svg viewBox="0 0 24 24"><circle cx="9" cy="7" r="3"/><path d="M1 21v-2a4 4 0 014-4h8a4 4 0 014 4v2" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17" cy="7" r="2"/></svg>'],
                        'reviews.php'    => ['title' => 'نظرات', 'icon' => '<svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>', 'badge' => $new_reviews_count ?? 0]
                    ];

                    foreach ($menuItems as $file => $item):
                        $isActive = ($currentPage === $file);
                    ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL . 'admin/' . $file; ?>" class="<?php echo $isActive ? 'active' : ''; ?>">
                                <span class="nav-icon"><?php echo $item['icon']; ?></span>
                                <?php echo $item['title']; ?>
                                <?php if (!empty($item['badge'])): ?>
                                    <span class="nav-badge"><?php echo $item['badge']; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info-mini">
                    <div class="user-avatar-mini">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                        </svg>
                    </div>
                    <div class="user-details-mini">
                        <strong><?php echo htmlspecialchars($adminName); ?></strong>
                        <span>مدیر سیستم</span>
                    </div>
                </div>
                <div class="footer-actions">
                    <a href="<?php echo BASE_URL; ?>" target="_self" class="footer-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
                            <polyline points="15 3 21 3 21 9" />
                            <line x1="10" y1="14" x2="21" y2="3" />
                        </svg>
                        مشاهده سایت
                    </a>
                    <a href="<?php echo BASE_URL; ?>authentication/logout.php" class="footer-btn logout" onclick="return confirm('آیا از خروج اطمینان دارید؟')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                        خروج از حساب
                    </a>
                </div>
            </div>
        </aside>

        <main class="admin-main" id="adminMain">
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()" aria-label="منو">
                        <span></span><span></span><span></span>
                    </button>
                    <div class="breadcrumb">
                        <svg viewBox="0 0 24 24">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" fill="none" stroke="currentColor" stroke-width="1.5" />
                        </svg>
                        <span>خانه</span>
                        <span>/</span>
                        <span><?php echo $pageTitle; ?></span>
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="topbar-date">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        <?php echo fullJalaliDate(date('Y-m-d')); ?>
                    </div>
                    <div class="user-chip">
                        <div class="user-chip-avatar">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                            </svg>
                        </div>
                        <span class="user-chip-name"><?php echo htmlspecialchars($adminName); ?></span>
                    </div>
                </div>
            </header>

            <div class="admin-content">

                <?php
                if (function_exists('showMessages')) {
                    echo showMessages();
                }
                ?>