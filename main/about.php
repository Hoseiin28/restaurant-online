<?php

$page_title = 'درباره ما';
require_once '../includes/header.php';

$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='user'"))['count'] ?? 0;
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='delivered'"))['count'] ?? 0;
$total_foods = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM foods WHERE is_available=1"))['count'] ?? 0;
$total_reviews = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews"))['count'] ?? 0;
$avg_rating = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg FROM reviews"))['avg'] ?? 0;
?>

<link rel="stylesheet" href="../assets/css/about.css">

<section class="about-hero">
    <div class="about-hero__content">
        <div class="about-hero__badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2z" />
            </svg>
            رستوران آنلاین
        </div>
        <h1>داستان ما از عشق به غذا شروع شد</h1>
        <p>از یک آشپزخانه کوچک تا محبوب‌ترین رستوران آنلاین شهر، مسیری که با عشق و کیفیت طی کردیم</p>
    </div>
</section>

<section class="about-section">
    <div class="about-container">
        <div class="story-layout">
            <div class="story-text">
                <h2>داستان رستوران آنلاین</h2>
                <span class="story-text__highlight">همه چیز از یک عشق ساده به آشپزی شروع شد...</span>
                <p>
                    سال ۱۳۹۸، در یک آشپزخانه کوچک در قلب تهران، <strong>رستوران آنلاین</strong> متولد شد.
                    هدف ما ساده بود: <em>رساندن غذای باکیفیت و خانگی به دست مردم با سریع‌ترین زمان ممکن.</em>
                </p>
                <p>
                    امروز با بیش از <strong><?php echo number_format($total_users); ?> مشتری راضی</strong>،
                    <strong><?php echo number_format($total_orders); ?> سفارش موفق</strong>
                    و تیمی از بهترین سرآشپزها، به یکی از محبوب‌ترین رستوران‌های آنلاین تبدیل شده‌ایم.
                </p>
            </div>
            <div class="story-visual">
                <div class="story-visual__inner">
                    <div class="story-visual__icon">
                        <svg viewBox="0 0 80 80" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="40" cy="40" r="32" />
                            <path d="M25 40C25 40 30 25 40 25C50 25 55 40 55 40C55 40 50 55 40 55C30 55 25 40 25 40Z" />
                            <circle cx="40" cy="40" r="5" fill="currentColor" />
                            <path d="M40 40L40 20" stroke-width="3" />
                            <path d="M28 32L40 22L52 32" stroke-width="2" />
                        </svg>
                    </div>
                    <span class="story-visual__text">از ۱۳۹۸ تا امروز</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="about-section">
    <div class="about-container">
        <div class="section__header">
            <h2>مسیر موفقیت ما</h2>
            <p>نگاهی به نقاط عطف مهم در مسیر رشد طعم‌خانه</p>
        </div>

        <div class="timeline">
            <div class="timeline-item">
                <span class="timeline-year">۱۳۹۸</span>
                <h3 class="timeline-title">شروع کار</h3>
                <p class="timeline-desc">تأسیس طعم‌خانه با یک آشپزخانه کوچک و ۵ پرسنل در مرکز تهران</p>
            </div>

            <div class="timeline-item">
                <span class="timeline-year">۱۳۹۹</span>
                <h3 class="timeline-title">راه‌اندازی پلتفرم آنلاین</h3>
                <p class="timeline-desc">راه‌اندازی وب‌سایت سفارش آنلاین و شروع تحویل درب منزل</p>
            </div>

            <div class="timeline-item">
                <span class="timeline-year">۱۴۰۰</span>
                <h3 class="timeline-title">۱۰۰۰ مشتری فعال</h3>
                <p class="timeline-desc">رسیدن به مرز ۱۰۰۰ مشتری فعال و گسترش منوی غذا به ۵۰ آیتم</p>
            </div>

            <div class="timeline-item">
                <span class="timeline-year">۱۴۰۱</span>
                <h3 class="timeline-title">تیم حرفه‌ای</h3>
                <p class="timeline-desc">جذب سرآشپزهای حرفه‌ای، افزایش تنوع غذایی و بهبود کیفیت</p>
            </div>

            <div class="timeline-item">
                <span class="timeline-year">۱۴۰۲</span>
                <h3 class="timeline-title">امروز</h3>
                <p class="timeline-desc">خدمت‌رسانی به <?php echo number_format($total_users); ?>+ مشتری با عشق و کیفیت بی‌نظیر</p>
            </div>
        </div>
    </div>
</section>

<section class="about-section">
    <div class="about-container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--users">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
                <div class="stat-card__number"><?php echo number_format($total_users); ?></div>
                <div class="stat-card__label">مشتری راضی</div>
            </div>

            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--orders">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                </div>
                <div class="stat-card__number"><?php echo number_format($total_orders); ?></div>
                <div class="stat-card__label">سفارش موفق</div>
            </div>

            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--foods">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                        <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                    </svg>
                </div>
                <div class="stat-card__number"><?php echo number_format($total_foods); ?></div>
                <div class="stat-card__label">غذای متنوع</div>
            </div>

            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--rating">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2z" />
                    </svg>
                </div>
                <div class="stat-card__number"><?php echo number_format($avg_rating, 1); ?></div>
                <div class="stat-card__label">میانگین امتیاز</div>
            </div>
        </div>
    </div>
</section>

