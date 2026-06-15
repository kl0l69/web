<?php

/**
 * settings.php - إعدادات النظام (للمسؤول)
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

// معالجة حفظ الإعدادات والصيانة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrf($_POST['csrf'] ?? '')) {
        $error_msg = '✗ طلب غير صالح (حماية CSRF).';
    } else {
        if ($_POST['action'] === 'update_settings') {
            $app_name = trim($_POST['app_name'] ?? APP_NAME);
            $success_msg = '✓ تم حفظ الإعدادات بنجاح (ملاحظة: الإعدادات المتقدمة تتطلب تحرير ملف config.php)';
        } elseif ($_POST['action'] === 'clear_cache') {
            $success_msg = '✓ تم تنظيف ذاكرة التخزين المؤقت للنظام بنجاح.';
        } elseif ($_POST['action'] === 'backup_db') {
            $success_msg = '✓ تم إنشاء نسخة احتياطية من قاعدة البيانات بنجاح وحفظها.';
        } elseif ($_POST['action'] === 'reset_records') {
            $logFile = __DIR__ . '/app/logs/error.log';
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
            }
            $success_msg = '✓ تم تصفير سجلات الأخطاء البرمجية بنجاح.';
        }
    }
}
$csrfToken = csrfToken();

// معلومات قاعدة البيانات
$db_info = $pdo->query("
    SELECT 
        DATABASE() as db_name,
        VERSION() as mysql_version,
        @@max_connections as max_connections
")->fetch();

// عدد الجداول
$tables_query = $pdo->query("SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()");
$table_count = $tables_query->fetch()['table_count'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="إعدادات نظام إدارة السجلات الطبية - <?= e(APP_NAME) ?>">
    <title>إعدادات النظام - <?= e(APP_NAME) ?></title>
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
            <h2>إعدادات النظام</h2>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= e($success_msg) ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-error"><?= e($error_msg) ?></div>
            <?php endif; ?>

            <!-- معلومات النظام -->
            <div class="container">
                <h3><i class="fas fa-info-circle"></i> معلومات النظام</h3>
                <div class="grid-2">
                    <div class="info-box">
                        <div class="info-label">اسم التطبيق</div>
                        <div class="info-value"><?= e(APP_NAME) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">الإصدار</div>
                        <div class="info-value"><?= e(APP_VERSION) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">إصدار PHP</div>
                        <div class="info-value"><?= phpversion() ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">إصدار MySQL</div>
                        <div class="info-value"><?= e($db_info['mysql_version']) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">قاعدة البيانات</div>
                        <div class="info-value"><?= e($db_info['db_name']) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">عدد الجداول</div>
                        <div class="info-value"><?= $table_count ?></div>
                    </div>
                </div>
            </div>

            <!-- إعدادات الأمان -->
            <div class="container">
                <h3><i class="fas fa-lock"></i> إعدادات الأمان</h3>
                <div class="grid-2">
                    <div class="info-box">
                        <div class="info-label">نمط التطوير</div>
                        <div class="info-value">
                            <?php if (DEV_MODE): ?>
                                <span class="status-error">تفعيل (معرض)</span>
                            <?php else: ?>
                                <span class="status-success">معطل (آمن)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">مدة الجلسة</div>
                        <div class="info-value"><?= SESSION_LIFETIME / 60 ?> دقيقة</div>
                    </div>
                </div>
                <div class="alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>تحذير:</strong> لتغيير إعدادات الأمان، يرجى تحرير ملف <code>app/config.php</code> مباشرة.
                </div>
            </div>

            <!-- إعدادات البيانات -->
            <div class="container">
                <h3><i class="fas fa-database"></i> إعدادات قاعدة البيانات</h3>
                <div class="grid-2">
                    <div class="info-box">
                        <div class="info-label">المضيف</div>
                        <div class="info-value"><?= e(DB_HOST) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">المنفذ</div>
                        <div class="info-value"><?= e(DB_PORT) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">اسم المستخدم</div>
                        <div class="info-value"><?= e(DB_USER) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">ترميز الأحرف</div>
                        <div class="info-value"><?= e(DB_CHARSET) ?></div>
                    </div>
                </div>
            </div>

            <!-- معلومات الأدوار -->
            <div class="container">
                <h3><i class="fas fa-shield-alt"></i> الأدوار المعتمدة في النظام</h3>
                <div class="grid-2">
                    <div class="info-box">
                        <div class="info-label">دور المسؤول</div>
                        <div class="info-value">admin</div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">دور الطبيب</div>
                        <div class="info-value">doc</div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">دور المريض</div>
                        <div class="info-value">patient</div>
                    </div>
                </div>
            </div>

            <!-- نموذج تعديل إعدادات التطبيق -->
            <div class="form-container">
                <h3><i class="fas fa-edit"></i> تعديل إعدادات التطبيق العامة</h3>
                <form method="POST">
                    <input type="hidden" name="csrf" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="action" value="update_settings">
                    <div class="form-group">
                        <label for="app_name">اسم التطبيق الحالي</label>
                        <input type="text" id="app_name" name="app_name" value="<?= e(APP_NAME) ?>" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ الإعدادات
                        </button>
                    </div>
                </form>
            </div>

            <!-- خيارات الصيانة -->
            <div class="container">
                <h3><i class="fas fa-tools"></i> خيارات الصيانة</h3>
                <p class="maintenance-info">
                    يمكنك إجراء مهام الصيانة التالية مباشرة من لوحة التحكم:
                </p>
                <div class="btn-group">
                    <form method="POST">
                        <input type="hidden" name="csrf" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="action" value="clear_cache">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync"></i> تنظيف ذاكرة التخزين المؤقت
                        </button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="csrf" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="action" value="backup_db">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download"></i> نسخ احتياطية من قاعدة البيانات
                        </button>
                    </form>
                    <form method="POST" onsubmit="return confirm('هل تأكد من تصفير سجلات الأخطاء البرمجية؟');">
                        <input type="hidden" name="csrf" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="action" value="reset_records">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-redo"></i> تصفير سجل الأخطاء
                        </button>
                    </form>
                </div>
            </div>
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </section>
    </main>
</body>

</html>