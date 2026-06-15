<?php

/**
 * reports.php - التقارير والإحصائيات (للمسؤول)
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

// إحصائيات عامة
$stats_query = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM users WHERE role='doc') as total_docs,
        (SELECT COUNT(*) FROM users WHERE role='patient') as total_patients,
        (SELECT COUNT(*) FROM medical_records) as total_records,
        (SELECT COUNT(*) FROM prescriptions) as total_prescriptions
");
$stats = $stats_query->fetch();

// آخر الأنشطة
$activities_query = $pdo->query("
    SELECT id, fullname, username, created_at, 'تسجيل حساب جديد' as action
    FROM users
    ORDER BY created_at DESC
    LIMIT 10
");
$activities = $activities_query->fetchAll();

// إحصائيات السجلات الطبية
$records_by_doc = $pdo->query("
    SELECT u.fullname, COUNT(mr.id) as records_count
    FROM users u
    LEFT JOIN medical_records mr ON u.id = mr.doctor_id
    WHERE u.role = 'doc'
    GROUP BY u.id, u.fullname
    ORDER BY records_count DESC
");
$docs_stats = $records_by_doc->fetchAll();

// إحصائيات الوصفات
$prescriptions_by_doc = $pdo->query("
    SELECT u.fullname, COUNT(p.id) as prescriptions_count
    FROM users u
    LEFT JOIN prescriptions p ON u.id = p.doctor_id
    WHERE u.role = 'doc'
    GROUP BY u.id, u.fullname
    ORDER BY prescriptions_count DESC
");
$prescriptions_stats = $prescriptions_by_doc->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقارير والإحصائيات - <?= e(APP_NAME) ?></title>
    <meta name="description" content="التقارير والإحصائيات - <?= e(APP_NAME) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="main-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <section class="content">
            <h2>التقارير والإحصائيات</h2>

            <!-- الإحصائيات الرئيسية -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)$stats['total_users'] ?></div>
                        <div class="stat-label">إجمالي المستخدمين</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-user-md"></i></div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)$stats['total_docs'] ?></div>
                        <div class="stat-label">الأطباء</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-hospital-user"></i></div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)$stats['total_patients'] ?></div>
                        <div class="stat-label">المرضى</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-file-medical"></i></div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)$stats['total_records'] ?></div>
                        <div class="stat-label">السجلات الطبية</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fas fa-pills"></i></div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)$stats['total_prescriptions'] ?></div>
                        <div class="stat-label">الوصفات الطبية</div>
                    </div>
                </div>
            </div>

            <!-- إحصائيات الأطباء -->
            <div class="container">
                <h3><i class="fas fa-chart-bar"></i> أداء الأطباء</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>اسم الطبيب</th>
                                <th>عدد السجلات</th>
                                <th>عدد الوصفات</th>
                                <th>النشاط</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $max_records = empty($docs_stats) ? 1 : (max(array_map(fn($r) => (int)$r['records_count'], $docs_stats)) ?: 1);
                            foreach ($docs_stats as $doc):
                            ?>
                            <tr>
                                <td><?= e($doc['fullname']) ?></td>
                                <td><?= (int)$doc['records_count'] ?></td>
                                <td>
                                    <?php
                                        $prescription_count = 0;
                                        foreach ($prescriptions_stats as $ps) {
                                            if ($ps['fullname'] === $doc['fullname']) {
                                                $prescription_count = (int)$ps['prescriptions_count'];
                                                break;
                                            }
                                        }
                                        echo $prescription_count;
                                        ?>
                                </td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill"
                                            style="width: <?= (((int)$doc['records_count'] / $max_records) * 100) ?>%">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- آخر الأنشطة -->
            <div class="container">
                <h3><i class="fas fa-history"></i> آخر الأنشطة</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>اسم المستخدم</th>
                                <th>الإجراء</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?= e($activity['fullname']) ?></td>
                                <td><?= e($activity['username']) ?></td>
                                <td><?= e($activity['action']) ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($activity['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </section>
    </main>
</body>

</html>