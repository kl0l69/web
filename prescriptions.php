<?php

/**
 * prescriptions.php - إدارة الوصفات الطبية للطبيب
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/User.php';
require_once __DIR__ . '/app/MedicalRecord.php';
require_once __DIR__ . '/app/Prescription.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!User::isLoggedIn() || !User::hasRole('doc')) {
    redirect('index.php');
}

$user_id = User::getUserId();
$fullname = User::getFullName();
$prescription = new Prescription($pdo);
$medicalRecord = new MedicalRecord($pdo);

// معالجة إضافة وصفة
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrf($_POST['csrf'] ?? '')) {
        $error_msg = '✗ طلب غير صالح (حماية CSRF).';
    } else {
        if ($_POST['action'] === 'add_prescription') {
            $medical_record_id = (int)($_POST['medical_record_id'] ?? 0);
            $patient_id = (int)($_POST['patient_id'] ?? 0);
            $medication = trim($_POST['medication'] ?? '');
            $dosage = trim($_POST['dosage'] ?? '');
            $duration = trim($_POST['duration'] ?? '');
            $instructions = trim($_POST['instructions'] ?? '');

            if ($medical_record_id && $patient_id && $medication && $dosage && $duration) {
                if ($prescription->create($medical_record_id, $patient_id, $user_id, $medication, $dosage, $duration, $instructions ?: null)) {
                    $success_msg = '✓ تم إضافة الوصفة الطبية بنجاح';
                } else {
                    $error_msg = '✗ فشل في إضافة الوصفة';
                }
            } else {
                $error_msg = '✗ الرجاء ملء جميع الحقول المطلوبة';
            }
        }
    }
}

// جلب قائمة المرضى والسجلات
$patients_query = $pdo->query("SELECT id, fullname FROM users WHERE role = 'patient' ORDER BY fullname");
$patients = $patients_query->fetchAll();

// جلب الوصفات
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$prescriptions = $prescription->getByDocId($user_id, $per_page, $offset);
$total_prescriptions = $prescription->countByDocId($user_id);
$total_pages = ceil($total_prescriptions / $per_page);
$csrfToken = csrfToken();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="إدارة الوصفات الطبية - <?= e(APP_NAME) ?>">
    <title>إدارة الوصفات - <?= e(APP_NAME) ?></title>
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
            <h2>إدارة الوصفات الطبية</h2>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= e($success_msg) ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-error"><?= e($error_msg) ?></div>
            <?php endif; ?>

            <!-- نموذج إضافة وصفة -->
            <div class="form-container">
                <h3>إضافة وصفة طبية جديدة</h3>
                <form method="POST">
                    <input type="hidden" name="csrf" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="action" value="add_prescription">

                    <div class="grid-2">
                        <div class="form-group">
                            <label>المريض *</label>
                            <?php
                            $selected_patient = 0;
                            if (isset($_GET['patient'])) {
                                $selected_patient = (int)$_GET['patient'];
                            } elseif (isset($_GET['add_for'])) {
                                $selected_patient = (int)$_GET['add_for'];
                            }
                            ?>
                            <select name="patient_id" required onchange="loadPatientRecords(this.value)">
                                <option value="">-- اختر المريض --</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= e($patient['id']) ?>" <?= $selected_patient == $patient['id'] ? 'selected' : '' ?>><?= e($patient['fullname']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>السجل الطبي *</label>
                            <select name="medical_record_id" id="medical_record_id" required>
                                <option value="">-- اختر السجل الطبي --</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>الدواء *</label>
                        <input type="text" name="medication" required placeholder="اسم الدواء">
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label>الجرعة *</label>
                            <input type="text" name="dosage" required placeholder="مثال: قرص واحد ثلاث مرات يومياً">
                        </div>

                        <div class="form-group">
                            <label>مدة العلاج *</label>
                            <input type="text" name="duration" required placeholder="مثال: 10 أيام">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>تعليمات إضافية</label>
                        <textarea name="instructions" placeholder="مثال: تناول الدواء بعد الأكل مباشرة"></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">إضافة الوصفة</button>
                    </div>
                </form>
            </div>

            <!-- قائمة الوصفات -->
            <div class="form-container">
                <h3>الوصفات الطبية (<?= $total_prescriptions ?> وصفة)</h3>
                <?php if ($prescriptions): ?>
                    <div class="table-responsive">
                        <table class="records-table">
                            <thead>
                                <tr>
                                    <th>المريض</th>
                                    <th>الدواء</th>
                                    <th>الجرعة</th>
                                    <th>المدة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prescriptions as $presc): ?>
                                    <tr>
                                        <td><?= e($presc['patient_name']) ?></td>
                                        <td><?= e($presc['medication']) ?></td>
                                        <td><?= e($presc['dosage']) ?></td>
                                        <td><?= e($presc['duration']) ?></td>
                                        <td><?= date('Y-m-d', strtotime($presc['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="prescriptions.php?page=<?= $page - 1 ?>" class="btn btn-secondary">السابق</a>
                        <?php endif; ?>
                        <span class="page-info">الصفحة <?= $page ?> من <?= $total_pages ?></span>
                        <?php if ($page < $total_pages): ?>
                            <a href="prescriptions.php?page=<?= $page + 1 ?>" class="btn btn-secondary">التالي</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-prescription-bottle"></i>
                        <p>لا توجد وصفات طبية حتى الآن</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </section>
    </main>

    <script>
        function loadPatientRecords(patientId) {
            if (!patientId) {
                document.getElementById('medical_record_id').innerHTML = '<option value="">-- اختر السجل الطبي --</option>';
                return;
            }

            fetch('api/get-patient-records.php?patient_id=' + patientId)
                .then(r => r.json())
                .then(data => {
                    let html = '<option value="">-- اختر السجل الطبي --</option>';
                    data.forEach(record => {
                        html += `<option value="${record.id}">${record.diagnosis} (${record.created_at})</option>`;
                    });
                    document.getElementById('medical_record_id').innerHTML = html;
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const patientSelect = document.querySelector('select[name="patient_id"]');
            if (patientSelect && patientSelect.value) {
                loadPatientRecords(patientSelect.value);
            }
        });
    </script>
</body>

</html>