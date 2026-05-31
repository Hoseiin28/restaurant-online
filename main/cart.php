<?php

$page_title = 'سبد خرید';
require_once '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $food_id = (int)($_POST['food_id'] ?? 0);

    switch ($action) {
        case 'update':
            $quantity = (int)$_POST['quantity'];
            if ($quantity > 0 && isset($_SESSION['cart'][$food_id])) {
                $_SESSION['cart'][$food_id]['quantity'] = $quantity;
                $_SESSION['cart_message'] = ['type' => 'success', 'text' => 'سبد خرید بروزرسانی شد.'];
            }
            break;

        case 'remove':
            if (isset($_SESSION['cart'][$food_id])) {
                $removed_name = $_SESSION['cart'][$food_id]['name'];
                unset($_SESSION['cart'][$food_id]);
                $_SESSION['cart_message'] = ['type' => 'info', 'text' => $removed_name . ' از سبد خرید حذف شد.'];
            }
            break;

        case 'clear':
            unset($_SESSION['cart']);
            $_SESSION['cart_message'] = ['type' => 'info', 'text' => 'سبد خرید خالی شد.'];
            break;
    }

    redirect(BASE_URL . 'main/cart.php');
}

$subtotal = 0;
$total_items = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $total_items += $item['quantity'];
    }
}

$delivery_cost = ($subtotal > 500000 || $subtotal === 0) ? 0 : 35000;
$final_total = $subtotal + $delivery_cost;

// تنظیم مسیر تصویر پیش‌فرض
$default_food_image = BASE_URL . 'assets/images/foods/default-food.jpg';
?>

<link rel="stylesheet" href="../assets/css/cart.css">

<?php if (isset($_SESSION['cart_message'])): ?>
    <div class="cart-toast cart-toast--<?php echo $_SESSION['cart_message']['type']; ?> cart-toast--visible" id="cartToast">
        <?php echo $_SESSION['cart_message']['text']; ?>
    </div>
    <?php unset($_SESSION['cart_message']); ?>
<?php endif; ?>

