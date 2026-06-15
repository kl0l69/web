<?php
// includes/sidebar.php - شريط جانبي موحد حسب دور المستخدم
if (!isset($currentUser)) {
    $userObj = new User($pdo);
    $currentUser = $userObj->getCurrentUser();
}
$role = $currentUser['role'] ?? User::getRole();
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$sidebarItems = [];

if ($role === 'doc') {
    $sidebarItems = [
        ['href' => 'dashboard.php', 'icon' => 'fa-home', 'label' => 'لوحة التحكم'],
        ['href' => 'medical-records.php', 'icon' => 'fa-folder', 'label' => 'السجلات الطبية'],
        ['href' => 'prescriptions.php', 'icon' => 'fa-prescription-bottle', 'label' => 'الوصفات'],
        ['href' => 'patients.php', 'icon' => 'fa-users', 'label' => 'المرضى'],
        ['href' => 'news.php', 'icon' => 'fa-users', 'label' => 'فريق المشروع'],
    ];
} elseif ($role === 'patient') {
    $sidebarItems = [
        ['href' => 'dashboard.php', 'icon' => 'fa-home', 'label' => 'لوحة التحكم'],
        ['href' => 'my-medical-records.php', 'icon' => 'fa-file-medical', 'label' => 'ملفي الطبي'],
        ['href' => 'my-prescriptions.php', 'icon' => 'fa-prescription-bottle', 'label' => 'وصفاتي'],
        ['href' => 'contact-doctors.php', 'icon' => 'fa-phone', 'label' => 'الأطباء'],
        ['href' => 'news.php', 'icon' => 'fa-users', 'label' => 'فريق المشروع'],
    ];
} else {
    $sidebarItems = [
        ['href' => 'dashboard.php', 'icon' => 'fa-home', 'label' => 'لوحة التحكم'],
        ['href' => 'users.php', 'icon' => 'fa-users-cog', 'label' => 'المستخدمين'],
        ['href' => 'reports.php', 'icon' => 'fa-chart-pie', 'label' => 'التقارير'],
        ['href' => 'settings.php', 'icon' => 'fa-cogs', 'label' => 'الإعدادات'],
        ['href' => 'news.php', 'icon' => 'fa-users', 'label' => 'فريق المشروع'],
    ];
}
?>
<div class="sidebar">
    <?php foreach ($sidebarItems as $item): ?>
        <?php $activeClass = $item['href'] === $currentPage ? ' active' : ''; ?>
        <a href="<?= e($item['href']) ?>" class="sidebar-link<?= $activeClass ?>">
            <i class="fas <?= e($item['icon']) ?>"></i> <?= e($item['label']) ?>
        </a>
    <?php endforeach; ?>
</div>