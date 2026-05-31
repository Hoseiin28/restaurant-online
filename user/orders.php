<?php
$page_title = 'سفارش‌های من';
require_once '../includes/header.php';

checkAccess('user');

$user_id = $_SESSION['user_id'];

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 6;

$where = "WHERE o.user_id = $user_id";
if (!empty($status_filter)) {
    $where .= " AND o.status = '$status_filter'";
}

$count_sql = "SELECT COUNT(*) as total FROM orders o $where";
$count_result = mysqli_query($conn, $count_sql);
$total_orders = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_orders / $per_page);
$offset = ($page - 1) * $per_page;

$orders_sql = "SELECT o.*, 
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
               FROM orders o 
               $where 
               ORDER BY o.order_date DESC 
               LIMIT $offset, $per_page";
$orders_result = mysqli_query($conn, $orders_sql);

$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
    SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM orders WHERE user_id = $user_id";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_sql));

$status_config = [
    'pending'   => ['text' => 'در انتظار', 'color' => '#E65100', 'bg' => '#FFF3E0'],
    'confirmed' => ['text' => 'تأیید شده', 'color' => '#1565C0', 'bg' => '#E3F2FD'],
    'preparing' => ['text' => 'در حال آماده‌سازی', 'color' => '#6A1B9A', 'bg' => '#EDE7F6'],
    'delivered' => ['text' => 'تحویل شده', 'color' => '#2E7D32', 'bg' => '#E8F5E9'],
    'cancelled' => ['text' => 'لغو شده', 'color' => '#C62828', 'bg' => '#FFEBEE'],
];

$tabs = [
    ''           => ['label' => 'همه', 'count' => $stats['total']],
    'pending'    => ['label' => 'در انتظار', 'count' => $stats['pending']],
    'confirmed'  => ['label' => 'تأیید شده', 'count' => $stats['confirmed']],
    'preparing'  => ['label' => 'آماده‌سازی', 'count' => $stats['preparing']],
    'delivered'  => ['label' => 'تحویل شده', 'count' => $stats['delivered']],
    'cancelled'  => ['label' => 'لغو شده', 'count' => $stats['cancelled']],
];
?>

<link rel="stylesheet" href="../assets/css/user_orders.css">

