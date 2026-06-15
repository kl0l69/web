<?php

/**
 * check_duplicate.php - ملف وسيط للتحقق من تكرار البريد الإلكتروني أو اسم المستخدم عبر AJAX
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/User.php';

header('Content-Type: application/json; charset=utf-8');

$username = trim($_GET['username'] ?? '');
$email = trim($_GET['email'] ?? '');

if (empty($username) && empty($email)) {
    echo json_encode(['username_taken' => false, 'email_taken' => false]);
    exit();
}

$user = new User($pdo);
$dup = $user->isDuplicate($username, $email);

echo json_encode([
    'username_taken' => $dup['username'],
    'email_taken'    => $dup['email']
]);
exit();