<div class="cart-page">
    <div class="container" style="max-width:var(--container);margin:0 auto;padding:0;">

        <div class="cart-header">
            <h1>سبد خرید</h1>
            <?php if ($total_items > 0): ?>
                <span class="cart-header__badge"><?php echo $total_items; ?> آیتم</span>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>

            <div class="cart-layout">

                <div class="cart-items-col">
                    <div class="cart-table">

                        <div class="cart-table__head">
                            <span>غذا</span>
                            <span>قیمت واحد</span>
                            <span>تعداد</span>
                            <span>جمع</span>
                            <span></span>
                        </div>

                        <?php foreach ($_SESSION['cart'] as $id => $item):
                            $item_total = $item['price'] * $item['quantity'];
                            $img_sql = "SELECT image FROM foods WHERE id = " . (int)$id;
                            $img_result = mysqli_query($conn, $img_sql);
                            $food_img = mysqli_fetch_assoc($img_result);

                            // بررسی وجود تصویر
                            $cart_image = $food_img['image'] ?? '';
                            $has_cart_image = !empty($cart_image) && file_exists(UPLOAD_DIR . $cart_image);
                            $display_cart_image = $has_cart_image ? UPLOAD_URL . $cart_image : $default_food_image;
                        ?>
                            <div class="cart-row">
                                <div class="cart-row__product">
                                    <div class="cart-row__img">
                                        <img src="<?php echo $display_cart_image; ?>"
                                            alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div>
                                        <p class="cart-row__name"><?php echo htmlspecialchars($item['name']); ?></p>
                                        <span class="cart-row__unit-price"><?php echo number_format($item['price']); ?> تومان</span>
                                    </div>
                                </div>

                                <div class="cart-row__price">
                                    <?php echo number_format($item['price']); ?> <span>تومان</span>
                                </div>

                                <div class="cart-row__qty">
                                    <form method="POST" action="" class="qty-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="food_id" value="<?php echo $id; ?>">
                                        <div class="qty-control">
                                            <button type="button" class="qty-control__btn" onclick="changeQty(this, -1)" aria-label="کاهش">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12">
                                                    <line x1="5" y1="12" x2="19" y2="12" />
                                                </svg>
                                            </button>
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="99" class="qty-control__input" readonly>
                                            <button type="button" class="qty-control__btn" onclick="changeQty(this, 1)" aria-label="افزایش">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12">
                                                    <line x1="12" y1="5" x2="12" y2="19" />
                                                    <line x1="5" y1="12" x2="19" y2="12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div class="cart-row__total">
                                    <?php echo number_format($item_total); ?> <span>تومان</span>
                                </div>

                                <div class="cart-row__remove">
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="food_id" value="<?php echo $id; ?>">
                                        <button type="submit" class="btn-remove" title="حذف آیتم">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                                <line x1="10" y1="11" x2="10" y2="17" />
                                                <line x1="14" y1="11" x2="14" y2="17" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="cart-table__footer">
                            <a href="menu.php" class="btn btn--outline">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                    <polyline points="15 18 9 12 15 6" />
                                </svg>
                                ادامه خرید
                            </a>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="btn btn--danger-ghost">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6" />
                                    </svg>
                                    خالی کردن سبد
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="cart-summary-col">
                    <div class="summary-card">
                        <h3 class="summary-card__title">خلاصه سفارش</h3>

                        <div class="summary-card__rows">
                            <div class="summary-card__row">
                                <span>جمع سبد خرید</span>
                                <span class="summary-card__row-value"><?php echo number_format($subtotal); ?> تومان</span>
                            </div>

                            <div class="summary-card__row">
                                <span>هزینه ارسال</span>
                                <?php if ($delivery_cost === 0): ?>
                                    <span class="summary-card__row-value--free">رایگان</span>
                                <?php else: ?>
                                    <span class="summary-card__row-value"><?php echo number_format($delivery_cost); ?> تومان</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($subtotal < 500000 && $subtotal > 0): ?>
                                <?php
                                $remaining = 500000 - $subtotal;
                                $progress_percent = min(($subtotal / 500000) * 100, 100);
                                ?>
                                <div class="delivery-progress">
                                    <span class="delivery-progress__text">
                                        فقط <strong><?php echo number_format($remaining); ?> تومان</strong> تا ارسال رایگان
                                    </span>
                                    <div class="delivery-progress__bar">
                                        <div class="delivery-progress__fill" style="width: <?php echo $progress_percent; ?>%"></div>
                                    </div>
                                    <span class="delivery-progress__percentage">
                                        <?php echo number_format($progress_percent, 0); ?>٪ تکمیل شده
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if ($delivery_cost === 0 && $subtotal > 0): ?>
                                <div class="delivery-progress delivery-progress--completed">
                                    <span class="delivery-progress__text" style="color: #2e7d32;">
                                        🎉 سفارش شما شامل ارسال رایگان است!
                                    </span>
                                    <div class="delivery-progress__bar">
                                        <div class="delivery-progress__fill" style="width: 100%"></div>
                                    </div>
                                    <span class="delivery-progress__percentage">100% تکمیل شده</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="summary-card__total">
                            <span>مبلغ قابل پرداخت</span>
                            <span class="summary-card__total-price"><?php echo number_format($final_total); ?> تومان</span>
                        </div>

                        <?php if (isLoggedIn()): ?>
                            <a href="checkout.php" class="btn btn--primary-fill btn--lg btn--block">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                    <rect x="1" y="3" width="15" height="13" />
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
                                    <circle cx="5.5" cy="18.5" r="2.5" />
                                    <circle cx="18.5" cy="18.5" r="2.5" />
                                </svg>
                                نهایی کردن خرید
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>authentication/login.php" class="btn btn--primary-fill btn--lg btn--block">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                    <polyline points="10 17 15 12 10 7" />
                                    <line x1="15" y1="12" x2="3" y2="12" />
                                </svg>
                                برای ادامه خرید وارد شوید
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="cart-features">
                        <div class="cart-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            تحویل سریع
                        </div>
                        <div class="cart-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            تضمین کیفیت
                        </div>
                        <div class="cart-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            پرداخت امن
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-cart">
                <div class="empty-cart__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                        <line x1="6" y1="6" x2="23" y2="6" />
                    </svg>
                </div>
                <h2>سبد خرید شما خالی است</h2>
                <p>هنوز هیچ غذایی به سبد خرید اضافه نکرده‌اید. منوی ما را ببینید و غذای مورد علاقه‌تان را سفارش دهید.</p>
                <a href="menu.php" class="btn btn--primary btn--lg">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <path d="M16 10a4 4 0 0 1-8 0" />
                    </svg>
                    مشاهده منو غذا
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="../assets/js/cart.js"></script>

<?php require_once '../includes/footer.php'; ?>