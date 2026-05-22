-- ElderCare database schema (import in phpMyAdmin or MySQL CLI)
CREATE DATABASE IF NOT EXISTS eldercare_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eldercare_db;

DROP TABLE IF EXISTS health_records;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS caregiver_elders;
DROP TABLE IF EXISTS elders;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'caregiver', 'elderly') NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE elders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    date_of_birth DATE DEFAULT NULL,
    address TEXT,
    ec_name VARCHAR(255) DEFAULT NULL,
    ec_phone VARCHAR(50) DEFAULT NULL,
    ec_relationship VARCHAR(100) DEFAULT NULL,
    blood_type VARCHAR(20) DEFAULT NULL,
    allergies TEXT,
    medications TEXT,
    conditions TEXT,
    health_status ENUM('stable', 'monitoring', 'critical', 'recovering') NOT NULL DEFAULT 'stable',
    assigned_caregiver_id INT DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)
        REFERENCES users (id)
        ON DELETE CASCADE,
    FOREIGN KEY (assigned_caregiver_id)
        REFERENCES users (id)
        ON DELETE SET NULL
);

CREATE TABLE caregiver_elders (
    caregiver_id INT NOT NULL,
    elder_id INT NOT NULL,
    PRIMARY KEY (caregiver_id , elder_id),
    FOREIGN KEY (caregiver_id)
        REFERENCES users (id)
        ON DELETE CASCADE,
    FOREIGN KEY (elder_id)
        REFERENCES elders (id)
        ON DELETE CASCADE
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    elder_id INT NOT NULL,
    caregiver_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    appt_date DATE NOT NULL,
    appt_time VARCHAR(50) NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') NOT NULL DEFAULT 'scheduled',
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (elder_id)
        REFERENCES elders (id)
        ON DELETE CASCADE,
    FOREIGN KEY (caregiver_id)
        REFERENCES users (id)
        ON DELETE SET NULL,
    FOREIGN KEY (created_by)
        REFERENCES users (id)
        ON DELETE SET NULL
);

CREATE TABLE health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    elder_id INT NOT NULL,
    blood_pressure VARCHAR(20) DEFAULT NULL,
    heart_rate INT DEFAULT NULL,
    temperature DECIMAL(5 , 2 ) DEFAULT NULL,
    weight DECIMAL(6 , 2 ) DEFAULT NULL,
    blood_sugar INT DEFAULT NULL,
    oxygen_level INT DEFAULT NULL,
    notes TEXT,
    recorded_by INT NOT NULL,
    record_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (elder_id)
        REFERENCES elders (id)
        ON DELETE CASCADE,
    FOREIGN KEY (recorded_by)
        REFERENCES users (id)
        ON DELETE CASCADE
);

CREATE INDEX idx_elders_caregiver ON elders(assigned_caregiver_id);
CREATE INDEX idx_appointments_elder ON appointments(elder_id);
CREATE INDEX idx_health_elder ON health_records(elder_id);
