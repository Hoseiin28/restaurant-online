<?php
$pageTitle = 'مدیریت نظرات';
require_once '../includes/header_admin.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$food_filter = isset($_GET['food_id']) ? (int)$_GET['food_id'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;

if (isset($_GET['delete']) && isset($_GET['token']) && $_GET['token'] === $_SESSION['csrf_token']) {
    $review_id = (int)$_GET['delete'];

    $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $review_id);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_affected_rows($conn) > 0) {
            setSuccessMessage('نظر با موفقیت حذف شد.');
        } else {
            setErrorMessage('نظر مورد نظر یافت نشد.');
        }
    } else {
        setErrorMessage('خطا در حذف نظر: ' . mysqli_error($conn));
    }

    $redirect_params = $_GET;
    unset($redirect_params['delete']);
    unset($redirect_params['token']);

    $redirect_url = BASE_URL . 'admin/reviews.php';
    if (!empty($redirect_params)) {
        $redirect_url .= '?' . http_build_query($redirect_params);
    }

    redirect($redirect_url);
    exit();
}

$where = [];
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $where[] = "(u.full_name LIKE '%$search_escaped%' OR f.name LIKE '%$search_escaped%' OR r.comment LIKE '%$search_escaped%')";
}
if ($rating_filter > 0) {
    $where[] = "r.rating = " . (int)$rating_filter;
}
if ($food_filter > 0) {
    $where[] = "r.food_id = " . (int)$food_filter;
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$order_clause = 'ORDER BY r.created_at DESC';
switch ($sort) {
    case 'oldest':
        $order_clause = 'ORDER BY r.created_at ASC';
        break;
    case 'rating_high':
        $order_clause = 'ORDER BY r.rating DESC';
        break;
    case 'rating_low':
        $order_clause = 'ORDER BY r.rating ASC';
        break;
}

$count_sql = "SELECT COUNT(*) as total FROM reviews r JOIN users u ON r.user_id = u.id JOIN foods f ON r.food_id = f.id $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$total_reviews = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_reviews / $per_page);
$offset = ($page - 1) * $per_page;

$reviews_sql = "SELECT r.*, u.full_name as user_name, u.email as user_email, 
                f.name as food_name, f.id as food_id
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                JOIN foods f ON r.food_id = f.id 
                $where_clause 
                $order_clause 
                LIMIT $offset, $per_page";
$reviews_result = mysqli_query($conn, $reviews_sql);

$stats = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT 
        COUNT(*) as total,
        IFNULL(AVG(rating), 0) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating <= 3 THEN 1 ELSE 0 END) as low_star,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_new
     FROM reviews"
));

$foods_list = mysqli_query($conn, "SELECT id, name FROM foods ORDER BY name");

generateCSRFToken();
?>

<link rel="stylesheet" href="../assets/css/admin_reviews.css">

<div class="page-toolbar">
    <h2>مدیریت نظرات</h2>
</div>

