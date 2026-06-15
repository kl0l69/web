<?php

/**
 * dashboard.php - لوحة التحكم الرئيسية للنظام
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/User.php';

// التحقق من حالة تسجيل الدخول
if (!User::isLoggedIn()) {
    redirect('index.php');
}

$user_id = User::getUserId();
$username = User::getUsername();
$fullname = User::getFullName();
$role = User::getRole();

// جلب تفاصيل المستخدم من قاعدة البيانات
$userObj = new User($pdo);
$currentUser = $userObj->getCurrentUser();
$email = $currentUser['email'] ?? '';
$phone = $currentUser['phone'] ?? '';

// تحديد أسماء الأدوار باللغة العربية
$role_names = [
    'admin' => 'مسؤول النظام',
    'doc' => 'طبيب معالج',
    'patient' => 'مريض مسجل'
];
$display_role = $role_names[$role] ?? 'مستخدم';

// تهيئة الإحصائيات
$stats = [];
try {
    if ($role === 'admin') {
        // عدد المرضى
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'");
        $stats['patients_count'] = $stmt->fetchColumn();

        // عدد الأطباء
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'doc'");
        $stats['docs_count'] = $stmt->fetchColumn();

        // إجمالي السجلات الطبية
        $stmt = $pdo->query("SELECT COUNT(*) FROM medical_records");
        $stats['records_count'] = $stmt->fetchColumn();

        // إجمالي الوصفات الطبية
        $stmt = $pdo->query("SELECT COUNT(*) FROM prescriptions");
        $stats['prescriptions_count'] = $stmt->fetchColumn();
    } elseif ($role === 'doc') {
        // عدد المرضى المتابعين مع هذا الطبيب
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT patient_id) FROM medical_records WHERE doctor_id = :doctor_id");
        $stmt->execute(['doctor_id' => $user_id]);
        $stats['my_patients_count'] = $stmt->fetchColumn();

        // إجمالي السجلات التي كتبها هذا الطبيب
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM medical_records WHERE doctor_id = :doctor_id");
        $stmt->execute(['doctor_id' => $user_id]);
        $stats['my_records_count'] = $stmt->fetchColumn();

        // إجمالي الوصفات الطبية الصادرة عن هذا الطبيب
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM prescriptions WHERE doctor_id = :doctor_id");
        $stmt->execute(['doctor_id' => $user_id]);
        $stats['my_prescriptions_count'] = $stmt->fetchColumn();
    } elseif ($role === 'patient') {
        // السجلات الطبية للمريض
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM medical_records WHERE patient_id = :patient_id");
        $stmt->execute(['patient_id' => $user_id]);
        $stats['my_records_count'] = $stmt->fetchColumn();

        // الوصفات الطبية الصادرة للمريض
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM prescriptions WHERE patient_id = :patient_id");
        $stmt->execute(['patient_id' => $user_id]);
        $stats['my_prescriptions_count'] = $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    logError("Dashboard query error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="لوحة التحكم - <?= e(APP_NAME) ?>">
    <title>لوحة التحكم - <?= e(APP_NAME) ?></title>
    <!-- Google Fonts: English + Arabic fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <!-- مكتبة Font Awesome للأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- ملف التنسيق الخاص بلوحة التحكم -->
    <link href="assets/css/dashboard.css" rel="stylesheet">
</head>

<body>

    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="main-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <section class="content">
            <!-- Welcome Banner -->
            <section class="welcome-banner">
                <h1>مرحباً بك، <?= e($fullname) ?></h1>
                <p>مرحباً بك في نظام إدارة السجلات الطبية. يمكنك استعراض الإحصائيات والوصول للخدمات المتاحة أدناه.</p>
            </section>

            <!-- Stats Cards Grid -->
            <h2 class="section-title">إحصائيات النظام</h2>
            <div class="stats-grid">
                <?php if ($role === 'admin'): ?>
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)($stats['patients_count'] ?? 0) ?></div>
                        <div class="stat-label">إجمالي المرضى المسجلين</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)($stats['docs_count'] ?? 0) ?></div>
                        <div class="stat-label">إجمالي الأطباء المعتمدين</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)($stats['records_count'] ?? 0) ?></div>
                        <div class="stat-label">السجلات الطبية الموثقة</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-prescription-bottle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)($stats['prescriptions_count'] ?? 0) ?></div>
                        <div class="stat-label">الوصفات الطبية الصادرة</div>
                    </div>
                </div>

                <?php elseif ($role === 'doc'): ?>
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)($stats['my_patients_count'] ?? 0) ?></div>
                        <div class="stat-label">عدد المرضى المتابعين</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)($stats['my_records_count'] ?? 0) ?></div>
                        <div class="stat-label">السجلات المضافة من قبلي</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-file-prescription"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)($stats['my_prescriptions_count'] ?? 0) ?></div>
                        <div class="stat-label">الوصفات الصادرة من قبلي</div>
                    </div>
                </div>

                <?php elseif ($role === 'patient'): ?>
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)($stats['my_records_count'] ?? 0) ?></div>
                        <div class="stat-label">إجمالي الزيارات الطبية</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-pills"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-num"><?= (int)($stats['my_prescriptions_count'] ?? 0) ?></div>
                        <div class="stat-label">الوصفات الطبية الخاصة بي</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Cards Grid -->
            <h2 class="section-title">إجراءات النظام</h2>
            <div class="cards-grid">
                <?php if ($role === 'admin'): ?>
                <a href="users.php" class="action-card card-admin">
                    <div class="card-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3>إدارة المستخدمين</h3>
                    <p>إنشاء وتعديل وحذف حسابات الأطباء والمرضى والمسؤولين داخل النظام.</p>
                    <div class="card-link">
                        <i class="fas fa-arrow-left"></i> الدخول لإدارة الحسابات
                    </div>
                </a>
                <a href="reports.php" class="action-card card-admin">
                    <div class="card-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3>التقارير والإحصائيات</h3>
                    <p>استعراض الإحصائيات العامة والتقارير التحليلية لعمليات النظام.</p>
                    <div class="card-link">
                        <i class="fas fa-arrow-left"></i> عرض التقارير
                    </div>
                </a>
                <a href="settings.php" class="action-card card-admin">
                    <div class="card-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3>إعدادات النظام</h3>
                    <p>تهيئة خيارات الأمان وإعدادات قواعد البيانات العامة للنظام.</p>
                    <div class="card-link">
                        <i class="fas fa-arrow-left"></i> تهيئة الإعدادات
                    </div>
                </a>

                <?php elseif ($role === 'doc'): ?>
                <a href="medical-records.php" class="action-card card-doc">
                    <div class="card-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3>السجلات الطبية</h3>
                    <p>إدخال ومراجعة السجلات الطبية والتشخيصات المحددة للحالات الطبية.</p>
                    <div class="card-link">
                        <i class="fas fa-arrow-left"></i> إدارة السجلات الطبية
                    </div>
                </a>
                <a href="prescriptions.php" class="action-card card-doc">
                    <div class="card-icon">
                        <i class="fas fa-file-prescription"></i>
                    </div>
                    <h3>الوصفات الطبية</h3>
                    <p>إصدار وتعديل وصفات المرضى الدوائية والجرعات المعتمدة.</p>
                    <div class="card-link">
                        <i class="fas fa-arrow-left"></i> كتابة وصفة دوائية
                    </div>
                </a>
                <a href="patients.php" class="action-card card-doc">
                    <div class="card-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <h3>قائمة المرضى المتابعين</h3>
                    <p>استعراض قائمة بجميع المرضى المتابعين لتاريخهم الطبي والتشخيصي.</p>
                    <div class="card-link">
                        <i class="fas fa-arrow-left"></i> تصفح قائمة المرضى
                    </div>
                </a>

                <?php elseif ($role === 'patient'): ?>
                <a href="my-medical-records.php" class="action-card card-patient">
                    <div class="card-icon">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <h3>ملفي الطبي</h3>
                    <p>استعراض السجلات الطبية والتشخيصات السابقة المسجلة من قبل الأطباء.</p>
                    <div class="card-link">
                        <i class="fas fa-arrow-left"></i> استعراض السجل الطبي
                    </div>
                </a>
                <a href="my-prescriptions.php" class="action-card card-patient">
                    <div class="card-icon">
                        <i class="fas fa-prescription-bottle-alt"></i>
                    </div>
                    <h3>وصفاتي الطبية</h3>
                    <p>تصفح الوصفات الطبية الفعالة والأدوية والجرعات المقررة لك.</p>
                    <div class="card-link">
                        <i class="fas fa-arrow-left"></i> عرض تفاصيل الوصفات
                    </div>
                </a>
                <a href="contact-doctors.php" class="action-card card-patient">
                    <div class="card-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h3>الاتصال بالأطباء</h3>
                    <p>قنوات الاتصال المباشرة مع عيادات الأطباء المعالجين للاستفسارات.</p>
                    <div class="card-link">
                        <i class="fas fa-arrow-left"></i> تواصل مع طبيب
                    </div>
                </a>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </section>
    </main>

    <script src="assets/js/dashboard.js"></script>
</body>

</html>