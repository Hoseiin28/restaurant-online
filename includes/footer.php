</main>

<footer class="footer" id="contact">
    <div class="footer__top">
        <div class="container">
            <div class="footer__grid">
                <div class="footer__col footer__col--brand">
                    <div class="footer__brand">
                        <img
                            src="<?php echo BASE_URL; ?>assets/images/logo/logo.jpg"
                            alt="رستوران آنلاین"
                            class="footer__brand-img"
                            width="500"
                            height="465">
                        <div>
                            <span class="footer__brand-name">رستوران آنلاین</span>
                            <span class="footer__brand-tagline">طعم واقعی غذاهای خانگی</span>
                        </div>
                    </div>
                    <p class="footer__desc">
                        رستوران آنلاین با ارائه بهترین غذاهای ایرانی و فست‌فود با بالاترین کیفیت و قیمت مناسب، تجربه‌ای لذت‌بخش از سفارش آنلاین غذا را برای شما به ارمغان می‌آورد. ما متعهد به ارائه تازه‌ترین و باکیفیت‌ترین غذاها با سریع‌ترین زمان ارسال هستیم.
                    </p>
                    <div class="footer__social">
                        <a href="#" class="social-link" aria-label="اینستاگرام">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="2" y="2" width="20" height="20" rx="5" />
                                <circle cx="12" cy="12" r="5" />
                                <circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none" />
                            </svg>
                        </a>
                        <a href="#" class="social-link" aria-label="تلگرام">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21.5 2.5L2.5 9.8L9.5 12.5" />
                                <path d="M9.5 12.5L12.5 21.5L21.5 2.5" />
                                <path d="M9.5 12.5L21.5 2.5" />
                            </svg>
                        </a>
                        <a href="#" class="social-link" aria-label="واتساپ">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="footer__col">
                    <h4 class="footer__heading">دسترسی سریع</h4>
                    <ul class="footer__links">
                        <li><a href="<?php echo BASE_URL; ?>">صفحه اصلی</a></li>
                        <li><a href="<?php echo BASE_URL; ?>main/menu.php">منو غذا</a></li>
                        <li><a href="<?php echo BASE_URL; ?>main/about.php">درباره ما</a></li>
                        <li><a href="<?php echo BASE_URL; ?>main/cart.php">سبد خرید</a></li>
                        <li><a href="#contact">تماس با ما</a></li>
                    </ul>
                </div>

                <div class="footer__col">
                    <h4 class="footer__heading">حساب کاربری</h4>
                    <ul class="footer__links">
                        <?php if (isLoggedIn()): ?>
                            <?php if (isAdmin()): ?>
                                <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php">داشبورد ادمین</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo BASE_URL; ?>user/dashboard.php">داشبورد کاربری</a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo BASE_URL; ?>user/orders.php">سفارش‌های من</a></li>
                            <li><a href="<?php echo BASE_URL; ?>user/reviews.php">نظرات من</a></li>
                            <li><a href="<?php echo BASE_URL; ?>user/profile.php">ویرایش پروفایل</a></li>
                            <li><a href="<?php echo BASE_URL; ?>authentication/logout.php">خروج از حساب</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo BASE_URL; ?>authentication/login.php">ورود به حساب</a></li>
                            <li><a href="<?php echo BASE_URL; ?>authentication/signup.php">ثبت‌نام</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="footer__col">
                    <h4 class="footer__heading">تماس با ما</h4>
                    <ul class="footer__contact-list">
                        <li class="footer__contact-item">
                            <svg class="icon icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span>تهران، خیابان ولیعصر، نبش کوچه باغ</span>
                        </li>
                        <li class="footer__contact-item">
                            <svg class="icon icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                            <span>۰۲۱-۱۲۳۴۵۶۷۸</span>
                        </li>
                        <li class="footer__contact-item">
                            <svg class="icon icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg>
                            <span>info@restaurantonline.ir</span>
                        </li>
                        <li class="footer__contact-item">
                            <svg class="icon icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span>همه روزه ۱۰ صبح تا ۱۱ شب</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer__bottom">
        <div class="container">
            <div class="footer__bottom-inner">
                <p class="footer__copy">
                    &copy; <?php echo toJalali(date('Y-m-d H:i:s'), 'Y'); ?> - تمامی حقوق برای <strong>رستوران آنلاین</strong> محفوظ است.
                </p>
                <div class="footer__bottom-links">
                    <a href="#">قوانین و مقررات</a>
                    <span>|</span>
                    <a href="#">حریم خصوصی</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<button class="scroll-top" id="scrollTop" aria-label="بازگشت به بالا" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="18 15 12 9 6 15" />
    </svg>
</button>

<script src="<?php echo BASE_URL; ?>assets/js/header.js"></script>

</body>

</html>