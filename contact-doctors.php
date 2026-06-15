<?php

/**
 * contact-doctors.php - الاتصال بالأطباء
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/User.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!User::isLoggedIn() || !User::hasRole('patient')) {
    redirect('index.php');
}

$user_id = User::getUserId();
$fullname = User::getFullName();


$doctors_query = $pdo->query("
    SELECT DISTINCT u.id, u.fullname, u.email, u.phone, COUNT(mr.id) as patients_count
    FROM users u
    LEFT JOIN medical_records mr ON u.id = mr.doctor_id
    WHERE u.role = 'doc'
    GROUP BY u.id, u.fullname, u.email, u.phone
    ORDER BY u.fullname
");
$doctors = $doctors_query->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="الاتصال والتواصل مع الأطباء - <?= e(APP_NAME) ?>">
    <title>الاتصال بالأطباء - <?= e(APP_NAME) ?></title>
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
            <h2>الاتصال بالأطباء</h2>

            <div class="container">
                <p class="info-message">
                    <i class="fas fa-info-circle"></i> اختر الطبيب وتواصل معه مباشرة عبر البريد الإلكتروني أو الهاتف
                </p>

                <?php if ($doctors): ?>
                    <div class="doctors-grid">
                        <?php foreach ($doctors as $doctor): ?>
                            <div class="doctor-card">
                                <div class="doctor-header">
                                    <div class="doctor-avatar">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <h3 class="doctor-name"><?= e($doctor['fullname']) ?></h3>
                                </div>

                                <div class="doctor-info">
                                    <strong><i class="fas fa-users"></i> المرضى المعالجين:</strong><br>
                                    <?= (int)$doctor['patients_count'] ?> مريض
                                </div>

                                <div class="doctor-info">
                                    <strong><i class="fas fa-envelope"></i> البريد الإلكتروني:</strong><br>
                                    <a href="mailto:<?= e($doctor['email']) ?>" class="email-link">
                                        <?= e($doctor['email']) ?>
                                    </a>
                                </div>

                                <div class="doctor-info">
                                    <strong><i class="fas fa-phone"></i> الهاتف:</strong><br>
                                    <?php if ($doctor['phone']): ?>
                                        <a href="tel:<?= e($doctor['phone']) ?>" class="email-link">
                                            <?= e($doctor['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">غير محدد</span>
                                    <?php endif; ?>
                                </div>

                                <div class="contact-buttons">
                                    <a href="mailto:<?= e($doctor['email']) ?>" class="btn btn-email btn-flex">
                                        <i class="fas fa-envelope"></i> بريد
                                    </a>
                                    <?php if ($doctor['phone']): ?>
                                        <a href="tel:<?= e($doctor['phone']) ?>" class="btn btn-phone btn-flex">
                                            <i class="fas fa-phone"></i> اتصال
                                        </a>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $doctor['phone']) ?>" target="_blank" class="btn btn-whatsapp btn-flex">
                                            <i class="fab fa-whatsapp"></i> واتساب
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        لا توجد أطباء متاحين حالياً
                    </div>
                <?php endif; ?>
            </div>
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </section>
    </main>
</body>

</html>