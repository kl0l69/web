<?php

/**
 * users.php - إدارة المستخدمين (للمسؤول)
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/User.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!User::isLoggedIn() || !User::hasRole('admin')) {
    redirect('index.php');
}

$fullname = User::getFullName();
$success_msg = '';
$error_msg = '';

// معالجة حذف/تعطيل حساب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrf($_POST['csrf'] ?? '')) {
        $error_msg = '✗ طلب غير صالح (حماية CSRF).';
    } else {
        if ($_POST['action'] === 'delete_user') {
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id && $user_id != User::getUserId()) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                    if ($stmt->execute(['id' => $user_id])) {
                        $success_msg = '✓ تم حذف المستخدم بنجاح';
                    } else {
                        $error_msg = '✗ فشل في حذف المستخدم';
                    }
                } catch (Exception $e) {
                    $error_msg = '✗ خطأ: ' . $e->getMessage();
                }
            }
        }
    }
}

// جلب قائمة المستخدمين
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$users_query = $pdo->query("
    SELECT id, fullname, username, email, role, phone, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT $offset, $per_page
");
$users = $users_query->fetchAll();

$count_query = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $count_query->fetchColumn();
$total_pages = ceil($total_users / $per_page);

// إحصائيات
$stats_query = $pdo->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role");
$roles_stats = [];
while ($row = $stats_query->fetch()) {
    $roles_stats[$row['role']] = $row['cnt'];
}
$csrfToken = csrfToken();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="إدارة مستخدمي النظام - <?= e(APP_NAME) ?>">
    <title>إدارة المستخدمين - <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa+Ink:wght@400;700&family=Bebas+Neue&family=Boldonse&family=Fustat:wght@200..800&family=Macondo&family=Marhey:wght@300..700&family=Oswald:wght@200..700&family=Playpen+Sans+Arabic:wght@100..800&family=Revalia&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="main-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <section class="content">
            <h2>إدارة المستخدمين</h2>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= e($success_msg) ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-error"><?= e($error_msg) ?></div>
            <?php endif; ?>

            <!-- إحصائيات -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $total_users ?></div>
                        <div class="stat-label">إجمالي المستخدمين</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fas fa-user-shield"></i></div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $roles_stats['admin'] ?? 0 ?></div>
                        <div class="stat-label">المسؤولون</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-user-md"></i></div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $roles_stats['doc'] ?? 0 ?></div>
                        <div class="stat-label">الأطباء</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-hospital-user"></i></div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $roles_stats['patient'] ?? 0 ?></div>
                        <div class="stat-label">المرضى</div>
                    </div>
                </div>
            </div>

            <!-- جدول المستخدمين -->
            <div class="container">
                <h3>قائمة المستخدمين</h3>
                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>الاسم الكامل</th>
                                <th>اسم المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الهاتف</th>
                                <th>النوع</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= e($user['fullname']) ?></td>
                                    <td><?= e($user['username']) ?></td>
                                    <td><?= e($user['email']) ?></td>
                                    <td><?= e($user['phone'] ?? '-') ?></td>
                                    <td>
                                        <span class="role-badge role-<?= e($user['role']) ?>">
                                            <?php
                                            $role_names = ['admin' => 'مسؤول', 'doc' => 'طبيب', 'patient' => 'مريض'];
                                            echo $role_names[$user['role']] ?? $user['role'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['id'] != User::getUserId()): ?>
                                            <form method="POST" class="inline-form" onsubmit="return confirm('هل تأكد من حذف هذا المستخدم؟');">
                                                <input type="hidden" name="csrf" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?= e($user['id']) ?>">
                                                <button type="submit" class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="users.php?page=<?= $page - 1 ?>" class="btn btn-secondary">السابق</a>
                    <?php endif; ?>
                    <span class="page-info">الصفحة <?= $page ?> من <?= $total_pages ?></span>
                    <?php if ($page < $total_pages): ?>
                        <a href="users.php?page=<?= $page + 1 ?>" class="btn btn-secondary">التالي</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </section>
    </main>
</body>

</html>