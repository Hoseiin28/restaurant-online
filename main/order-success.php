<?php
$page_title = 'پرداخت موفق';
require_once '../includes/header.php';

$order_info = $_SESSION['order_success'] ?? null;

if (!$order_info) {
    setErrorMessage('اطلاعات سفارش یافت نشد.');
    redirect(BASE_URL . 'user/orders.php');
}

$order_id = $order_info['order_id'];
$order_total = $order_info['total'];
$payment_method = $order_info['method'];
$payment_time = $order_info['time'];

unset($_SESSION['order_success']);
?>

<link rel="stylesheet" href="../assets/css/order-success.css">

<div class="success-page">
    <div class="container">
        
        <div class="success-header">
            <div class="success-header__animation">
                <svg class="checkmark-animated" viewBox="0 0 52 52">
                    <circle class="checkmark-animated__circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-animated__check" fill="none" d="M14 27l7 7 16-16"/>
                </svg>
            </div>
            
            <h1 class="success-header__title">پرداخت با موفقیت انجام شد!</h1>
            <p class="success-header__subtitle">
                سفارش شما با موفقیت ثبت شد و در حال آماده‌سازی است.
            </p>
        </div>

        <div class="success-details-card">
            <div class="success-details-card__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                <span>جزئیات سفارش #<?php echo $order_id; ?></span>
            </div>
            
            <div class="success-details-card__body">
                <div class="detail-row">
                    <div class="detail-row__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="detail-row__content">
                        <span class="detail-row__label">شماره پیگیری</span>
                        <span class="detail-row__value"><?php echo 'ORD-' . str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-row__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <div class="detail-row__content">
                        <span class="detail-row__label">مبلغ پرداختی</span>
                        <span class="detail-row__value detail-row__value--price">
                            <?php echo number_format($order_total); ?> تومان
                        </span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-row__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                    </div>
                    <div class="detail-row__content">
                        <span class="detail-row__label">روش پرداخت</span>
                        <span class="detail-row__value">
                            <?php echo $payment_method === 'online' ? 'پرداخت آنلاین (کارت بانکی)' : 'پرداخت در محل'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-row__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div class="detail-row__content">
                        <span class="detail-row__label">تاریخ و زمان</span>
                        <span class="detail-row__value">
                            <?php 
                            echo function_exists('toJalali') ? toJalali($payment_time, 'Y/m/d - H:i') : $payment_time; 
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-row__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <div class="detail-row__content">
                        <span class="detail-row__label">وضعیت سفارش</span>
                        <span class="detail-row__value">
                            <span class="status-badge status-badge--pending">
                                <span class="status-badge__dot"></span>
                                در انتظار تأیید
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="success-info-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <p>
                سفارش شما پس از تأیید وارد فرایند آماده‌سازی و ارسال خواهد شد. 
                می‌توانید از طریق <strong>داشبورد کاربری > سفارش‌های من</strong> وضعیت سفارش خود را پیگیری کنید.
            </p>
        </div>

        <div class="success-actions">
            <a href="<?php echo BASE_URL; ?>user/orders.php" class="action-btn action-btn--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                پیگیری سفارش
            </a>
            
            <a href="<?php echo BASE_URL; ?>main/menu.php" class="action-btn action-btn--outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                منوی غذا
            </a>
            
            <a href="<?php echo BASE_URL; ?>" class="action-btn action-btn--ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                بازگشت به صفحه اصلی
            </a>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>