<?php
/**
 * db.php - تهيئة اتصال قاعدة البيانات والجلسة الآمنة
 */

require_once __DIR__ . '/config.php';

// تهيئة وإعداد الجلسة الآمنة قبل البدء
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    session_start();
}

// التحقق من انتهاء مدة الجلسة تلقائياً
if (isset($_SESSION['last_act']) && (time() - $_SESSION['last_act']) > SESSION_LIFETIME) {
    $_SESSION = [];
    session_destroy();
    // أعد بدء الجلسة لعرض صفحة الدخول بدون أخطاء
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} else {
    // تحديث وقت آخر نشاط
    $_SESSION['last_act'] = time();
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('Database connection error: ' . $e->getMessage());
    if (DEV_MODE) {
        die('خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage());
    } else {
        die('خطأ في الاتصال بالخادم، يرجى المحاولة لاحقاً.');
    }
}
?>
