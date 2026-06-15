<?php

/**
 * config.php - ملف إعدادات النظام
 */

// إعدادات الاتصال بقاعدة البيانات
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'national_health_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// إعدادات التطبيق العامة
define('APP_NAME', 'نظام إدارة السجلات الطبية');
define('APP_VERSION', '1.0');
define('SESSION_LIFETIME', 1800); // 30 دقيقة

// وضع التطوير (DEV_MODE) - يعرض تفاصيل الأخطاء عند تفعيله
define('DEV_MODE', true);

// الأدوار المعتمدة في النظام
define('ROLES', ['admin', 'doc', 'patient']);
