<?php
$pageTitle = 'مدیریت سفارش‌ها';
require_once '../includes/header_admin.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 15;

$date_from_gregorian = '';
$date_to_gregorian = '';

if (!empty($date_from)) {
    $date_from_gregorian = toGregorian($date_from);
}
if (!empty($date_to)) {
    $date_to_gregorian = toGregorian($date_to);
}

if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);

    $allowed_statuses = ['pending', 'confirmed', 'preparing', 'delivered', 'cancelled'];
    if (in_array($status, $allowed_statuses)) {
        mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id=$order_id");
        setSuccessMessage('وضعیت سفارش #' . $order_id . ' بروزرسانی شد.');
    }
    redirect(BASE_URL . 'admin/orders.php?' . http_build_query(array_filter($_GET)));
}

$where = [];
if (!empty($search)) {
    $where[] = "(o.id LIKE '%$search%' OR u.full_name LIKE '%$search%' OR u.phone LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $where[] = "o.status = '$status_filter'";
}
if (!empty($date_from_gregorian)) {
    $where[] = "DATE(o.order_date) >= '$date_from_gregorian'";
}
if (!empty($date_to_gregorian)) {
    $where[] = "DATE(o.order_date) <= '$date_to_gregorian'";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$count_sql = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.id $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$total_orders = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_orders / $per_page);
$offset = ($page - 1) * $per_page;

$orders_sql = "SELECT o.*, u.full_name as user_name, u.phone as user_phone, u.email as user_email,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
               FROM orders o 
               JOIN users u ON o.user_id = u.id 
               $where_clause 
               ORDER BY o.order_date DESC 
               LIMIT $offset, $per_page";
$orders_result = mysqli_query($conn, $orders_sql);

$stats = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status IN ('confirmed','preparing') THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
        SUM(CASE WHEN status != 'cancelled' THEN total_price ELSE 0 END) as revenue,
        SUM(CASE WHEN DATE(order_date) = CURDATE() THEN total_price ELSE 0 END) as today_revenue
    FROM orders"
));

generateCSRFToken();
?>

<link rel="stylesheet" href="../assets/css/admin_orders.css">

<div class="page-toolbar">
    <h2>مدیریت سفارش‌ها</h2>
</div>

<div class="stats-mini-row">
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#fef3c7;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#f59e0b;stroke-width:2;">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($stats['pending']); ?></strong>
            <span>در انتظار بررسی</span>
        </div>
    </div>
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#dbeafe;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#3b82f6;stroke-width:2;">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($stats['active']); ?></strong>
            <span>فعال (تأیید/آماده‌سازی)</span>
        </div>
    </div>
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#d1fae5;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#10b981;stroke-width:2;">
                <line x1="12" y1="1" x2="12" y2="23" />
                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($stats['revenue']); ?></strong>
            <span>درآمد کل (تومان)</span>
            <span class="stat-mini-sub">امروز: <?php echo number_format($stats['today_revenue']); ?> تومان</span>
        </div>
    </div>
</div>

