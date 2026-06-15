<?php

/**
 * index.php - صفحة تسجيل الدخول الرئيسية للنظام
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/User.php';

if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// توجيه المستخدم الموثق تلقائياً إلى لوحة التحكم
if (User::isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
if (isPost()) {
    // التحقق من رمز CSRF لمنع الهجمات الموجهة
    if (!validateCsrf($_POST['csrf'] ?? '')) {
        $error = 'طلب غير صالح (حماية CSRF).';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'يرجى ملء كافة الحقول المطلوبة.';
        } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_]{3,100}$/', $username)) {
            $error = 'اسم المستخدم أو البريد الإلكتروني غير صالح.';
        } else {
            $user = new User($pdo);
            $role = $user->login($username, $password);
            if ($role) {
                redirect('dashboard.php');
            } else {
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
            }
        }
    }
}
$csrfToken = csrfToken();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="تسجيل الدخول - <?= e(APP_NAME) ?>">
    <title>تسجيل الدخول - <?= e(APP_NAME) ?></title>
    <!-- Google Fonts: English + Arabic fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <!-- مكتبة Font Awesome للأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- ملف التنسيق المخصص -->
    <link href="assets/css/main.css" rel="stylesheet">
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <div class="logo-icon">
                <i class="fas fa-key"></i>
            </div>
            <h1><?= e(APP_NAME) ?></h1>
            <p>نظام تسجيل الدخول الموحد</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate id="loginForm">
            <input type="hidden" name="csrf" value="<?= e($csrfToken) ?>">

            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> اسم المستخدم أو البريد الإلكتروني</label>
                <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم أو البريد الإلكتروني"
                    maxlength="255" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> كلمة المرور</label>
                <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" maxlength="255"
                    required>
            </div>

            <button type="submit" class="btn-primary" id="submitBtn">
                دخول
            </button>
        </form>

        <div class="login-footer">
            <p>ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد من هنا</a></p>
            <p class="copyright">© <?= date('Y') ?> <?= e(APP_NAME) ?></p>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>

</html>