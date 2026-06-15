<?php

/**
 * patients.php - قائمة المرضى المتابعين للطبيب
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/User.php';
require_once __DIR__ . '/app/MedicalRecord.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!User::isLoggedIn() || !User::hasRole('doc')) {
    redirect('index.php');
}

$user_id = User::getUserId();
$fullname = User::getFullName();
$medicalRecord = new MedicalRecord($pdo);

// جلب المرضى المتابعين
$patients = $medicalRecord->getPatientsForDoctor($user_id);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="المرضى المتابعين للأطباء في النظام - <?= e(APP_NAME) ?>">
    <title>المرضى المتابعين - <?= e(APP_NAME) ?></title>
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
            <h2>المرضى المتابعين (<?= count($patients) ?> مريض)</h2>

            <?php if ($patients): ?>
                <div class="container">
                    <div class="cards-grid">
                        <?php foreach ($patients as $patient): ?>
                            <div class="patient-card">
                                <h3>
                                    <i class="fas fa-user-circle"></i> <?= e($patient['fullname']) ?>
                                </h3>
                                <div class="patient-info">
                                    <strong>البريد:</strong> <?= e($patient['email']) ?>
                                </div>
                                <div class="patient-info">
                                    <strong>الهاتف:</strong> <?= e($patient['phone'] ?? 'غير محدد') ?>
                                </div>
                                <div class="patient-info">
                                    <strong>عدد السجلات:</strong> <?= (int)$patient['records_count'] ?>
                                </div>
                                <div class="patient-actions">
                                    <button class="btn btn-primary" onclick="viewPatientRecords(<?= $patient['id'] ?>)">
                                        <i class="fas fa-file-medical"></i> السجلات
                                    </button>
                                    <button class="btn btn-secondary" onclick="addRecordForPatient(<?= $patient['id'] ?>)">
                                        <i class="fas fa-plus"></i> إضافة
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>لا توجد مرضى متابعين حتى الآن</p>
                </div>
            <?php endif; ?>
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </section>
    </main>

    <script>
        function viewPatientRecords(patientId) {
            window.location.href = `medical-records.php?patient=${patientId}`;
        }

        function addRecordForPatient(patientId) {
            window.location.href = `medical-records.php?add_for=${patientId}`;
        }
    </script>
</body>

</html>