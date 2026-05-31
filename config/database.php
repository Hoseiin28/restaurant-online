<?php
// config/database.php

// ست کردن session فقط اگر هنوز شروع نشده
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // نام کاربری دیتابیس خودت
define('DB_PASS', '');            // رمز عبور دیتابیس خودت
define('DB_NAME', 'online_restaurant');

// ایجاد اتصال
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// بررسی اتصال
if (!$conn) {
    die("خطا در اتصال به پایگاه داده: " . mysqli_connect_error());
}

// تنظیم کاراکتر ست برای پشتیبانی کامل از فارسی
mysqli_set_charset($conn, "utf8mb4");

// تنظیم منطقه زمانی ایران
date_default_timezone_set('Asia/Tehran');

// ثابت‌های مسیر پروژه
define('BASE_URL', 'http://localhost/resturan-online/'); // آدرس پروژه رو تنظیم کن
define('ROOT_PATH', dirname(__FILE__) . '/../');
define('UPLOAD_DIR', ROOT_PATH . 'assets/images/foods/');
define('UPLOAD_URL', BASE_URL . 'assets/images/foods/');
?>