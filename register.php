<?php

/**
 * register.php - صفحة إنشاء حساب جديد في النظام
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/User.php';

if (User::isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

$role = 'patient';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf($_POST['csrf'] ?? '')) {
        $error = 'طلب غير صالح (حماية CSRF).';
    } else {
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = trim($_POST['role'] ?? 'patient');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!in_array($role, ['patient', 'doc'], true)) {
            $role = 'patient';
        }

        if (empty($fullname) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'يرجى ملء جميع الحقول المطلوبة.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,100}$/', $username)) {
            $error = 'اسم المستخدم غير صالح (يجب أن يحتوي على أحرف وأرقام وشرطة سفلية فقط، ومن 3 إلى 100 حرف).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'البريد الإلكتروني المدخل غير صالح.';
        } elseif ($password !== $confirm_password) {
            $error = 'كلمتا المرور غير متطابقتين.';
        } elseif (strlen($password) < 6) {
            $error = 'يجب أن تكون كلمة المرور مكونة من 6 خانات على الأقل.';
        } else {
            $user = new User($pdo);
            if ($user->register($fullname, $username, $email, $password, $role, $phone)) {
                $success = 'تم إنشاء الحساب بنجاح. يمكنك الآن تسجيل الدخول.';
            } else {
                $error = 'اسم المستخدم أو البريد الإلكتروني مسجل بالفعل.';
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
    <meta name="description" content="إنشاء حساب جديد - <?= e(APP_NAME) ?>">
    <title>إنشاء حساب جديد - <?= e(APP_NAME) ?></title>
    <!-- Google Fonts: English + Arabic fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap"
    <!-- مكتبة Font Awesome للأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- التنسيق الرئيسي المخصص -->
    <link href="assets/css/main.css" rel="stylesheet">
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <div class="logo-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>إنشاء حساب جديد</h1>
            <p>قم بملء البيانات التالية للتسجيل في النظام</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <?= e($success) ?>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate id="registerForm">
            <input type="hidden" name="csrf" value="<?= e($csrfToken) ?>">

            <div class="form-group">
                <label for="fullname"><i class="fas fa-address-card"></i> الاسم الكامل</label>
                <input type="text" id="fullname" name="fullname" value="<?= e($fullname ?? '') ?>"
                    placeholder="أدخل الاسم الكامل" maxlength="255" required>
                <span class="error-feedback" id="fullnameFeedback"></span>
            </div>

            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> اسم المستخدم</label>
                <input type="text" id="username" name="username" value="<?= e($username ?? '') ?>"
                    placeholder="أدخل اسم المستخدم (أحرف وأرقام)" maxlength="100" required>
                <span class="error-feedback" id="usernameFeedback"></span>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="<?= e($email ?? '') ?>"
                    placeholder="example@domain.com" maxlength="255" required>
                <span class="error-feedback" id="emailFeedback"></span>
            </div>

            <div class="form-group">
                <label for="role"><i class="fas fa-user-tag"></i> نوع الحساب</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="patient" <?= ($role ?? 'patient') === 'patient' ? 'selected' : '' ?>>مريض</option>
                    <option value="doc" <?= ($role ?? '') === 'doc' ? 'selected' : '' ?>>طبيب</option>
                </select>
                <small class="form-help">اختر حساب مريض إذا كنت تستخدم التطبيق للمرضى، أو حساب طبيب إذا كنت
                    طبيباً.</small>
                <span class="error-feedback" id="roleFeedback"></span>
            </div>

            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> رقم الهاتف (اختياري)</label>
                <input type="tel" id="phone" name="phone" value="<?= e($phone ?? '') ?>"
                    placeholder="مثال: +201234567890" maxlength="20">
                <span class="error-feedback" id="phoneFeedback"></span>
            </div>

            <div class="form-group password-group">
                <label for="password"><i class="fas fa-lock"></i> كلمة المرور</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" maxlength="255"
                        required>
                    <button type="button" class="toggle-password" id="togglePasswordBtn">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <!-- مؤشر قوة كلمة المرور -->
                <div class="strength-meter">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <span class="strength-text" id="strengthText">قوة كلمة المرور</span>
            </div>

            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> تأكيد كلمة المرور</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="أعد إدخال كلمة المرور"
                    maxlength="255" required>
                <span class="error-feedback" id="confirmPasswordFeedback"></span>
            </div>

            <button type="submit" class="btn-primary" id="submitBtn">
                تسجيل الحساب
            </button>
        </form>

        <div class="login-footer">
            <p>لديك حساب بالفعل؟ <a href="index.php">تسجيل الدخول من هنا</a></p>
            <p class="copyright">© <?= date('Y') ?> <?= e(APP_NAME) ?></p>
        </div>
    </div>

    <script src="assets/js/register.js"></script>
</body>

</html>