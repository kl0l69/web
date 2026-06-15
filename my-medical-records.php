<?php

/**
 * my-medical-records.php - ملفي الطبي للمريض
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/User.php';
require_once __DIR__ . '/app/MedicalRecord.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!User::isLoggedIn() || !User::hasRole('patient')) {
    redirect('index.php');
}

$user_id = User::getUserId();
$fullname = User::getFullName();
$medicalRecord = new MedicalRecord($pdo);

// جلب السجلات الطبية
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$records = $medicalRecord->getByPatientId($user_id, $per_page, $offset);
$total_records = $medicalRecord->countByPatientId($user_id);
$total_pages = ceil($total_records / $per_page);

$detail_record = null;
if (isset($_GET['view'])) {
    $detail_record = $medicalRecord->getById((int)$_GET['view']);
    // حماية IDOR: تأكد أن السجل يخص المريض الحالي
    if ($detail_record && (int)$detail_record['patient_id'] !== $user_id) {
        $detail_record = null;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="استعراض سجلاتي الطبية وزياراتي - <?= e(APP_NAME) ?>">
    <title>ملفي الطبي - <?= e(APP_NAME) ?></title>
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
            <h2>ملفي الطبي</h2>

            <?php if ($detail_record): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h3><i class="fas fa-file-medical"></i> تفاصيل السجل الطبي</h3>
                    </div>

                    <div class="detail-row">
                        <div>
                            <strong>الطبيب المعالج:</strong>
                            <p><?= e($detail_record['doc_name']) ?></p>
                        </div>
                        <div>
                            <strong>تاريخ الزيارة:</strong>
                            <p><?= date('Y-m-d H:i', strtotime($detail_record['created_at'])) ?></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <strong>التشخيص:</strong>
                        <p class="text-box">
                            <?= nl2br(e($detail_record['diagnosis'])) ?>
                        </p>
                    </div>

                    <div class="form-group">
                        <strong>العلاج المقترح:</strong>
                        <p class="text-box">
                            <?= nl2br(e($detail_record['treatment'])) ?>
                        </p>
                    </div>

                    <?php if ($detail_record['notes']): ?>
                        <div class="form-group">
                            <strong>ملاحظات إضافية:</strong>
                            <p class="text-box">
                                <?= nl2br(e($detail_record['notes'])) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div>
                        <a href="my-medical-records.php" class="btn btn-primary">العودة للقائمة</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- قائمة السجلات -->
            <div class="container">
                <h3>قائمة الزيارات الطبية (<?= $total_records ?> زيارة)</h3>

                <?php if ($records): ?>
                    <div class="table-responsive">
                        <table class="records-table">
                            <thead>
                                <tr>
                                    <th>الطبيب</th>
                                    <th>التاريخ</th>
                                    <th>التشخيص</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?= e($record['doc_name']) ?></td>
                                        <td><?= date('Y-m-d', strtotime($record['created_at'])) ?></td>
                                        <td><?= e(substr($record['diagnosis'], 0, 40)) ?>...</td>
                                        <td>
                                            <a href="my-medical-records.php?view=<?= $record['id'] ?>" class="btn btn-secondary">
                                                <i class="fas fa-eye"></i> عرض
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
                            <a href="my-medical-records.php?page=<?= $page - 1 ?>" class="btn btn-secondary">السابق</a>
                        <?php endif; ?>
                        <span class="page-info">الصفحة <?= $page ?> من <?= $total_pages ?></span>
                        <?php if ($page < $total_pages): ?>
                            <a href="my-medical-records.php?page=<?= $page + 1 ?>" class="btn btn-secondary">التالي</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        لا توجد زيارات طبية مسجلة حتى الآن
                    </div>
                <?php endif; ?>
            </div>
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </section>
    </main>
</body>

</html>