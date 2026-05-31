<?php

$page_title = 'تسویه حساب';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    setErrorMessage('برای ادامه خرید باید وارد شوید.');
    redirect(BASE_URL . 'authentication/login.php');
}

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    setErrorMessage('سبد خرید شما خالی است.');
    redirect(BASE_URL . 'main/menu.php');
}

$subtotal = 0;
$total_items = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $total_items += $item['quantity'];
}

$free_delivery_threshold = 500000;
$delivery_cost = $subtotal > $free_delivery_threshold ? 0 : 35000;
$final_total = $subtotal + $delivery_cost;

$step = isset($_GET['step']) ? $_GET['step'] : 'review';
$payment_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $card_number = str_replace(' ', '', sanitize($_POST['card_number'] ?? ''));
    $payment_method = $_POST['payment_method'] ?? 'online';

    if ($payment_method === 'online' && (empty($card_number) || strlen($card_number) < 16)) {
        $payment_error = 'لطفاً شماره کارت معتبر وارد کنید (۱۶ رقم).';
    } else {
        $user_id = $_SESSION['user_id'];

        $order_sql = "INSERT INTO orders (user_id, total_price, status) 
                      VALUES ($user_id, $final_total, 'pending')";

        if (mysqli_query($conn, $order_sql)) {
            $order_id = mysqli_insert_id($conn);

            foreach ($_SESSION['cart'] as $food_id => $item) {
                $quantity = $item['quantity'];
                $price = $item['price'];
                $item_sql = "INSERT INTO order_items (order_id, food_id, quantity, price_per_unit) 
                            VALUES ($order_id, $food_id, $quantity, $price)";
                mysqli_query($conn, $item_sql);
            }

            unset($_SESSION['cart']);

            $_SESSION['order_success'] = [
                'order_id' => $order_id,
                'total' => $final_total,
                'method' => $payment_method,
                'time' => date('Y-m-d H:i:s')
            ];

            redirect(BASE_URL . 'main/order-success.php');
        } else {
            $payment_error = 'خطا در ثبت سفارش. لطفاً دوباره تلاش کنید.';
        }
    }
}

$user_sql = "SELECT * FROM users WHERE id = {$_SESSION['user_id']}";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);

$remaining_for_free = $free_delivery_threshold - $subtotal;
$progress_percent = ($subtotal > 0 && $subtotal < $free_delivery_threshold) ? min(($subtotal / $free_delivery_threshold) * 100, 100) : 0;
?>

<link rel="stylesheet" href="../assets/css/checkout.css">

<?php if (!empty($payment_error)): ?>
    <div class="checkout-toast checkout-toast--error checkout-toast--visible" id="checkoutToast">
        <?php echo $payment_error; ?>
    </div>
<?php endif; ?>

