<?php

/**
 * helpers.php - الدوال المساعدة العامة للنظام
 */

require_once __DIR__ . '/config.php';

/**
 * إعادة توجيه المستخدم لصفحة محددة
 */
function redirect(string $url, int $code = 302): void
{
    header("Location: $url", true, $code);
    exit();
}

/**
 * التحقق من أن نوع الطلب هو POST
 */
function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * توليد رمز حماية CSRF وتخزينه في الجلسة
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * التحقق من صحة رمز CSRF
 */
function validateCsrf(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * تطهير النصوص لمنع ثغرات XSS
 * يقبل أي نوع ويعيد تمثيل نصي آمن. قيمة null تُرجع سلسلة فارغة.
 */
function e(mixed $value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * تسجيل الأخطاء البرمجية في ملف السجل الخاص بالنظام
 */
function logError(string $message): void
{
    $logFile = __DIR__ . '/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
