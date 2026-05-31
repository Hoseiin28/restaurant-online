<?php
$pageTitle = 'مدیریت کاربران';
require_once '../includes/header_admin.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;

if (isset($_GET['toggle_role']) && isset($_GET['token']) && $_GET['token'] === $_SESSION['csrf_token']) {
    $user_id = (int)$_GET['toggle_role'];

    if ($user_id == $_SESSION['user_id']) {
        setErrorMessage('شما نمی‌توانید نقش خود را تغییر دهید.');
    } else {
        $stmt = mysqli_prepare($conn, "SELECT role FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if ($user) {
            $new_role = ($user['role'] === 'admin') ? 'user' : 'admin';
            
            $update_stmt = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE id = ?");
            mysqli_stmt_bind_param($update_stmt, "si", $new_role, $user_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                setSuccessMessage('نقش کاربر با موفقیت تغییر کرد.');
            } else {
                setErrorMessage('خطا در تغییر نقش کاربر: ' . mysqli_error($conn));
            }
        } else {
            setErrorMessage('کاربر مورد نظر یافت نشد.');
        }
    }
    
    $redirect_params = $_GET;
    unset($redirect_params['toggle_role']);
    unset($redirect_params['token']);
    
    $redirect_url = BASE_URL . 'admin/users.php';
    if (!empty($redirect_params)) {
        $redirect_url .= '?' . http_build_query($redirect_params);
    }
    
    redirect($redirect_url);
    exit();
}

if (isset($_GET['delete']) && isset($_GET['token']) && $_GET['token'] === $_SESSION['csrf_token']) {
    $user_id = (int)$_GET['delete'];

    if ($user_id == $_SESSION['user_id']) {
        setErrorMessage('شما نمی‌توانید حساب خود را حذف کنید.');
    } else {
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order_count = mysqli_fetch_assoc($result)['count'];

        if ($order_count > 0) {
            setErrorMessage("این کاربر $order_count سفارش دارد و قابل حذف نیست.");
        } else {
            $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                setSuccessMessage('کاربر با موفقیت حذف شد.');
            } else {
                setErrorMessage('خطا در حذف کاربر: ' . mysqli_error($conn));
            }
        }
    }
    
    $redirect_params = $_GET;
    unset($redirect_params['delete']);
    unset($redirect_params['token']);
    
    $redirect_url = BASE_URL . 'admin/users.php';
    if (!empty($redirect_params)) {
        $redirect_url .= '?' . http_build_query($redirect_params);
    }
    
    redirect($redirect_url);
    exit();
}


$where = [];
if (!empty($search)) {
    $where[] = "(u.full_name LIKE '%$search%' OR u.email LIKE '%$search%' OR u.phone LIKE '%$search%')";
}
if (!empty($role_filter)) {
    $where[] = "u.role = '$role_filter'";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$order_clause = 'ORDER BY u.created_at DESC';
switch ($sort) {
    case 'oldest':
        $order_clause = 'ORDER BY u.created_at ASC';
        break;
    case 'name_asc':
        $order_clause = 'ORDER BY u.full_name ASC';
        break;
    case 'name_desc':
        $order_clause = 'ORDER BY u.full_name DESC';
        break;
    case 'orders':
        $order_clause = 'ORDER BY order_count DESC';
        break;
}

$count_sql = "SELECT COUNT(*) as total FROM users u $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$total_users = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_users / $per_page);
$offset = ($page - 1) * $per_page;

$users_sql = "SELECT u.*, 
              (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
              (SELECT SUM(total_price) FROM orders WHERE user_id = u.id AND status = 'delivered') as total_spent,
              (SELECT COUNT(*) FROM reviews WHERE user_id = u.id) as review_count
              FROM users u 
              $where_clause 
              $order_clause 
              LIMIT $offset, $per_page";
$users_result = mysqli_query($conn, $users_sql);

$stats = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_new
     FROM users"
));

generateCSRFToken();
?>

<link rel="stylesheet" href="../assets/css/admin_users.css">

<div class="page-toolbar">
    <h2>مدیریت کاربران</h2>
</div>

<div class="stats-mini-row">
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#eff6ff;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#3b82f6;stroke-width:2;">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($stats['users']); ?></strong>
            <span>کاربر عادی</span>
        </div>
    </div>
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#ede9fe;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#8b5cf6;stroke-width:2;">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($stats['admins']); ?></strong>
            <span>مدیر</span>
        </div>
    </div>
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#fef3c7;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#f59e0b;stroke-width:2;">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($stats['today_new']); ?></strong>
            <span>ثبت‌نام امروز</span>
        </div>
    </div>
</div>

