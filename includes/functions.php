<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================== توابع احراز هویت ====================

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        echo '<script>window.location.href = "' . $url . '";</script>';
        exit();
    }
}

function checkAccess($requiredRole = null) {
    error_log("checkAccess called - requiredRole: " . ($requiredRole ?? 'null'));
    error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
    error_log("Session role: " . ($_SESSION['role'] ?? 'not set'));
    error_log("isLoggedIn: " . (isLoggedIn() ? 'true' : 'false'));
    error_log("isAdmin: " . (isAdmin() ? 'true' : 'false'));
    
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'لطفاً ابتدا وارد حساب کاربری خود شوید.';
        redirect(BASE_URL . 'authentication/login.php');
    }
    
    if ($requiredRole === 'admin' && !isAdmin()) {
        $_SESSION['error'] = 'شما دسترسی به این بخش را ندارید.';
        redirect(BASE_URL . 'index.php');
    }
}

// ==================== توابع امنیتی ====================

function sanitize($data)
{
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return mysqli_real_escape_string($conn, $data);
}

function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token)
{
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    unset($_SESSION['csrf_token']);
    return true;
}

// ==================== توابع تاریخ شمسی ====================

function toJalali($gregorianDate, $format = 'Y/m/d')
{
    if (empty($gregorianDate) || $gregorianDate == '0000-00-00 00:00:00') {
        return '---';
    }

    $timestamp = strtotime($gregorianDate);
    $gYear = date('Y', $timestamp);
    $gMonth = date('m', $timestamp);
    $gDay = date('d', $timestamp);

    list($jYear, $jMonth, $jDay) = gregorian_to_jalali($gYear, $gMonth, $gDay);

    $hour = date('H', $timestamp);
    $minute = date('i', $timestamp);
    $second = date('s', $timestamp);

    $result = $format;
    $result = str_replace('Y', $jYear, $result);
    $result = str_replace('m', str_pad($jMonth, 2, '0', STR_PAD_LEFT), $result);
    $result = str_replace('d', str_pad($jDay, 2, '0', STR_PAD_LEFT), $result);
    $result = str_replace('H', $hour, $result);
    $result = str_replace('i', $minute, $result);
    $result = str_replace('s', $second, $result);

    return $result;
}

function nowJalali($format = 'Y/m/d H:i:s')
{
    return toJalali(date('Y-m-d H:i:s'), $format);
}

function jMonthName($monthNumber)
{
    $months = [
        1 => 'فروردین',
        2 => 'اردیبهشت',
        3 => 'خرداد',
        4 => 'تیر',
        5 => 'مرداد',
        6 => 'شهریور',
        7 => 'مهر',
        8 => 'آبان',
        9 => 'آذر',
        10 => 'دی',
        11 => 'بهمن',
        12 => 'اسفند'
    ];
    return isset($months[$monthNumber]) ? $months[$monthNumber] : '';
}

function jDayOfWeek($gregorianDate)
{
    $days = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'];
    $timestamp = strtotime($gregorianDate);
    $dayOfWeek = date('w', $timestamp);
    $jDayIndex = ($dayOfWeek + 1) % 7;
    return $days[$jDayIndex];
}

function fullJalaliDate($gregorianDate)
{
    if (empty($gregorianDate)) return '';
    $dayOfWeek = jDayOfWeek($gregorianDate);
    $jalali = toJalali($gregorianDate, 'Y/m/d');
    $parts = explode('/', $jalali);
    $monthName = jMonthName((int)$parts[1]);
    return $dayOfWeek . ' ' . $parts[2] . ' ' . $monthName . ' ' . $parts[0];
}

function toGregorian($jalaliDate)
{
    if (empty($jalaliDate)) return null;
    $parts = explode('/', $jalaliDate);
    if (count($parts) !== 3) return null;
    list($jYear, $jMonth, $jDay) = $parts;
    list($gYear, $gMonth, $gDay) = jalali_to_gregorian($jYear, $jMonth, $jDay);
    return sprintf('%04d-%02d-%02d', $gYear, $gMonth, $gDay);
}

function gregorian_to_jalali($gy, $gm, $gd)
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gy2 = ($gm > 2) ? $gy + 1 : $gy;
    $days = 355666 + (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * ((int)($days / 12053)));
    $days %= 12053;
    $jy += 4 * ((int)($days / 1461));
    $days %= 1461;
    if ($days > 365) {
        $jy += (int)(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    if ($days < 186) {
        $jm = 1 + (int)($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + (int)(($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }
    return [$jy, $jm, $jd];
}

function jalali_to_gregorian($jy, $jm, $jd)
{
    $jy += 1595;
    $days = -355668 + 365 * $jy + ((int)($jy / 33)) * 8 + ((int)((($jy % 33) + 3) / 4));
    $days += ($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186;
    $days += $jd;
    $gy = 400 * ((int)($days / 146097));
    $days %= 146097;
    if ($days > 36524) {
        $gy += 100 * ((int)(--$days / 36524));
        $days %= 36524;
        if ($days >= 365) $days++;
    }
    $gy += 4 * ((int)($days / 1461));
    $days %= 1461;
    if ($days > 365) {
        $gy += (int)(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    $gd = $days + 1;
    $sal_a = [0, 31, (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    for ($gm = 0; $gm < 13 && $gd > $sal_a[$gm]; $gm++) {
        $gd -= $sal_a[$gm];
    }
    return [$gy, $gm, $gd];
}

// ==================== توابع پیام ====================

function setSuccessMessage($message)
{
    $_SESSION['success'] = $message;
}
function setErrorMessage($message)
{
    $_SESSION['error'] = $message;
}

function showMessages()
{
    $output = '';
    if (isset($_SESSION['success'])) {
        $output .= '<div class="alert alert-success alert-dismissible">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        $output .= '<div class="alert alert-error alert-dismissible">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    return $output;
}

// ==================== توابع عمومی ====================

function getCartCount()
{
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

function formatPrice($price)
{
    return number_format($price, 0, '.', ',') . ' تومان';
}

function isFieldUnique($field, $value, $excludeId = null)
{
    global $conn;
    $sql = "SELECT id FROM users WHERE $field = '$value'";
    if ($excludeId) {
        $sql .= " AND id != $excludeId";
    }
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) === 0;
}

function uploadFile($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'])
{
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedTypes)) return false;
    $newFileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $targetPath = $targetDir . $newFileName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) return $newFileName;
    return false;
}

function excerpt($text, $length = 150)
{
    if (empty($text)) return '';
    
    $text = strip_tags($text);
    
    $text = trim($text);
    
    if (mb_strlen($text, 'UTF-8') <= $length) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    return htmlspecialchars(mb_substr($text, 0, $length, 'UTF-8'), ENT_QUOTES, 'UTF-8') . '...';
}