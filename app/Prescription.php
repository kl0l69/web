<?php

/**
 * Prescription.php - كلاس إدارة الوصفات الطبية
 */

class Prescription
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * إضافة وصفة طبية جديدة
     */
    public function create(int $medicalRecordId, int $patientId, int $docId, string $medication, string $dosage, string $duration, ?string $instructions = null): bool
    {
        $sql = "INSERT INTO prescriptions (medical_record_id, patient_id, doctor_id, medication, dosage, duration, instructions) 
                VALUES (:medical_record_id, :patient_id, :doctor_id, :medication, :dosage, :duration, :instructions)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'medical_record_id' => $medicalRecordId,
            'patient_id' => $patientId,
            'doctor_id' => $docId,
            'medication' => $medication,
            'dosage' => $dosage,
            'duration' => $duration,
            'instructions' => $instructions
        ]);
    }

    /**
     * الحصول على وصفة طبية محددة
     */
    public function getById(int $prescriptionId): ?array
    {
        $sql = "SELECT p.*, u.fullname as patient_name, d.fullname as doc_name, mr.diagnosis 
                FROM prescriptions p 
                JOIN users u ON p.patient_id = u.id 
                JOIN users d ON p.doctor_id = d.id 
                JOIN medical_records mr ON p.medical_record_id = mr.id
                WHERE p.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $prescriptionId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * الحصول على الوصفات الطبية للمريض
     */
    public function getByPatientId(int $patientId, int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT p.*, d.fullname as doc_name, d.phone as doc_phone, mr.diagnosis 
                FROM prescriptions p 
                JOIN users d ON p.doctor_id = d.id 
                JOIN medical_records mr ON p.medical_record_id = mr.id 
                WHERE p.patient_id = :patient_id 
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patient_id', $patientId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * الحصول على الوصفات الطبية التي أصدرها طبيب معين
     */
    public function getByDocId(int $docId, int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT p.*, u.fullname as patient_name, mr.diagnosis 
                FROM prescriptions p 
                JOIN users u ON p.patient_id = u.id 
                JOIN medical_records mr ON p.medical_record_id = mr.id 
                WHERE p.doctor_id = :doctor_id 
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':doctor_id', $docId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * تحديث وصفة طبية
     */
    public function update(int $prescriptionId, string $medication, string $dosage, string $duration, ?string $instructions = null): bool
    {
        $sql = "UPDATE prescriptions SET medication = :medication, dosage = :dosage, duration = :duration, instructions = :instructions, updated_at = NOW() 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $prescriptionId,
            'medication' => $medication,
            'dosage' => $dosage,
            'duration' => $duration,
            'instructions' => $instructions
        ]);
    }

    /**
     * حذف وصفة طبية
     */
    public function delete(int $prescriptionId): bool
    {
        $sql = "DELETE FROM prescriptions WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $prescriptionId]);
    }

    /**
     * عدد الوصفات الطبية للمريض
     */
    public function countByPatientId(int $patientId): int
    {
        $sql = "SELECT COUNT(*) FROM prescriptions WHERE patient_id = :patient_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * عدد الوصفات الطبية التي أصدرها طبيب
     */
    public function countByDocId(int $docId): int
    {
        $sql = "SELECT COUNT(*) FROM prescriptions WHERE doctor_id = :doctor_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['doctor_id' => $docId]);
        return (int)$stmt->fetchColumn();
    }
}