<div class="checkout-page">
    <div class="container" style="max-width:var(--container);margin:0 auto;padding:0;">

        <div class="checkout-steps">
            <div class="step <?php echo $step === 'review' ? 'step--active' : ($step === 'payment' ? 'step--done' : ''); ?>">
                <span class="step__circle">
                    <?php if ($step === 'payment'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="14" height="14">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    <?php else: ?>1<?php endif; ?>
                </span>
                <span class="step__label">بررسی سفارش</span>
            </div>
            <div class="step__connector <?php echo $step === 'payment' ? 'step__connector--done' : ''; ?>"></div>
            <div class="step <?php echo $step === 'payment' ? 'step--active' : ''; ?>">
                <span class="step__circle">2</span>
                <span class="step__label">پرداخت</span>
            </div>
        </div>

        <?php if ($step === 'review'): ?>
            <div class="checkout-layout">

                <div class="checkout-main">
                    <div class="card">
                        <h3 class="card__title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            اطلاعات تحویل
                        </h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-item__label">نام:</span>
                                <span class="info-item__value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-item__label">موبایل:</span>
                                <span class="info-item__value"><?php echo htmlspecialchars($user['phone'] ?: 'ثبت نشده'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-item__label">ایمیل:</span>
                                <span class="info-item__value"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <h3 class="card__title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1" />
                                <circle cx="20" cy="21" r="1" />
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                            </svg>
                            اقلام سفارش (<?php echo $total_items; ?> آیتم)
                        </h3>
                        <div class="order-items">
                            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                                <div class="order-item">
                                    <span>
                                        <span class="order-item__name"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="order-item__qty">× <?php echo $item['quantity']; ?></span>
                                    </span>
                                    <span class="order-item__price">
                                        <?php echo number_format($item['price'] * $item['quantity']); ?> تومان
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="checkout-sidebar">
                    <div class="card">
                        <h3 class="card__title">خلاصه پرداخت</h3>

                        <div class="summary-rows">
                            <div class="summary-row">
                                <span>جمع سفارش</span>
                                <span class="summary-row__value"><?php echo number_format($subtotal); ?> تومان</span>
                            </div>
                            <div class="summary-row">
                                <span>هزینه ارسال</span>
                                <?php if ($delivery_cost === 0): ?>
                                    <span class="summary-row__value--free">رایگان</span>
                                <?php else: ?>
                                    <span class="summary-row__value"><?php echo number_format($delivery_cost); ?> تومان</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($subtotal < $free_delivery_threshold && $subtotal > 0): ?>
                            <div class="delivery-progress">
                                <span class="delivery-progress__text">
                                    فقط <strong><?php echo number_format($remaining_for_free); ?> تومان</strong> تا ارسال رایگان
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

                        <div class="summary-total">
                            <span class="summary-total__label">مبلغ قابل پرداخت</span>
                            <span class="summary-total__amount"><?php echo number_format($final_total); ?> تومان</span>
                        </div>

                        <a href="checkout.php?step=payment" class="btn btn--primary btn--lg btn--block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                            ادامه به پرداخت
                        </a>

                        <a href="cart.php" class="btn btn--ghost btn--block" style="margin-top:8px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                            بازگشت به سبد خرید
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($step === 'payment'): ?>
            <div class="checkout-layout">

                <div class="checkout-main">
                    <div class="card">
                        <h3 class="card__title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            اطلاعات پرداخت
                        </h3>

                        <div class="payment-card-visual">
                            <div class="payment-card-visual__amount">مبلغ قابل پرداخت</div>
                            <div class="payment-card-visual__price"><?php echo number_format($final_total); ?> تومان</div>
                            <div class="payment-card-visual__secure">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                درگاه پرداخت امن
                            </div>
                        </div>

                        <form method="POST" action="" id="paymentForm">
                            <input type="hidden" name="process_payment" value="1">

                            <div class="payment-methods">
                                <label class="payment-method payment-method--active">
                                    <input type="radio" name="payment_method" value="online" checked>
                                    <div class="payment-method__icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="1" y="4" width="22" height="16" rx="2" />
                                            <line x1="1" y1="10" x2="23" y2="10" />
                                        </svg>
                                    </div>
                                    <span>پرداخت آنلاین (کارت بانکی)</span>
                                </label>
                            </div>

                            <div id="cardForm">
                                <div class="form-group">
                                    <label class="form-label">شماره کارت</label>
                                    <input type="text" name="card_number" class="form-input"
                                        placeholder="1234 5678 9012 3456" maxlength="19"
                                        oninput="formatCardNumber(this)" autocomplete="off">
                                </div>

                                <div class="form-row-2">
                                    <div class="form-group">
                                        <label class="form-label">تاریخ انقضا</label>
                                        <input type="text" class="form-input" placeholder="MM/YY" maxlength="5" autocomplete="off">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">CVV2</label>
                                        <input type="text" class="form-input" placeholder="123" maxlength="4" autocomplete="off">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">رمز پویا (اختیاری)</label>
                                    <input type="text" class="form-input" placeholder="رمز پویا" maxlength="8" autocomplete="off">
                                </div>
                            </div>

                            <button type="submit" class="btn btn--primary btn--lg btn--block" style="margin-bottom:10px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                    <line x1="12" y1="1" x2="12" y2="23" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                                پرداخت <?php echo number_format($final_total); ?> تومان
                            </button>

                            <div class="trust-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                اطلاعات پرداخت شما رمزنگاری و محفوظ است
                            </div>
                        </form>
                    </div>
                </div>

                <div class="checkout-sidebar">
                    <div class="card">
                        <h3 class="card__title">سفارش شما</h3>
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="order-item">
                                <span>
                                    <?php echo htmlspecialchars($item['name']); ?>
                                    <span class="order-item__qty">× <?php echo $item['quantity']; ?></span>
                                </span>
                                <span class="order-item__price">
                                    <?php echo number_format($item['price'] * $item['quantity']); ?> ت
                                </span>
                            </div>
                        <?php endforeach; ?>

                        <div class="summary-rows" style="margin-top: 12px; margin-bottom: 8px;">
                            <div class="summary-row">
                                <span>جمع سفارش</span>
                                <span class="summary-row__value"><?php echo number_format($subtotal); ?> تومان</span>
                            </div>
                            <div class="summary-row">
                                <span>هزینه ارسال</span>
                                <?php if ($delivery_cost === 0): ?>
                                    <span class="summary-row__value--free">رایگان</span>
                                <?php else: ?>
                                    <span class="summary-row__value"><?php echo number_format($delivery_cost); ?> تومان</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($subtotal < $free_delivery_threshold && $subtotal > 0): ?>
                            <div class="delivery-progress" style="margin: 8px 0 12px;">
                                <span class="delivery-progress__text">
                                    فقط <strong><?php echo number_format($remaining_for_free); ?> تومان</strong> تا ارسال رایگان
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
                            <div class="delivery-progress delivery-progress--completed" style="margin: 8px 0 12px;">
                                <span class="delivery-progress__text" style="color: #2e7d32;">
                                    🎉 سفارش شما شامل ارسال رایگان است!
                                </span>
                                <div class="delivery-progress__bar">
                                    <div class="delivery-progress__fill" style="width: 100%"></div>
                                </div>
                                <span class="delivery-progress__percentage">100% تکمیل شده</span>
                            </div>
                        <?php endif; ?>

                        <div class="summary-total" style="margin-bottom:0;">
                            <span class="summary-total__label">مبلغ قابل پرداخت</span>
                            <span class="summary-total__amount"><?php echo number_format($final_total); ?> تومان</span>
                        </div>
                    </div>

                    <a href="checkout.php?step=review" class="btn btn--ghost btn--block" style="margin-top:8px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                        بازگشت به مرحله قبل
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="../assets/js/checkout.js"></script>

<?php require_once '../includes/footer.php'; ?>