<div class="filters-card">
    <form method="GET" action="" id="filterForm">
        <div class="filters-row">
            <div class="search-wrap">
                <span class="search-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </span>
                <input type="text" name="search" placeholder="جستجوی شماره، نام یا موبایل..."
                    value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">همه وضعیت‌ها</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>در انتظار</option>
                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>تأیید شده</option>
                <option value="preparing" <?php echo $status_filter === 'preparing' ? 'selected' : ''; ?>>در حال آماده‌سازی</option>
                <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>تحویل شده</option>
                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>لغو شده</option>
            </select>

            <div class="datepicker-wrapper" id="dateFromWrapper">
                <input type="text" name="date_from" class="datepicker-input"
                    id="dateFrom" placeholder="از تاریخ"
                    value="<?php echo htmlspecialchars($date_from); ?>" readonly>
                <span class="datepicker-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </span>
                <div class="jalali-calendar" id="calendarFrom"></div>
            </div>

            <span class="filter-date-separator">تا</span>

            <div class="datepicker-wrapper" id="dateToWrapper">
                <input type="text" name="date_to" class="datepicker-input"
                    id="dateTo" placeholder="تا تاریخ"
                    value="<?php echo htmlspecialchars($date_to); ?>" readonly>
                <span class="datepicker-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </span>
                <div class="jalali-calendar" id="calendarTo"></div>
            </div>

            <button type="submit" class="btn btn-outline-primary" style="padding:10px 16px;">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                اعمال فیلتر
            </button>

            <?php if (!empty($search) || !empty($status_filter) || !empty($date_from) || !empty($date_to)): ?>
                <a href="orders.php" class="clear-filters">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2;">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                    حذف فیلترها
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="orders-grid">
    <?php if (mysqli_num_rows($orders_result) > 0): ?>
        <?php while ($order = mysqli_fetch_assoc($orders_result)):
            $status_color = [
                'pending' => '#f59e0b',
                'confirmed' => '#3b82f6',
                'preparing' => '#8b5cf6',
                'delivered' => '#10b981',
                'cancelled' => '#ef4444'
            ];
            $status_class = [
                'pending' => 'status-pending',
                'confirmed' => 'status-active',
                'preparing' => 'status-active',
                'delivered' => 'status-delivered',
                'cancelled' => 'status-cancelled'
            ];
            $status_text = [
                'pending' => 'در انتظار',
                'confirmed' => 'تأیید شده',
                'preparing' => 'در حال آماده‌سازی',
                'delivered' => 'تحویل شده',
                'cancelled' => 'لغو شده'
            ];
            $color = $status_color[$order['status']] ?? '#94a3b8';
            $s_class = $status_class[$order['status']] ?? 'status-pending';
            $s_text = $status_text[$order['status']] ?? 'نامشخص';
        ?>
            <div class="order-card">
                <div class="order-card-status" style="background:<?php echo $color; ?>"></div>
                <div class="order-card-body">
                    <div class="order-card-top">
                        <div class="order-id-section">
                            <span class="order-number">#<?php echo $order['id']; ?></span>
                            <span class="order-status-badge <?php echo $s_class; ?>"><?php echo $s_text; ?></span>
                        </div>
                        <span class="order-date"><?php echo toJalali($order['order_date'], 'Y/m/d H:i'); ?></span>
                    </div>
                    <div class="order-user-row">
                        <div class="user-avatar">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                            </svg>
                        </div>
                        <div class="user-info">
                            <strong><?php echo $order['user_name']; ?></strong>
                            <span><?php echo $order['user_phone'] ? $order['user_phone'] : $order['user_email']; ?></span>
                        </div>
                    </div>
                    <div class="order-details-row">
                        <div class="order-detail-item">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <path d="M1 3h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" />
                            </svg>
                            <strong><?php echo $order['item_count']; ?></strong> قلم
                        </div>
                        <div class="order-detail-item">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                            </svg>
                            <?php echo toJalali($order['order_date'], 'Y/m/d'); ?>
                        </div>
                    </div>
                    <div class="order-total">
                        <span class="order-total-label">مبلغ کل:</span>
                        <span class="order-total-price"><?php echo number_format($order['total_price']); ?> تومان</span>
                    </div>
                </div>
                <div class="order-card-actions">
                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-info">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        جزئیات
                    </a>
                    <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                        <form method="POST" style="flex:1;display:flex;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="update_status" value="1">
                            <select name="status" class="filter-select" onchange="if(confirm('تغییر وضعیت؟')) this.form.submit()" style="flex:1;">
                                <option value="">تغییر وضعیت</option>
                                <option value="confirmed">تأیید</option>
                                <option value="preparing">آماده‌سازی</option>
                                <option value="delivered">تحویل شد</option>
                                <option value="cancelled">لغو</option>
                            </select>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state-full">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#94a3b8;stroke-width:1.5;">
                <path d="M1 3h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" />
            </svg>
            <h3>هیچ سفارشی یافت نشد</h3>
            <p><?php echo !empty($search) || !empty($status_filter) || !empty($date_from) ? 'سفارشی با این شرایط پیدا نشد.' : 'هنوز سفارشی ثبت نشده.'; ?></p>
        </div>
    <?php endif; ?>
</div>

<?php if ($total_pages > 1): ?>
    <div class="pagination-row">
        <div class="pagination-info">
            نمایش <?php echo min(($page - 1) * $per_page + 1, $total_orders); ?>
            تا <?php echo min($page * $per_page, $total_orders); ?>
            از <?php echo number_format($total_orders); ?> سفارش
        </div>
        <div class="pagination">
            <?php
            $query_params = array_filter($_GET);
            unset($query_params['page']);
            $base_url = 'orders.php?' . http_build_query($query_params);
            ?>
            <?php if ($page > 1): ?>
                <a href="<?php echo $base_url; ?>&page=<?php echo $page - 1; ?>" class="page-link">قبلی</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++):
                if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                    <a href="<?php echo $base_url; ?>&page=<?php echo $i; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
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

<script src="../assets/js/admin_orders.js"></script>

<?php require_once '../includes/footer_admin.php'; ?>