<div class="filters-card">
    <form method="GET" action="">
        <div class="filters-row">
            <div class="search-wrap">
                <span class="search-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </span>
                <input type="text" name="search" placeholder="جستجوی نام، ایمیل یا موبایل..."
                    value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <select name="role" class="filter-select" onchange="this.form.submit()">
                <option value="">همه نقش‌ها</option>
                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>کاربر عادی</option>
                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>مدیر</option>
            </select>

            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>جدیدترین</option>
                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>قدیمی‌ترین</option>
                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>نام (الفبا)</option>
                <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>نام (معکوس)</option>
                <option value="orders" <?php echo $sort === 'orders' ? 'selected' : ''; ?>>بیشترین سفارش</option>
            </select>

            <?php if (!empty($search) || !empty($role_filter)): ?>
                <a href="users.php" class="clear-filters">حذف فیلترها</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="users-grid">
    <?php if (mysqli_num_rows($users_result) > 0): ?>
        <?php while ($user = mysqli_fetch_assoc($users_result)):
            $is_me = $user['id'] == $_SESSION['user_id'];
            $is_admin = $user['role'] === 'admin';
            $stripe_color = $is_admin ? '#8b5cf6' : '#3b82f6';
            $avatar_bg = $is_admin ? '#ede9fe' : '#eff6ff';
            $avatar_stroke = $is_admin ? '#8b5cf6' : '#3b82f6';
            $dot_color = $is_admin ? '#8b5cf6' : '#22c55e';
        ?>
            <div class="user-card <?php echo $is_me ? 'is-me' : ''; ?>">
                <?php if ($is_me): ?>
                    <span class="me-badge">شما</span>
                <?php endif; ?>

                <div class="user-card-stripe" style="background:<?php echo $stripe_color; ?>"></div>

                <div class="user-card-body-main">
                    <div class="user-card-top">
                        <div class="user-avatar-circle" style="background:<?php echo $avatar_bg; ?>;">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                style="fill:none;stroke:<?php echo $avatar_stroke; ?>;stroke-width:2;">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                            </svg>
                            <span class="role-dot" style="background:<?php echo $dot_color; ?>;"></span>
                        </div>
                        <div class="user-info-top">
                            <h3><?php echo $user['full_name']; ?></h3>
                            <span class="user-email-text"><?php echo $user['email']; ?></span>
                            <span class="user-phone-text"><?php echo $user['phone'] ? $user['phone'] : 'بدون شماره'; ?></span>
                        </div>
                    </div>

                    <div class="user-stats-row">
                        <div class="user-stat-item">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <path d="M1 3h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" />
                            </svg>
                            <strong><?php echo number_format($user['order_count']); ?></strong> سفارش
                        </div>
                        <div class="user-stat-item">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                            </svg>
                            <strong><?php echo $user['total_spent'] ? number_format($user['total_spent']) : '0'; ?></strong> تومان
                        </div>
                        <div class="user-stat-item">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                            </svg>
                            <strong><?php echo number_format($user['review_count']); ?></strong> نظر
                        </div>
                    </div>

                    <div class="user-meta-row">
                        <span class="user-role-badge <?php echo $is_admin ? 'role-admin-badge' : 'role-user-badge'; ?>">
                            <?php echo $is_admin ? 'مدیر' : 'کاربر'; ?>
                        </span>
                        <span class="user-join-date">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:2;">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                            </svg>
                            <?php echo toJalali($user['created_at'], 'Y/m/d'); ?>
                        </span>
                    </div>
                </div>

                <div class="user-card-actions">
                    <?php if (!$is_me): ?>
                        <button class="btn btn-outline-warning"
                            onclick="toggleRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>', '<?php echo addslashes($user['full_name']); ?>')">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <polyline points="17 1 21 5 17 9" />
                                <path d="M3 11V9a4 4 0 014-4h14" />
                                <polyline points="7 23 3 19 7 15" />
                                <path d="M21 13v2a4 4 0 01-4 4H3" />
                            </svg>
                            تغییر نقش
                        </button>
                        <button class="btn btn-outline-danger"
                            onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo addslashes($user['full_name']); ?>', <?php echo $user['order_count']; ?>)">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <polyline points="3 6 5 6 21 6" />
                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                            </svg>
                            حذف
                        </button>
                    <?php else: ?>
                        <span style="flex:1;text-align:center;font-size:12px;color:#94a3b8;padding:8px;">
                            حساب کاربری شما
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state-full">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#94a3b8;stroke-width:1.5;">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
            </svg>
            <h3>هیچ کاربری یافت نشد</h3>
            <p><?php echo !empty($search) ? 'کاربری با این مشخصات پیدا نشد.' : 'کاربری ثبت نشده است.'; ?></p>
        </div>
    <?php endif; ?>
</div>

<?php if ($total_pages > 1): ?>
    <div class="pagination-row">
        <div class="pagination-info">
            نمایش <?php echo min(($page - 1) * $per_page + 1, $total_users); ?>
            تا <?php echo min($page * $per_page, $total_users); ?>
            از <?php echo number_format($total_users); ?> کاربر
        </div>
        <div class="pagination">
            <?php
            $query_params = array_filter($_GET);
            unset($query_params['page']);
            $base_url = 'users.php?' . http_build_query($query_params);
            ?>
            <?php if ($page > 1): ?>
                <a href="<?php echo $base_url; ?>&page=<?php echo $page - 1; ?>" class="page-link">قبلی</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++):
                if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                    <a href="<?php echo $base_url; ?>&page=<?php echo $i; ?>"
                        class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                    <span class="page-dots">...</span>
            <?php endif;
            endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="<?php echo $base_url; ?>&page=<?php echo $page + 1; ?>" class="page-link">بعدی</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="modal" id="deleteModal">
    <div class="modal-overlay" onclick="closeDeleteModal()"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>تأیید حذف کاربر</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>آیا از حذف <strong id="deleteUserName"></strong> اطمینان دارید؟</p>
            <p style="font-size:12px;color:#ef4444;">این عملیات قابل بازگشت نیست.</p>
            <p id="deleteWarning" style="font-size:12px;color:#f59e0b;display:none;"></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-light" onclick="closeDeleteModal()">انصراف</button>
            <a href="#" id="deleteConfirmBtn" class="btn btn-danger">حذف کاربر</a>
        </div>
    </div>
</div>

<script>
    const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token']; ?>';
</script>
<script src="../assets/js/admin_users.js"></script>

<?php require_once '../includes/footer_admin.php'; ?>