<?php

/**
 * MedicalRecord.php - كلاس إدارة السجلات الطبية
 */

class MedicalRecord
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * إضافة سجل طبي جديد
     */
    public function create(int $patientId, int $docId, string $diagnosis, string $treatment, ?string $notes = null): bool
    {
        $sql = "INSERT INTO medical_records (patient_id, doctor_id, diagnosis, treatment, notes) 
                VALUES (:patient_id, :doctor_id, :diagnosis, :treatment, :notes)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'patient_id' => $patientId,
            'doctor_id' => $docId,
            'diagnosis' => $diagnosis,
            'treatment' => $treatment,
            'notes' => $notes
        ]);
    }

    /**
     * الحصول على سجل طبي محدد
     */
    public function getById(int $recordId): ?array
    {
        $sql = "SELECT mr.*, u.fullname as patient_name, d.fullname as doc_name 
                FROM medical_records mr 
                JOIN users u ON mr.patient_id = u.id 
                JOIN users d ON mr.doctor_id = d.id 
                WHERE mr.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $recordId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * الحصول على السجلات الطبية للمريض
     */
    public function getByPatientId(int $patientId, int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT mr.*, d.fullname as doc_name, d.phone as doc_phone 
                FROM medical_records mr 
                JOIN users d ON mr.doctor_id = d.id 
                WHERE mr.patient_id = :patient_id 
                ORDER BY mr.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':patient_id', $patientId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * الحصول على السجلات الطبية التي أنشأها طبيب معين
     */
    public function getByDocId(int $docId, int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT mr.*, u.fullname as patient_name 
                FROM medical_records mr 
                JOIN users u ON mr.patient_id = u.id 
                WHERE mr.doctor_id = :doctor_id 
                ORDER BY mr.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':doctor_id', $docId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * تحديث سجل طبي
     */
    public function update(int $recordId, string $diagnosis, string $treatment, ?string $notes = null): bool
    {
        $sql = "UPDATE medical_records SET diagnosis = :diagnosis, treatment = :treatment, notes = :notes, updated_at = NOW() 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $recordId,
            'diagnosis' => $diagnosis,
            'treatment' => $treatment,
            'notes' => $notes
        ]);
    }

    /**
     * حذف سجل طبي
     */
    public function delete(int $recordId): bool
    {
        $sql = "DELETE FROM medical_records WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $recordId]);
    }

    /**
     * عدد السجلات الطبية للمريض
     */
    public function countByPatientId(int $patientId): int
    {
        $sql = "SELECT COUNT(*) FROM medical_records WHERE patient_id = :patient_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * عدد السجلات الطبية التي أنشأها طبيب
     */
    public function countByDocId(int $docId): int
    {
        $sql = "SELECT COUNT(*) FROM medical_records WHERE doctor_id = :doctor_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['doctor_id' => $docId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * الحصول على جميع المرضى الذين عالجهم الطبيب
     */
    public function getPatientsForDoctor(int $docId): array
    {
        $sql = "SELECT DISTINCT u.id, u.fullname, u.email, u.phone, COUNT(mr.id) as records_count 
                FROM users u 
                JOIN medical_records mr ON u.id = mr.patient_id 
                WHERE mr.doctor_id = :doctor_id 
                GROUP BY u.id, u.fullname, u.email, u.phone 
                ORDER BY u.fullname";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['doctor_id' => $docId]);
        return $stmt->fetchAll();
    }
}
