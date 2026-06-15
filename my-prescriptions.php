<?php

/**
 * my-prescriptions.php - وصفاتي الطبية
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/User.php';
require_once __DIR__ . '/app/Prescription.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!User::isLoggedIn() || !User::hasRole('patient')) {
    redirect('index.php');
}

$user_id = User::getUserId();
$fullname = User::getFullName();
$prescription = new Prescription($pdo);

// جلب الوصفات
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$prescriptions = $prescription->getByPatientId($user_id, $per_page, $offset);
$total_prescriptions = $prescription->countByPatientId($user_id);
$total_pages = ceil($total_prescriptions / $per_page);

$detail_prescription = null;
if (isset($_GET['view'])) {
    $detail_prescription = $prescription->getById((int)$_GET['view']);
    // حماية IDOR: تأكد أن الوصفة تخص المريض الحالي
    if ($detail_prescription && (int)$detail_prescription['patient_id'] !== $user_id) {
        $detail_prescription = null;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="استعراض الوصفات الطبية الخاصة بي والجرعات - <?= e(APP_NAME) ?>">
    <title>وصفاتي الطبية - <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap"
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="main-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <section class="content">
            <h2>وصفاتي الطبية</h2>

            <?php if ($detail_prescription): ?>
            <div class="detail-card">
                <div class="detail-header">
                    <h3><i class="fas fa-prescription-bottle"></i> تفاصيل الوصفة</h3>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <strong>الطبيب المعالج:</strong>
                        <p><?= e($detail_prescription['doc_name']) ?></p>
                    </div>
                    <div class="detail-item">
                        <strong>تاريخ الوصفة:</strong>
                        <p><?= date('Y-m-d', strtotime($detail_prescription['created_at'])) ?></p>
                    </div>
                    <div class="detail-item">
                        <strong>التشخيص:</strong>
                        <p><?= e($detail_prescription['diagnosis']) ?></p>
                    </div>
                    <div class="detail-item">
                        <strong>مدة العلاج:</strong>
                        <p><?= e($detail_prescription['duration']) ?></p>
                    </div>
                </div>

                <div class="medication-box">
                    <h3>
                        <i class="fas fa-pills"></i> <?= e($detail_prescription['medication']) ?>
                    </h3>
                    <p><strong>الجرعة:</strong> <?= e($detail_prescription['dosage']) ?></p>
                    <?php if ($detail_prescription['instructions']): ?>
                    <p><strong>التعليمات:</strong> <?= nl2br(e($detail_prescription['instructions'])) ?></p>
                    <?php endif; ?>
                </div>

                <div class="btn-group">
                    <a href="my-prescriptions.php" class="btn btn-primary">العودة للقائمة</a>
                    <button class="btn print-btn" onclick="window.print()">
                        <i class="fas fa-print"></i> طباعة
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- قائمة الوصفات -->
            <div class="container">
                <h3>قائمة الوصفات الطبية (<?= $total_prescriptions ?> وصفة)</h3>

                <?php if ($prescriptions): ?>
                <div class="table-responsive">
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>الدواء</th>
                                <th>الجرعة</th>
                                <th>المدة</th>
                                <th>الطبيب</th>
                                <th>التاريخ</th>
                                <th>الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptions as $presc): ?>
                            <tr>
                                <td><?= e($presc['medication']) ?></td>
                                <td><?= e($presc['dosage']) ?></td>
                                <td><?= e($presc['duration']) ?></td>
                                <td><?= e($presc['doc_name']) ?></td>
                                <td><?= date('Y-m-d', strtotime($presc['created_at'])) ?></td>
                                <td>
                                    <a href="my-prescriptions.php?view=<?= $presc['id'] ?>" class="btn btn-secondary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="my-prescriptions.php?page=<?= $page - 1 ?>" class="btn btn-secondary">السابق</a>
                    <?php endif; ?>
                    <span class="page-info">الصفحة <?= $page ?> من <?= $total_pages ?></span>
                    <?php if ($page < $total_pages): ?>
                    <a href="my-prescriptions.php?page=<?= $page + 1 ?>" class="btn btn-secondary">التالي</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    لا توجد وصفات طبية مسجلة حتى الآن
                </div>
                <?php endif; ?>
            </div>
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </section>
    </main>
</body>

</html>