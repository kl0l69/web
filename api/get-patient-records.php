<?php

/**
 * api/get-patient-records.php - API لجلب السجلات الطبية للمريض
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/User.php';
require_once __DIR__ . '/../app/MedicalRecord.php';

header('Content-Type: application/json');

// التحقق من تسجيل الدخول والصلاحيات
if (!User::isLoggedIn() || !User::hasRole('doc')) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$patient_id = (int)($_GET['patient_id'] ?? 0);
if (!$patient_id) {
    echo json_encode([]);
    exit;
}

$medicalRecord = new MedicalRecord($pdo);
$records = $medicalRecord->getByPatientId($patient_id, 100, 0);

$data = [];
foreach ($records as $record) {
    $data[] = [
        'id' => $record['id'],
        'diagnosis' => $record['diagnosis'],
        'created_at' => date('Y-m-d', strtotime($record['created_at']))
    ];
}

echo json_encode($data);