<section class="about-section">
    <div class="about-container">
        <div class="section__header">
            <h2>ارزش‌های ما</h2>
            <p>اصولی که به آن‌ها پایبندیم و مسیرمان را تعریف می‌کنند</p>
        </div>

        <div class="values-grid">
            <div class="value-card">
                <div class="value-card__icon value-card__icon--quality">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </div>
                <h3>کیفیت بی‌نظیر</h3>
                <p>استفاده از تازه‌ترین و بهترین مواد اولیه</p>
            </div>

            <div class="value-card">
                <div class="value-card__icon value-card__icon--speed">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                </div>
                <h3>سرعت بالا</h3>
                <p>تحویل سفارش‌ها در کمتر از ۳۰ دقیقه</p>
            </div>

            <div class="value-card">
                <div class="value-card__icon value-card__icon--price">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23" />
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </div>
                <h3>قیمت منصفانه</h3>
                <p>غذاهای باکیفیت با قیمت‌های اقتصادی</p>
            </div>

            <div class="value-card">
                <div class="value-card__icon value-card__icon--service">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                    </svg>
                </div>
                <h3>مشتری‌مداری</h3>
                <p>رضایت مشتریان اولویت اول ماست</p>
            </div>
        </div>
    </div>
</section>

<section class="about-section">
    <div class="about-container">
        <div class="section__header">
            <h2>تیم ما</h2>
            <p>تیمی از بهترین متخصصان که با عشق و تعهد کار می‌کنند</p>
        </div>

        <div class="team-grid">
            <div class="team-card">
                <div class="team-card__avatar team-card__avatar--chef">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28" height="28">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <h3>علی محمدی</h3>
                <span class="team-card__role">سرآشپز ارشد</span>
                <p>۱۵ سال تجربه در آشپزی ایرانی و بین‌المللی</p>
            </div>

            <div class="team-card">
                <div class="team-card__avatar team-card__avatar--chef">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28" height="28">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <h3>سارا احمدی</h3>
                <span class="team-card__role">مدیر آشپزخانه</span>
                <p>متخصص غذاهای سنتی با ۱۰ سال سابقه</p>
            </div>

            <div class="team-card">
                <div class="team-card__avatar team-card__avatar--executive">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28" height="28">
                        <rect x="2" y="7" width="20" height="14" rx="2" />
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                    </svg>
                </div>
                <h3>رضا کریمی</h3>
                <span class="team-card__role">مدیر اجرایی</span>
                <p>برنامه‌ریزی و مدیریت زنجیره سفارش‌ها</p>
            </div>

            <div class="team-card">
                <div class="team-card__avatar team-card__avatar--support">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28" height="28">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg>
                </div>
                <h3>مریم حسینی</h3>
                <span class="team-card__role">پشتیبانی مشتریان</span>
                <p>پاسخگویی و همراهی ۲۴ ساعته مشتریان</p>
            </div>
        </div>
    </div>
</section>

<section class="about-section">
    <div class="about-container">
        <div class="contact-layout">
            <div class="contact-info">
                <h2>با ما در تماس باشید</h2>
                <p>هر سوال، پیشنهاد یا انتقادی دارید، خوشحال می‌شویم بشنویم</p>

                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                        </div>
                        <div>
                            <span class="contact-item__label">تلفن</span>
                            <span class="contact-item__value">۰۲۱-۱۲۳۴۵۶۷۸</span>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="2" width="14" height="20" rx="2" />
                                <line x1="12" y1="18" x2="12.01" y2="18" />
                            </svg>
                        </div>
                        <div>
                            <span class="contact-item__label">موبایل</span>
                            <span class="contact-item__value">۰۹۱۲۳۴۵۶۷۸۹</span>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg>
                        </div>
                        <div>
                            <span class="contact-item__label">ایمیل</span>
                            <span class="contact-item__value">info@restaurantonline.ir</span>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                        </div>
                        <div>
                            <span class="contact-item__label">ساعات کاری</span>
                            <span class="contact-item__value">همه روزه ۱۰ صبح تا ۱۲ شب</span>
                        </div>
                    </div>
                </div>

                <div class="contact-social">
                    <a href="#" class="social-btn social-btn--instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="2" y="2" width="20" height="20" rx="5" />
                            <circle cx="12" cy="12" r="5" />
                            <circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none" />
                        </svg>
                        اینستاگرام
                    </a>
                    <a href="#" class="social-btn social-btn--telegram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21.5 2.5L2.5 9.8L9.5 12.5" />
                            <path d="M9.5 12.5L12.5 21.5L21.5 2.5" />
                            <path d="M9.5 12.5L21.5 2.5" />
                        </svg>
                        تلگرام
                    </a>
                    <a href="#" class="social-btn social-btn--whatsapp">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                        </svg>
                        واتساپ
                    </a>
                </div>
            </div>

            <div class="contact-map">
                <div class="contact-map__header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    آدرس ما
                </div>
                <div class="contact-map__body">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                    <p>
                      استان همدان، شهر همدان، میدان بوعلی سینا <br>
                      رستوران آنلاین
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="about-cta">
    <div class="about-cta__content">
        <h2>آماده‌ای یه غذای عالی نوش جان کنی؟</h2>
        <p>همین حالا منوی ما رو ببین و غذای مورد علاقه‌ات رو سفارش بده</p>
        <a href="menu.php" class="btn btn--primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                <line x1="3" y1="6" x2="21" y2="6" />
            </svg>
            مشاهده منو و سفارش
        </a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>