<div class="stats-mini-row">
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#fef3c7;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:#f59e0b;stroke:none;">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($stats['avg_rating'], 1); ?></strong>
            <span>میانگین امتیاز</span>
        </div>
    </div>
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#d1fae5;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#10b981;stroke-width:2;">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($stats['five_star']); ?></strong>
            <span>نظر عالی (۵ ستاره)</span>
        </div>
    </div>
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:#fee2e2;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#ef4444;stroke-width:2;">
                <circle cx="12" cy="12" r="10" />
                <line x1="8" y1="15" x2="16" y2="15" />
            </svg>
        </div>
        <div class="stat-mini-info">
            <strong><?php echo number_format($stats['low_star']); ?></strong>
            <span>نظر منفی (کمتر از ۳)</span>
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
                <input type="text" name="search" placeholder="جستجوی کاربر، غذا یا متن نظر..."
                    value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <select name="rating" class="filter-select" onchange="this.form.submit()">
                <option value="">همه امتیازها</option>
                <option value="5" <?php echo $rating_filter === 5 ? 'selected' : ''; ?>>۵ ستاره</option>
                <option value="4" <?php echo $rating_filter === 4 ? 'selected' : ''; ?>>۴ ستاره</option>
                <option value="3" <?php echo $rating_filter === 3 ? 'selected' : ''; ?>>۳ ستاره</option>
                <option value="2" <?php echo $rating_filter === 2 ? 'selected' : ''; ?>>۲ ستاره</option>
                <option value="1" <?php echo $rating_filter === 1 ? 'selected' : ''; ?>>۱ ستاره</option>
            </select>

            <select name="food_id" class="filter-select" onchange="this.form.submit()">
                <option value="">همه غذاها</option>
                <?php
                mysqli_data_seek($foods_list, 0);
                while ($food = mysqli_fetch_assoc($foods_list)):
                ?>
                    <option value="<?php echo $food['id']; ?>" <?php echo $food_filter === $food['id'] ? 'selected' : ''; ?>>
                        <?php echo $food['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>جدیدترین</option>
                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>قدیمی‌ترین</option>
                <option value="rating_high" <?php echo $sort === 'rating_high' ? 'selected' : ''; ?>>بیشترین امتیاز</option>
                <option value="rating_low" <?php echo $sort === 'rating_low' ? 'selected' : ''; ?>>کمترین امتیاز</option>
            </select>

            <?php if (!empty($search) || $rating_filter > 0 || $food_filter > 0): ?>
                <a href="reviews.php" class="clear-filters">حذف فیلترها</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="reviews-grid">
    <?php if (mysqli_num_rows($reviews_result) > 0): ?>
        <?php while ($review = mysqli_fetch_assoc($reviews_result)):
            $stripe_color = $review['rating'] >= 4 ? '#10b981' : ($review['rating'] >= 3 ? '#f59e0b' : '#ef4444');
            $avatar_bg = $review['rating'] >= 4 ? '#d1fae5' : ($review['rating'] >= 3 ? '#fef3c7' : '#fee2e2');
            $avatar_stroke = $review['rating'] >= 4 ? '#10b981' : ($review['rating'] >= 3 ? '#f59e0b' : '#ef4444');
        ?>
            <div class="review-card">
                <div class="review-card-stripe" style="background:<?php echo $stripe_color; ?>"></div>

                <div class="review-card-body">
                    <div class="review-user-row">
                        <div class="review-avatar" style="background:<?php echo $avatar_bg; ?>;">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                style="fill:none;stroke:<?php echo $avatar_stroke; ?>;stroke-width:2;">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                            </svg>
                        </div>
                        <div class="review-user-info">
                            <strong><?php echo $review['user_name']; ?></strong>
                            <span><?php echo $review['user_email']; ?></span>
                        </div>
                        <div class="review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                    class="star <?php echo $i <= $review['rating'] ? 'filled' : 'empty'; ?>">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                </svg>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="review-food-tag">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                            <path d="M12 2L2 7l10 5 10-5-10-5z" />
                        </svg>
                        <strong><?php echo $review['food_name']; ?></strong>
                    </div>

                    <?php if (!empty(trim($review['comment']))): ?>
                        <p class="review-text"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    <?php else: ?>
                        <p style="font-size:12px;color:#94a3b8;font-style:italic;">بدون متن</p>
                    <?php endif; ?>

                    <?php if (!empty($review['admin_reply'])): ?>
                        <div class="admin-reply-box">
                            <div class="admin-reply-header">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                                </svg>
                                پاسخ شما
                                <span class="admin-reply-date"><?php echo fullJalaliDate($review['admin_reply_date']); ?></span>
                                <button class="btn-edit-reply" onclick="editReply(<?php echo $review['id']; ?>, '<?php echo addslashes(htmlspecialchars($review['admin_reply'])); ?>')" title="ویرایش پاسخ">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="admin-reply-text"><?php echo nl2br(htmlspecialchars($review['admin_reply'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="review-card-footer">
                        <span class="review-date">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                            </svg>
                            <?php echo fullJalaliDate($review['created_at']); ?>
                        </span>
                    </div>
                </div>

                <div class="review-card-actions">
                    <a href="../main/food-detail.php?id=<?php echo $review['food_id']; ?>" class="btn btn-outline-info" title="مشاهده غذا">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        مشاهده غذا
                    </a>
                    <button class="btn btn-outline-primary" onclick="openReplyModal(<?php echo $review['id']; ?>, '<?php echo addslashes($review['user_name']); ?>')" title="پاسخ به نظر">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                        </svg>
                        <?php echo !empty($review['admin_reply']) ? 'ویرایش پاسخ' : 'پاسخ'; ?>
                    </button>

                    <button class="btn btn-outline-danger"
                        onclick="deleteReview(<?php echo $review['id']; ?>, '<?php echo addslashes($review['user_name']); ?>')">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                        حذف
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state-full">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:#94a3b8;stroke-width:1.5;">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
            </svg>
            <h3>هیچ نظری یافت نشد</h3>
            <p><?php echo !empty($search) || $rating_filter > 0 ? 'نظری با این شرایط پیدا نشد.' : 'هنوز نظری ثبت نشده.'; ?></p>
        </div>
    <?php endif; ?>
</div>

<?php if ($total_pages > 1): ?>
    <div class="pagination-row">
        <div class="pagination-info">
            نمایش <?php echo min(($page - 1) * $per_page + 1, $total_reviews); ?>
            تا <?php echo min($page * $per_page, $total_reviews); ?>
            از <?php echo number_format($total_reviews); ?> نظر
        </div>
        <div class="pagination">
            <?php
            $query_params = array_filter($_GET);
            unset($query_params['page']);
            $base_url = 'reviews.php?' . http_build_query($query_params);
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

<div class="modal" id="replyModal">
    <div class="modal-overlay" onclick="closeReplyModal()"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>پاسخ به نظر</h3>
            <button class="modal-close" onclick="closeReplyModal()">&times;</button>
        </div>
        <form id="replyForm" method="POST" action="save-reply.php">
            <div class="modal-body">
                <p style="margin-bottom:12px;">پاسخ به نظر <strong id="replyUserName"></strong></p>
                <textarea name="admin_reply" id="replyText" rows="4" placeholder="پاسخ خود را بنویسید..." required></textarea>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="review_id" id="replyReviewId">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="button" class="btn btn-light" onclick="closeReplyModal()">انصراف</button>
                <button type="submit" class="btn btn-primary" style="background:#3b82f6;color:#fff;border-color:#3b82f6;">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill:none;stroke:currentColor;stroke-width:2;width:16px;height:16px;">
                        <line x1="22" y1="2" x2="11" y2="13" />
                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                    ارسال پاسخ
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="deleteModal">
    <div class="modal-overlay" onclick="closeDeleteModal()"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>تأیید حذف نظر</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>آیا از حذف نظر <strong id="deleteReviewUser"></strong> اطمینان دارید؟</p>
            <p style="font-size:12px;color:#ef4444;">این عملیات قابل بازگشت نیست.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-light" onclick="closeDeleteModal()">انصراف</button>
            <a href="#" id="deleteConfirmBtn" class="btn btn-danger">حذف نظر</a>
        </div>
    </div>
</div>

<script>
    const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token']; ?>';
</script>
<script src="../assets/js/admin_reviews.js"></script>

<?php require_once '../includes/footer_admin.php'; ?>