-- schema.sql - مخطط قاعدة البيانات والبيانات التجريبية
CREATE DATABASE IF NOT EXISTS national_health_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE national_health_db;

-- جدول المستخدمين
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doc', 'patient') NOT NULL DEFAULT 'patient',
    phone VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول السجلات الطبية
CREATE TABLE IF NOT EXISTS medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    diagnosis VARCHAR(500) NOT NULL,
    treatment VARCHAR(500) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_patient (patient_id),
    INDEX idx_doc (doctor_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الوصفات الطبية
CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medical_record_id INT NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    medication VARCHAR(255) NOT NULL,
    dosage VARCHAR(100) NOT NULL,
    duration VARCHAR(100) NOT NULL,
    instructions TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_patient (patient_id),
    INDEX idx_doc (doctor_id),
    INDEX idx_medical_record (medical_record_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدراج البيانات التجريبية للمستخدمين (كلمة المرور الافتراضية هي password مشفرة بـ BCRYPT)
INSERT INTO users (fullname, username, email, password, role, phone) VALUES
('محمد ', 'admin', 'admin@health.com', '$2y$10$E99G5cG0N0a9Z8aBvK/6UuD4aZQfP8xRzK1eZcVYH2mJ9rK5cN9qK', 'admin', ''),
('د. أحمد حمزة محمود', '7omosa', 'dr.ahmed@health.com', '$2y$10$E99G5cG0N0a9Z8aBvK/6UuD4aZQfP8xRzK1eZcVYH2mJ9rK5cN9qK', 'doc', '555-0002'),
('د. فاطمة عبد الرحمن', 'dr_fatima', 'dr.fatima@health.com', '$2y$10$E99G5cG0N0a9Z8aBvK/6UuD4aZQfP8xRzK1eZcVYH2mJ9rK5cN9qK', 'doc', '555-0003'),
('عادل محمود خالد', 'patient_001', 'patient1@health.com', '$2y$10$E99G5cG0N0a9Z8aBvK/6UuD4aZQfP8xRzK1eZcVYH2mJ9rK5cN9qK', 'patient', '555-0004'),
('منى يوسف حسن', 'patient_002', 'patient2@health.com', '$2y$10$E99G5cG0N0a9Z8aBvK/6UuD4aZQfP8xRzK1eZcVYH2mJ9rK5cN9qK', 'patient', '555-0005');
