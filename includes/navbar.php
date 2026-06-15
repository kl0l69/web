<?php
// includes/navbar.php - شريط تنقل موحد قابل لإعادة الاستخدام
if (!class_exists('User')) {
    require_once __DIR__ . '/../app/User.php';
}
// حاول الحصول على بيانات المستخدم الحالية إن لم تكن معرفة
if (!isset($currentUser)) {
    $userObj = new User($pdo);
    $currentUser = $userObj->getCurrentUser();
}
$fullname = $currentUser['fullname'] ?? '';
$email = $currentUser['email'] ?? '';
$phone = $currentUser['phone'] ?? '';
// حساب اسم الدور للعرض (إذا لم يكن معرفاً مسبقاً)
if (!isset($display_role)) {
    $current_role = $currentUser['role'] ?? 'patient';
    $_nav_role_names = ['admin' => 'مسؤول النظام', 'doc' => 'طبيب معالج', 'patient' => 'مريض مسجل'];
    $display_role = $_nav_role_names[$current_role] ?? 'مستخدم';
    $role_class_attr = 'role-' . $current_role;
} else {
    $role_class_attr = 'role-patient'; // Default fallback
}
?>
<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand">
        <div class="icon-wrap">
            <i class="fas fa-hospital"></i>
        </div>
        <span><?= e(APP_NAME) ?></span>
    </a>

    <div class="navbar-end">
        <div class="contact-links">
            <?php if (!empty($phone)): ?>
                <a href="tel:<?= e($phone) ?>" title="الهاتف" class="contact-item"><i class="fas fa-phone-alt"></i> <?= e($phone) ?></a>
            <?php endif; ?>
            <?php if (!empty($email)): ?>
                <a href="mailto:<?= e($email) ?>" title="البريد الإلكتروني" class="contact-item"><i class="fas fa-envelope"></i> <?= e($email) ?></a>
            <?php endif; ?>
        </div>

        <div class="user-pill">
            <div class="avatar"><i class="fas fa-user"></i></div>
            <div class="name"><?= e($fullname) ?></div>
            <div class="role-badge <?= e($role_class_attr) ?>"><?= e($display_role ?? '') ?></div>
        </div>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> <span>خروج</span></a>
    </div>
</nav>