<?php

/**
 * medical-records.php - إدارة السجلات الطبية للطبيب
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

// معالجة إضافة سجل جديد
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrf($_POST['csrf'] ?? '')) {
        $error_msg = '✗ طلب غير صالح (حماية CSRF).';
    } else {
        if ($_POST['action'] === 'add_record') {
            $patient_id = (int)($_POST['patient_id'] ?? 0);
            $diagnosis = trim($_POST['diagnosis'] ?? '');
            $treatment = trim($_POST['treatment'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if ($patient_id && $diagnosis && $treatment) {
                if ($medicalRecord->create($patient_id, $user_id, $diagnosis, $treatment, $notes ?: null)) {
                    $success_msg = '✓ تم إضافة السجل الطبي بنجاح';
                } else {
                    $error_msg = '✗ فشل في إضافة السجل الطبي';
                }
            } else {
                $error_msg = '✗ الرجاء ملء جميع الحقول المطلوبة';
            }
        } elseif ($_POST['action'] === 'update_record') {
            $record_id = (int)($_POST['record_id'] ?? 0);
            $diagnosis = trim($_POST['diagnosis'] ?? '');
            $treatment = trim($_POST['treatment'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if ($record_id && $diagnosis && $treatment) {
                if ($medicalRecord->update($record_id, $diagnosis, $treatment, $notes ?: null)) {
                    $success_msg = '✓ تم تحديث السجل الطبي بنجاح';
                } else {
                    $error_msg = '✗ فشل في تحديث السجل الطبي';
                }
            } else {
                $error_msg = '✗ الرجاء ملء جميع الحقول المطلوبة';
            }
        }
    }
}

// جلب قائمة المرضى
$patients_query = $pdo->query("SELECT id, fullname, email FROM users WHERE role = 'patient' ORDER BY fullname");
$patients = $patients_query->fetchAll();

// جلب السجلات الطبية
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$filter_patient_id = (int)($_GET['patient'] ?? 0);
if ($filter_patient_id) {
    $sql = "SELECT mr.*, u.fullname as patient_name 
            FROM medical_records mr 
            JOIN users u ON mr.patient_id = u.id 
            WHERE mr.doctor_id = :doctor_id AND mr.patient_id = :patient_id
            ORDER BY mr.created_at DESC 
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':doctor_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':patient_id', $filter_patient_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll();

    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM medical_records WHERE doctor_id = :doctor_id AND patient_id = :patient_id");
    $count_stmt->execute(['doctor_id' => $user_id, 'patient_id' => $filter_patient_id]);
    $total_records = $count_stmt->fetchColumn();
} else {
    $records = $medicalRecord->getByDocId($user_id, $per_page, $offset);
    $total_records = $medicalRecord->countByDocId($user_id);
}
$total_pages = ceil($total_records / $per_page);

$edit_record = null;
if (isset($_GET['edit'])) {
    $edit_record = $medicalRecord->getById((int)$_GET['edit']);
}
$csrfToken = csrfToken();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="إدارة السجلات الطبية للمرضى - <?= e(APP_NAME) ?>">
    <title>إدارة السجلات الطبية - <?= e(APP_NAME) ?></title>
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
            <h2>إدارة السجلات الطبية</h2>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= e($success_msg) ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-error"><?= e($error_msg) ?></div>
            <?php endif; ?>

            <!-- نموذج إضافة/تعديل سجل -->
            <div class="form-container">
                <h3><?= $edit_record ? 'تعديل السجل الطبي' : 'إضافة سجل طبي جديد' ?></h3>
                <form method="POST">
                    <input type="hidden" name="csrf" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="action" value="<?= $edit_record ? 'update_record' : 'add_record' ?>">
                    <?php if ($edit_record): ?>
                        <input type="hidden" name="record_id" value="<?= e($edit_record['id']) ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>المريض</label>
                        <?php
                        $selected_patient = 0;
                        if ($edit_record) {
                            $selected_patient = $edit_record['patient_id'];
                        } elseif ($filter_patient_id) {
                            $selected_patient = $filter_patient_id;
                        } elseif (isset($_GET['add_for'])) {
                            $selected_patient = (int)$_GET['add_for'];
                        }
                        ?>
                        <select name="patient_id" required <?= $edit_record ? 'disabled' : '' ?>>
                            <option value="">-- اختر المريض --</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?= e($patient['id']) ?>" <?= $selected_patient == $patient['id'] ? 'selected' : '' ?>>
                                    <?= e($patient['fullname']) ?> (<?= e($patient['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>التشخيص</label>
                        <textarea name="diagnosis" required><?= $edit_record ? e($edit_record['diagnosis']) : '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>العلاج المقترح</label>
                        <textarea name="treatment" required><?= $edit_record ? e($edit_record['treatment']) : '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>ملاحظات إضافية</label>
                        <textarea name="notes"><?= $edit_record ? e($edit_record['notes'] ?? '') : '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><?= $edit_record ? 'تحديث السجل' : 'إضافة السجل' ?></button>
                        <?php if ($edit_record): ?>
                            <a href="medical-records.php" class="btn btn-secondary">إلغاء</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- قائمة السجلات -->
            <div class="form-container">
                <h3>السجلات الطبية (<?= $total_records ?> سجل)</h3>

                <?php if ($filter_patient_id): ?>
                    <div class="filter-alert">
                        <div>
                            <i class="fas fa-filter"></i> عرض السجلات الخاصة بالتعامل مع المريض: <strong><?= e($records[0]['patient_name'] ?? 'مريض') ?></strong>
                        </div>
                        <a href="medical-records.php" class="filter-clear"><i class="fas fa-times"></i> عرض الكل</a>
                    </div>
                <?php endif; ?>

                <?php if ($records): ?>
                    <div class="table-responsive">
                        <table class="records-table">
                            <thead>
                                <tr>
                                    <th>المريض</th>
                                    <th>التاريخ</th>
                                    <th>التشخيص</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?= e($record['patient_name']) ?></td>
                                        <td><?= date('Y-m-d', strtotime($record['created_at'])) ?></td>
                                        <td><?= e(substr($record['diagnosis'], 0, 50)) ?>...</td>
                                        <td>
                                            <a href="medical-records.php?edit=<?= $record['id'] ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php $patient_param = $filter_patient_id ? "&patient=" . $filter_patient_id : ""; ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="medical-records.php?page=<?= $page - 1 ?><?= $patient_param ?>" class="btn btn-secondary">السابق</a>
                        <?php endif; ?>
                        <span class="page-info">الصفحة <?= $page ?> من <?= $total_pages ?></span>
                        <?php if ($page < $total_pages): ?>
                            <a href="medical-records.php?page=<?= $page + 1 ?><?= $patient_param ?>" class="btn btn-secondary">التالي</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>لا توجد سجلات طبية حتى الآن</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </section>
    </main>
</body>

</html>