<div class="orders-page">
    <div class="orders-wrapper">

        <aside class="orders-sidebar">
            <div class="filter-card">
                <div class="filter-card__header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    <h3>فیلتر وضعیت</h3>
                </div>
                <div class="filter-list">
                    <?php foreach ($tabs as $key => $tab): 
                        $is_active = ($status_filter === $key);
                        $url = 'orders.php' . ($key !== '' ? '?status=' . $key : '');
                    ?>
                        <a href="<?php echo $url; ?>" 
                           class="filter-link <?php echo $is_active ? 'filter-link--active' : ''; ?>">
                            <?php echo $tab['label']; ?>
                            <span class="filter-link__count"><?php echo $tab['count']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

        <div class="orders-content">

            <div class="orders-header">
                <div class="orders-header__info">
                    <h1>سفارش‌های من</h1>
                    <span><?php echo number_format($total_orders); ?> سفارش ثبت شده</span>
                </div>
                <a href="<?php echo BASE_URL; ?>main/menu.php" class="btn btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    سفارش جدید
                </a>
            </div>

            <?php if (mysqli_num_rows($orders_result) > 0): ?>
                <div class="orders-list">
                    <?php while ($order = mysqli_fetch_assoc($orders_result)):
                        $items_sql = "SELECT oi.*, f.name as food_name 
                                     FROM order_items oi 
                                     JOIN foods f ON oi.food_id = f.id 
                                     WHERE oi.order_id = {$order['id']}";
                        $items_result = mysqli_query($conn, $items_sql);
                        $items = [];
                        while ($item = mysqli_fetch_assoc($items_result)) {
                            $items[] = $item;
                        }

                        $current_status = $order['status'];
                        $status_info = $status_config[$current_status] ?? $status_config['pending'];
                        
                        $step_map = ['pending' => 1, 'confirmed' => 2, 'preparing' => 3, 'delivered' => 4];
                        $current_step = $step_map[$current_status] ?? 0;
                    ?>
                        <div class="order-card <?php echo $current_status === 'cancelled' ? 'order-card--cancelled' : ''; ?>">
                            <div class="order-card__top">
                                <span class="order-card__id">سفارش #<?php echo $order['id']; ?></span>
                                <span class="order-card__date"><?php echo toJalali($order['order_date'], 'Y/m/d H:i'); ?></span>
                                <span class="order-card__status" style="background:<?php echo $status_info['bg']; ?>;color:<?php echo $status_info['color']; ?>;">
                                    <?php echo $status_info['text']; ?>
                                </span>
                            </div>

                            <?php if ($current_status !== 'cancelled'): ?>
                                <div class="order-timeline">
                                    <div class="timeline-row">
                                        <?php
                                        $steps = [
                                            ['title' => 'ثبت سفارش', 'step' => 1],
                                            ['title' => 'تأیید', 'step' => 2],
                                            ['title' => 'آماده‌سازی', 'step' => 3],
                                            ['title' => 'تحویل', 'step' => 4],
                                        ];
                                        foreach ($steps as $index => $step):
                                            $is_done = $current_step > $step['step'];
                                            $is_current = $current_step === $step['step'];
                                            $circle_class = $is_done ? 'timeline-step__circle--done' : ($is_current ? 'timeline-step__circle--current' : '');
                                            $label_class = $is_done ? 'timeline-step__label--done' : ($is_current ? 'timeline-step__label--current' : '');
                                        ?>
                                            <div class="timeline-step">
                                                <div class="timeline-step__circle <?php echo $circle_class; ?>">
                                                    <?php echo $is_done ? '✓' : $step['step']; ?>
                                                </div>
                                                <span class="timeline-step__label <?php echo $label_class; ?>">
                                                    <?php echo $step['title']; ?>
                                                </span>
                                            </div>
                                            <?php if ($index < count($steps) - 1): ?>
                                                <div class="timeline-line <?php echo $current_step > $step['step'] ? 'timeline-line--done' : ''; ?>"></div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="order-card__items">
                                <div class="order-card__items-title">اقلام سفارش (<?php echo count($items); ?> آیتم)</div>
                                <div class="order-items-list">
                                    <?php foreach ($items as $item): ?>
                                        <div class="order-item">
                                            <span class="order-item__name"><?php echo htmlspecialchars($item['food_name']); ?></span>
                                            <span class="order-item__qty">×<?php echo $item['quantity']; ?></span>
                                            <span class="order-item__price"><?php echo number_format($item['price_per_unit'] * $item['quantity']); ?> تومان</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="order-card__footer">
                                <div>
                                    <span class="order-card__total-label">مبلغ کل: </span>
                                    <span class="order-card__total-price"><?php echo number_format($order['total_price']); ?> تومان</span>
                                </div>
                                <?php if ($current_status !== 'cancelled'): ?>
                                    <a href="<?php echo BASE_URL; ?>main/menu.php" class="btn btn--outline-xs">سفارش مجدد</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php $base_url = 'orders.php?' . ($status_filter ? "status=$status_filter&" : ''); ?>
                        
                        <?php if ($page > 1): ?>
                            <a href="<?php echo $base_url; ?>page=<?php echo $page - 1; ?>" class="pagination__link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                    <polyline points="15 18 9 12 15 6"/>
                                </svg>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++):
                            if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                <a href="<?php echo $base_url; ?>page=<?php echo $i; ?>" 
                                   class="pagination__link <?php echo $i === $page ? 'pagination__link--active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                <span class="pagination__dots">...</span>
                            <?php endif;
                        endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="<?php echo $base_url; ?>page=<?php echo $page + 1; ?>" class="pagination__link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                    <polyline points="9 18 15 12 9 6"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-orders">
                    <div class="empty-orders__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                    </div>
                    <h2>سفارشی یافت نشد</h2>
                    <p>
                        <?php if (!empty($status_filter)): ?>
                            سفارشی با این وضعیت ندارید. <a href="orders.php">نمایش همه سفارش‌ها</a>
                        <?php else: ?>
                            هنوز هیچ سفارشی ثبت نکرده‌اید. منوی ما را ببینید و اولین سفارش خود را ثبت کنید!
                        <?php endif; ?>
                    </p>
                    <a href="<?php echo BASE_URL; ?>main/menu.php" class="btn btn--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                        </svg>
                        مشاهده منو و سفارش
                    </a>
                </div>
            <?php endif; ?>

            <a href="dashboard.php" class="btn btn--ghost" style="margin-top:16px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                بازگشت به داشبورد
            </a>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>