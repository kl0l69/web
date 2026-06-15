<?php
// logout.php - تسجيل الخروج الآمن من النظام
require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/User.php';

// إذا كان هناك جلسة نشطة، نقوم بإنهاءها
if (User::isLoggedIn()) {
    User::logout();
}

// إعادة توجيه المستخدم لصفحة تسجيل الدخول
redirect('index.php');
?>
