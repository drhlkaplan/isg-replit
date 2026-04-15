SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS isg_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE isg_lms;

CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    is_system TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO roles (id, name, description, is_system) VALUES
(1, 'superadmin', 'Süper Yönetici', 1),
(2, 'admin', 'Eğitim Yöneticisi', 1),
(3, 'firm', 'Firma Yetkilisi', 1),
(4, 'student', 'Öğrenci / Kursiyer', 1),
(5, 'egitmen', 'Eğitmen / Eğitici', 1);

CREATE TABLE IF NOT EXISTS firms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    tax_number VARCHAR(20),
    contact_name VARCHAR(100),
    contact_email VARCHAR(150),
    contact_phone VARCHAR(20),
    address TEXT,
    status ENUM('active','passive') DEFAULT 'active',
    company_code VARCHAR(50) UNIQUE,
    logo_path VARCHAR(255),
    primary_color VARCHAR(7) DEFAULT '#005695',
    secondary_color VARCHAR(7) DEFAULT '#0072b5',
    header_title VARCHAR(150),
    kvkk_consent_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO firms (id, name, tax_number, contact_name, contact_email, company_code) VALUES
(1, 'Platform Yönetimi', '0000000000', 'Sistem Admin', 'admin@isg.local', 'PLATFORM');

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firm_id INT UNSIGNED DEFAULT 1,
    role_id INT UNSIGNED NOT NULL DEFAULT 4,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20),
    tc_identity_no VARCHAR(11),
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active','passive','pending') DEFAULT 'active',
    last_login_at DATETIME,
    locale VARCHAR(10) DEFAULT 'tr-TR',
    is_mfa_enabled TINYINT(1) DEFAULT 0,
    mfa_secret VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE SET NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE IF NOT EXISTS course_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    code VARCHAR(10) NOT NULL UNIQUE,
    color VARCHAR(7) DEFAULT '#007bff',
    refresh_period_years TINYINT UNSIGNED DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO course_categories (id, name, description, code, color, refresh_period_years) VALUES
(1, 'Az Tehlikeli', 'Az tehlikeli iş kolları için İSG eğitimi', 'AZ', '#28a745', 3),
(2, 'Tehlikeli', 'Tehlikeli iş kolları için İSG eğitimi', 'TE', '#ffc107', 2),
(3, 'Çok Tehlikeli', 'Çok tehlikeli iş kolları için İSG eğitimi', 'CT', '#dc3545', 1);

CREATE TABLE IF NOT EXISTS courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    parent_course_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    training_type ENUM('temel','tekrar') DEFAULT NULL,
    topic_type ENUM('paket','on_degerlendirme','genel','saglik','teknik','ise_ozgu','final_sinav') DEFAULT NULL,
    delivery_method ENUM('online','yuz_yuze','hibrit') DEFAULT 'online',
    workplace_variant VARCHAR(50) DEFAULT 'genel',
    sort_order SMALLINT UNSIGNED DEFAULT 0,
    duration_minutes INT UNSIGNED DEFAULT 60,
    completion_required TINYINT(1) DEFAULT 1,
    status ENUM('active','draft','archived') DEFAULT 'draft',
    thumbnail_path VARCHAR(255),
    start_date DATE NULL,
    end_date DATE NULL,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES course_categories(id),
    FOREIGN KEY (parent_course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS scorm_packages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL UNIQUE,
    scorm_version ENUM('1.2','2004') DEFAULT '1.2',
    package_path VARCHAR(255) NOT NULL,
    launch_url VARCHAR(500) NOT NULL,
    manifest_data JSON,
    title VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS enrollments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE,
    status ENUM('enrolled','in_progress','completed','failed') DEFAULT 'enrolled',
    progress_percent TINYINT UNSIGNED DEFAULT 0,
    completed_at DATETIME,
    last_activity DATETIME,
    is_locked TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_user_course (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS scorm_tracking (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    lesson_status VARCHAR(50) DEFAULT 'not attempted',
    completion_status VARCHAR(50) DEFAULT 'incomplete',
    success_status VARCHAR(50),
    score_raw DECIMAL(5,2),
    score_min DECIMAL(5,2),
    score_max DECIMAL(5,2),
    score_scaled DECIMAL(5,4),
    total_time VARCHAR(20) DEFAULT '0000:00:00.00',
    session_time VARCHAR(20),
    entry VARCHAR(20) DEFAULT 'ab-initio',
    suspend_data TEXT,
    location VARCHAR(255),
    objectives JSON,
    interactions JSON,
    learner_preferences JSON,
    tab_switch_count SMALLINT UNSIGNED DEFAULT 0,
    fast_forward_count SMALLINT UNSIGNED DEFAULT 0,
    low_quality_flag TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_course_scorm (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS exams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    exam_type ENUM('pre','final') NOT NULL DEFAULT 'final',
    duration_minutes INT UNSIGNED DEFAULT 30,
    pass_score TINYINT UNSIGNED DEFAULT 70,
    question_count TINYINT UNSIGNED DEFAULT 10,
    shuffle_questions TINYINT(1) DEFAULT 1,
    shuffle_answers TINYINT(1) DEFAULT 1,
    max_attempts TINYINT UNSIGNED DEFAULT 3,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_id INT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice','true_false') DEFAULT 'multiple_choice',
    points TINYINT UNSIGNED DEFAULT 1,
    order_num SMALLINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS question_options (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id INT UNSIGNED NOT NULL,
    option_text TEXT NOT NULL,
    is_correct TINYINT(1) DEFAULT 0,
    order_num TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS exam_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    exam_id INT UNSIGNED NOT NULL,
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    submitted_at DATETIME,
    score DECIMAL(5,2),
    is_passed TINYINT(1) DEFAULT 0,
    answers JSON,
    attempt_number TINYINT UNSIGNED DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS certificates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    cert_number VARCHAR(50) NOT NULL UNIQUE,
    issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATE,
    pdf_path VARCHAR(255),
    is_valid TINYINT(1) DEFAULT 1,
    UNIQUE KEY uq_user_course_cert (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT UNSIGNED,
    details JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(512),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS group_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INT UNSIGNED,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS group_key_courses (
    group_key_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (group_key_id, course_id),
    FOREIGN KEY (group_key_id) REFERENCES group_keys(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS group_key_usage (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_key_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_key_user (group_key_id, user_id),
    FOREIGN KEY (group_key_id) REFERENCES group_keys(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- İSG EĞİTİM PAKETLERİ — SEED VERİSİ
-- 6 standart paket (3 tehlike sınıfı × 2 eğitim türü)
-- 4 işyerine özgü varyant × 2 eğitim türü = 8 varyant paketi
-- Her pakette 6 modül: on_deg, genel, saglik, teknik, ise_ozgu, final
-- ============================================================

-- 1. Standart Paketler (parent_course_id NULL, topic_type='paket')
INSERT IGNORE INTO courses (id, category_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(1,  1, 'Az Tehlikeli İş Yerleri İçin Temel İSG Eğitim Paketi (8 Ders Saati)',    'Az tehlikeli işyerleri için 360 dakika temel İSG eğitim paketi.',       'temel',  'paket', 'online', 'genel', 1, 360, 'active'),
(2,  2, 'Tehlikeli İş Yerleri İçin Temel İSG Eğitim Paketi (12 Ders Saati)',      'Tehlikeli işyerleri için 540 dakika temel İSG eğitim paketi.',          'temel',  'paket', 'online', 'genel', 2, 540, 'active'),
(3,  3, 'Çok Tehlikeli İş Yerleri İçin Temel İSG Eğitim Paketi (16 Ders Saati)', 'Çok tehlikeli işyerleri için 720 dakika temel İSG eğitim paketi.',      'temel',  'paket', 'online', 'genel', 3, 720, 'active'),
(4,  1, 'Az Tehlikeli İş Yerleri İçin Tekrar İSG Eğitim Paketi (8 Ders Saati)',  'Az tehlikeli işyerleri için 360 dakika yenileme İSG eğitim paketi.',    'tekrar', 'paket', 'online', 'genel', 1, 360, 'active'),
(5,  2, 'Tehlikeli İş Yerleri İçin Tekrar İSG Eğitim Paketi (8 Ders Saati)',     'Tehlikeli işyerleri için 360 dakika yenileme İSG eğitim paketi.',       'tekrar', 'paket', 'online', 'genel', 2, 360, 'active'),
(6,  3, 'Çok Tehlikeli İş Yerleri İçin Tekrar İSG Eğitim Paketi (8 Ders Saati)','Çok tehlikeli işyerleri için 360 dakika yenileme İSG eğitim paketi.',  'tekrar', 'paket', 'online', 'genel', 3, 360, 'active');

-- 2. İşyerine Özgü Varyant Paketler
INSERT IGNORE INTO courses (id, category_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(7,  3, 'Gratis Depo — Çok Tehlikeli Temel İSG Eğitim Paketi (16 Ders Saati)',   'Gratis Depo işyerine özgü çok tehlikeli temel İSG paketi.',             'temel',  'paket', 'online', 'gratis_depo',   4, 720, 'active'),
(8,  3, 'Gratis Depo — Çok Tehlikeli Tekrar İSG Eğitim Paketi (8 Ders Saati)',   'Gratis Depo işyerine özgü çok tehlikeli tekrar İSG paketi.',            'tekrar', 'paket', 'online', 'gratis_depo',   4, 360, 'active'),
(9,  2, 'Beauty — Tehlikeli Temel İSG Eğitim Paketi (12 Ders Saati)',            'Beauty/Kozmetik işyerine özgü tehlikeli temel İSG paketi.',             'temel',  'paket', 'online', 'beauty',        5, 540, 'active'),
(10, 2, 'Beauty — Tehlikeli Tekrar İSG Eğitim Paketi (8 Ders Saati)',            'Beauty/Kozmetik işyerine özgü tehlikeli tekrar İSG paketi.',            'tekrar', 'paket', 'online', 'beauty',        5, 360, 'active'),
(11, 1, 'Gratis Mağaza — Az Tehlikeli Temel İSG Eğitim Paketi (8 Ders Saati)',  'Gratis Mağaza işyerine özgü az tehlikeli temel İSG paketi.',            'temel',  'paket', 'online', 'gratis_magaza', 6, 360, 'active'),
(12, 1, 'Gratis Mağaza — Az Tehlikeli Tekrar İSG Eğitim Paketi (8 Ders Saati)', 'Gratis Mağaza işyerine özgü az tehlikeli tekrar İSG paketi.',           'tekrar', 'paket', 'online', 'gratis_magaza', 6, 360, 'active'),
(13, 1, 'Mutfak — Az Tehlikeli Temel İSG Eğitim Paketi (8 Ders Saati)',         'Mutfak/Yemek servisi işyerine özgü az tehlikeli temel İSG paketi.',     'temel',  'paket', 'online', 'mutfak',        7, 360, 'active'),
(14, 1, 'Mutfak — Az Tehlikeli Tekrar İSG Eğitim Paketi (8 Ders Saati)',        'Mutfak/Yemek servisi işyerine özgü az tehlikeli tekrar İSG paketi.',    'tekrar', 'paket', 'online', 'mutfak',        7, 360, 'active');

-- ============================================================
-- MODÜLLER — Paket 1: Az Tehlikeli Temel (IDs 1001–1006)
-- NOTE: IDs 11-14 are used by workplace-variant packages; use 1001+ range for P1 modules.
-- ============================================================
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(1001, 1, 1, 'Az Tehlikeli İSG Ön Değerlendirme Sınavı',           'Ön bilgi ölçümü — başarı barajı yoktur.',                                       'temel', 'on_degerlendirme', 'online', 'genel', 1, 20,  'active'),
(1002, 1, 1, 'Az Tehlikeli İSG Genel Konular Eğitimi',             'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.', 'temel', 'genel',            'online', 'genel', 2, 45,  'active'),
(1003, 1, 1, 'Az Tehlikeli İSG Sağlık Konuları Eğitimi',           'Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                         'temel', 'saglik',           'online', 'genel', 3, 90,  'active'),
(1004, 1, 1, 'Az Tehlikeli İSG Teknik Konular Eğitimi',            'Kimyasal/fiziksel riskler, elle taşıma, yangın, elektrik, KKD.',                'temel', 'teknik',           'online', 'genel', 4, 135, 'active'),
(1005, 1, 1, 'Az Tehlikeli İSG İşe ve İşyerine Özgü Konular',     'Risk değerlendirmesi, acil durum, işyeri tehlikeleri. (2 ders saati)',           'temel', 'ise_ozgu',         'online', 'genel', 5, 90,  'active'),
(1006, 1, 1, 'Az Tehlikeli İSG Eğitimi Final Sınavı',              'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                              'temel', 'final_sinav',      'online', 'genel', 6, 30,  'active');

-- MODÜLLER — Paket 2: Tehlikeli Temel (IDs 21–26)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(21, 2, 2, 'Tehlikeli İSG Ön Değerlendirme Sınavı',              'Ön bilgi ölçümü — başarı barajı yoktur.',                                       'temel', 'on_degerlendirme', 'online',    'genel', 1, 20,  'active'),
(22, 2, 2, 'Tehlikeli İSG Genel Konular Eğitimi',                'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.', 'temel', 'genel',            'online',    'genel', 2, 90,  'active'),
(23, 2, 2, 'Tehlikeli İSG Sağlık Konuları Eğitimi',              'Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                         'temel', 'saglik',           'online',    'genel', 3, 90,  'active'),
(24, 2, 2, 'Tehlikeli İSG Teknik Konular Eğitimi',               'Kimyasal/fiziksel riskler, elle taşıma, yangın, elektrik, KKD, yüksek risk.',   'temel', 'teknik',           'online',    'genel', 4, 225, 'active'),
(25, 2, 2, 'Tehlikeli İSG İşe ve İşyerine Özgü Konular',         'Risk değerlendirmesi, işyeri tehlikeleri. ÖRGÜN — Yüz yüze zorunlu. (3 ders)', 'temel', 'ise_ozgu',         'yuz_yuze',  'genel', 5, 135, 'active'),
(26, 2, 2, 'Tehlikeli İSG Eğitimi Final Sınavı',                 'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                              'temel', 'final_sinav',      'online',    'genel', 6, 30,  'active');

-- MODÜLLER — Paket 3: Çok Tehlikeli Temel (IDs 31–36)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(31, 3, 3, 'Çok Tehlikeli İSG Ön Değerlendirme Sınavı',          'Ön bilgi ölçümü — başarı barajı yoktur.',                                       'temel', 'on_degerlendirme', 'online',    'genel', 1, 20,  'active'),
(32, 3, 3, 'Çok Tehlikeli İSG Genel Konular Eğitimi',            'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.', 'temel', 'genel',            'online',    'genel', 2, 90,  'active'),
(33, 3, 3, 'Çok Tehlikeli İSG Sağlık Konuları Eğitimi',          'Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                         'temel', 'saglik',           'online',    'genel', 3, 90,  'active'),
(34, 3, 3, 'Çok Tehlikeli İSG Teknik Konular Eğitimi',           'Kimyasal/fiziksel riskler, elle taşıma, yangın, elektrik, KKD, yüksek risk.',   'temel', 'teknik',           'online',    'genel', 4, 360, 'active'),
(35, 3, 3, 'Çok Tehlikeli İSG İşe ve İşyerine Özgü Konular',     'Risk değerlendirmesi, işyeri tehlikeleri. ÖRGÜN — Yüz yüze zorunlu. (4 ders)', 'temel', 'ise_ozgu',         'yuz_yuze',  'genel', 5, 180, 'active'),
(36, 3, 3, 'Çok Tehlikeli İSG Eğitimi Final Sınavı',             'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                              'temel', 'final_sinav',      'online',    'genel', 6, 30,  'active');

-- MODÜLLER — Paket 4: Az Tehlikeli Tekrar (IDs 41–46)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(41, 1, 4, 'Az Tehlikeli İSG Ön Değerlendirme Sınavı (Tekrar)',  'Ön bilgi ölçümü — başarı barajı yoktur.',                                       'tekrar', 'on_degerlendirme', 'online', 'genel', 1, 20,  'active'),
(42, 1, 4, 'Az Tehlikeli İSG Genel Konular Eğitimi (Tekrar)',    'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.', 'tekrar', 'genel',            'online', 'genel', 2, 45,  'active'),
(43, 1, 4, 'Az Tehlikeli İSG Sağlık Konuları Eğitimi (Tekrar)', 'Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                         'tekrar', 'saglik',           'online', 'genel', 3, 90,  'active'),
(44, 1, 4, 'Az Tehlikeli İSG Teknik Konular Eğitimi (Tekrar)',   'Kimyasal/fiziksel riskler, elle taşıma, yangın, elektrik, KKD.',                'tekrar', 'teknik',           'online', 'genel', 4, 135, 'active'),
(45, 1, 4, 'Az Tehlikeli İSG İşe ve İşyerine Özgü Konular (Tekrar)', 'Risk değerlendirmesi, acil durum, işyeri tehlikeleri. (2 ders saati)',      'tekrar', 'ise_ozgu',         'online', 'genel', 5, 90,  'active'),
(46, 1, 4, 'Az Tehlikeli İSG Final Sınavı (Tekrar)',             'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                              'tekrar', 'final_sinav',      'online', 'genel', 6, 30,  'active');

-- MODÜLLER — Paket 5: Tehlikeli Tekrar (IDs 51–56)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(51, 2, 5, 'Tehlikeli İSG Ön Değerlendirme Sınavı (Tekrar)',     'Ön bilgi ölçümü — başarı barajı yoktur.',                                       'tekrar', 'on_degerlendirme', 'online',    'genel', 1, 20,  'active'),
(52, 2, 5, 'Tehlikeli İSG Genel Konular Eğitimi (Tekrar)',       'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.', 'tekrar', 'genel',            'online',    'genel', 2, 45,  'active'),
(53, 2, 5, 'Tehlikeli İSG Sağlık Konuları Eğitimi (Tekrar)',     'Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                         'tekrar', 'saglik',           'online',    'genel', 3, 90,  'active'),
(54, 2, 5, 'Tehlikeli İSG Teknik Konular Eğitimi (Tekrar)',      'Kimyasal/fiziksel riskler, elle taşıma, yangın, elektrik.',                     'tekrar', 'teknik',           'online',    'genel', 4, 90,  'active'),
(55, 2, 5, 'Tehlikeli İSG İşe ve İşyerine Özgü Konular (Tekrar)','Risk değerlendirmesi, işyeri tehlikeleri. ÖRGÜN — Yüz yüze zorunlu. (3 ders)','tekrar', 'ise_ozgu',         'yuz_yuze',  'genel', 5, 135, 'active'),
(56, 2, 5, 'Tehlikeli İSG Final Sınavı (Tekrar)',                'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                              'tekrar', 'final_sinav',      'online',    'genel', 6, 30,  'active');

-- MODÜLLER — Paket 6: Çok Tehlikeli Tekrar (IDs 61–66)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(61, 3, 6, 'Çok Tehlikeli İSG Ön Değerlendirme Sınavı (Tekrar)','Ön bilgi ölçümü — başarı barajı yoktur.',                                        'tekrar', 'on_degerlendirme', 'online',    'genel', 1, 20,  'active'),
(62, 3, 6, 'Çok Tehlikeli İSG Genel Konular Eğitimi (Tekrar)',  'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.',  'tekrar', 'genel',            'online',    'genel', 2, 45,  'active'),
(63, 3, 6, 'Çok Tehlikeli İSG Sağlık Konuları Eğitimi (Tekrar)','Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                          'tekrar', 'saglik',           'online',    'genel', 3, 90,  'active'),
(64, 3, 6, 'Çok Tehlikeli İSG Teknik Konular Eğitimi (Tekrar)', 'Temel teknik konular özeti.',                                                     'tekrar', 'teknik',           'online',    'genel', 4, 45,  'active'),
(65, 3, 6, 'Çok Tehlikeli İSG İşe ve İşyerine Özgü Konular (Tekrar)','Risk değerlendirmesi, işyeri tehlikeleri. ÖRGÜN — Yüz yüze zorunlu. (4 ders)','tekrar','ise_ozgu',       'yuz_yuze',  'genel', 5, 180, 'active'),
(66, 3, 6, 'Çok Tehlikeli İSG Final Sınavı (Tekrar)',           'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                               'tekrar', 'final_sinav',      'online',    'genel', 6, 30,  'active');

-- MODÜLLER — Paket 7: Gratis Depo Çok Tehlikeli Temel (IDs 71–76)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(71, 3, 7, 'Gratis Depo — Çok Tehlikeli İSG Ön Değerlendirme Sınavı',    'Ön bilgi ölçümü.',                                                       'temel', 'on_degerlendirme', 'online',    'gratis_depo', 1, 20,  'active'),
(72, 3, 7, 'Gratis Depo — Çok Tehlikeli İSG Genel Konular',              'Çalışma mevzuatı, yasal haklar, işyeri temizliği.',                       'temel', 'genel',            'online',    'gratis_depo', 2, 90,  'active'),
(73, 3, 7, 'Gratis Depo — Çok Tehlikeli İSG Sağlık Konuları',            'Meslek hastalıkları, korunma, ilk yardım.',                               'temel', 'saglik',           'online',    'gratis_depo', 3, 90,  'active'),
(74, 3, 7, 'Gratis Depo — Çok Tehlikeli İSG Teknik Konular',             'Kimyasal/fiziksel riskler, KKD, ekipman güvenliği.',                      'temel', 'teknik',           'online',    'gratis_depo', 4, 360, 'active'),
(75, 3, 7, 'Gratis Depo — İşe ve İşyerine Özgü Konular',                 'Depo güvenliği, yüksekte çalışma, yangın, ekipman. ÖRGÜN (4 ders).',      'temel', 'ise_ozgu',         'yuz_yuze',  'gratis_depo', 5, 180, 'active'),
(76, 3, 7, 'Gratis Depo — Çok Tehlikeli İSG Final Sınavı',               'Final sınavı — min. 60 puan.',                                            'temel', 'final_sinav',      'online',    'gratis_depo', 6, 30,  'active');

-- MODÜLLER — Paket 8: Gratis Depo Çok Tehlikeli Tekrar (IDs 81–86)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(81, 3, 8, 'Gratis Depo — Çok Tehlikeli İSG Ön Değerlendirme (Tekrar)',  'Ön bilgi ölçümü.',                                                       'tekrar', 'on_degerlendirme', 'online',    'gratis_depo', 1, 20,  'active'),
(82, 3, 8, 'Gratis Depo — Çok Tehlikeli İSG Genel Konular (Tekrar)',     'Çalışma mevzuatı, yasal haklar.',                                         'tekrar', 'genel',            'online',    'gratis_depo', 2, 45,  'active'),
(83, 3, 8, 'Gratis Depo — Çok Tehlikeli İSG Sağlık Konuları (Tekrar)',   'Meslek hastalıkları, korunma, ilk yardım.',                               'tekrar', 'saglik',           'online',    'gratis_depo', 3, 90,  'active'),
(84, 3, 8, 'Gratis Depo — Çok Tehlikeli İSG Teknik Konular (Tekrar)',    'Temel teknik konular özeti.',                                             'tekrar', 'teknik',           'online',    'gratis_depo', 4, 45,  'active'),
(85, 3, 8, 'Gratis Depo — İşe ve İşyerine Özgü Konular (Tekrar)',        'Depo güvenliği, yüksekte çalışma, yangın, ekipman. ÖRGÜN (4 ders).',      'tekrar', 'ise_ozgu',         'yuz_yuze',  'gratis_depo', 5, 180, 'active'),
(86, 3, 8, 'Gratis Depo — Çok Tehlikeli İSG Final Sınavı (Tekrar)',      'Final sınavı — min. 60 puan.',                                            'tekrar', 'final_sinav',      'online',    'gratis_depo', 6, 30,  'active');

-- MODÜLLER — Paket 9: Beauty Tehlikeli Temel (IDs 91–96)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(91, 2, 9,  'Beauty — Tehlikeli İSG Ön Değerlendirme Sınavı',            'Ön bilgi ölçümü.',                                                        'temel', 'on_degerlendirme', 'online',    'beauty', 1, 20,  'active'),
(92, 2, 9,  'Beauty — Tehlikeli İSG Genel Konular',                      'Çalışma mevzuatı, yasal haklar, işyeri temizliği.',                        'temel', 'genel',            'online',    'beauty', 2, 90,  'active'),
(93, 2, 9,  'Beauty — Tehlikeli İSG Sağlık Konuları',                    'Meslek hastalıkları, korunma, ilk yardım.',                                'temel', 'saglik',           'online',    'beauty', 3, 90,  'active'),
(94, 2, 9,  'Beauty — Tehlikeli İSG Teknik Konular',                     'Kimyasal/fiziksel riskler, KKD, ekipman güvenliği.',                       'temel', 'teknik',           'online',    'beauty', 4, 225, 'active'),
(95, 2, 9,  'Beauty — İşe ve İşyerine Özgü Konular',                    'Hijyen, yangın, kimyasal/biyolojik etkenler, ekipman. ÖRGÜN (3 ders).',    'temel', 'ise_ozgu',         'yuz_yuze',  'beauty', 5, 135, 'active'),
(96, 2, 9,  'Beauty — Tehlikeli İSG Final Sınavı',                       'Final sınavı — min. 60 puan.',                                             'temel', 'final_sinav',      'online',    'beauty', 6, 30,  'active');

-- MODÜLLER — Paket 10: Beauty Tehlikeli Tekrar (IDs 101–106)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(101, 2, 10, 'Beauty — Tehlikeli İSG Ön Değerlendirme (Tekrar)', 'Ön bilgi ölçümü.',                                                               'tekrar', 'on_degerlendirme', 'online',    'beauty', 1, 20,  'active'),
(102, 2, 10, 'Beauty — Tehlikeli İSG Genel Konular (Tekrar)',    'Çalışma mevzuatı, yasal haklar.',                                                 'tekrar', 'genel',            'online',    'beauty', 2, 45,  'active'),
(103, 2, 10, 'Beauty — Tehlikeli İSG Sağlık Konuları (Tekrar)', 'Meslek hastalıkları, korunma, ilk yardım.',                                        'tekrar', 'saglik',           'online',    'beauty', 3, 90,  'active'),
(104, 2, 10, 'Beauty — Tehlikeli İSG Teknik Konular (Tekrar)',  'Kimyasal/fiziksel riskler, KKD.',                                                  'tekrar', 'teknik',           'online',    'beauty', 4, 90,  'active'),
(105, 2, 10, 'Beauty — İşe ve İşyerine Özgü Konular (Tekrar)', 'Hijyen, yangın, kimyasal/biyolojik etkenler, ekipman. ÖRGÜN (3 ders).',             'tekrar', 'ise_ozgu',         'yuz_yuze',  'beauty', 5, 135, 'active'),
(106, 2, 10, 'Beauty — Tehlikeli İSG Final Sınavı (Tekrar)',    'Final sınavı — min. 60 puan.',                                                     'tekrar', 'final_sinav',      'online',    'beauty', 6, 30,  'active');

-- MODÜLLER — Paket 11: Gratis Mağaza Az Tehlikeli Temel (IDs 111–116)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(111, 1, 11, 'Gratis Mağaza — Az Tehlikeli İSG Ön Değerlendirme',       'Ön bilgi ölçümü.',                                                          'temel', 'on_degerlendirme', 'online', 'gratis_magaza', 1, 20,  'active'),
(112, 1, 11, 'Gratis Mağaza — Az Tehlikeli İSG Genel Konular',          'Çalışma mevzuatı, yasal haklar, işyeri temizliği.',                          'temel', 'genel',            'online', 'gratis_magaza', 2, 45,  'active'),
(113, 1, 11, 'Gratis Mağaza — Az Tehlikeli İSG Sağlık Konuları',        'Meslek hastalıkları, korunma, ilk yardım.',                                  'temel', 'saglik',           'online', 'gratis_magaza', 3, 90,  'active'),
(114, 1, 11, 'Gratis Mağaza — Az Tehlikeli İSG Teknik Konular',         'Kimyasal/fiziksel riskler, elle taşıma, yangın.',                            'temel', 'teknik',           'online', 'gratis_magaza', 4, 135, 'active'),
(115, 1, 11, 'Gratis Mağaza — İşe ve İşyerine Özgü Konular',           'Mağaza güvenliği, yüksekte çalışma, yangın, depolama, ergonomi.',             'temel', 'ise_ozgu',         'online', 'gratis_magaza', 5, 90,  'active'),
(116, 1, 11, 'Gratis Mağaza — Az Tehlikeli İSG Final Sınavı',           'Final sınavı — min. 60 puan.',                                               'temel', 'final_sinav',      'online', 'gratis_magaza', 6, 30,  'active');

-- MODÜLLER — Paket 12: Gratis Mağaza Az Tehlikeli Tekrar (IDs 121–126)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(121, 1, 12, 'Gratis Mağaza — Az Tehlikeli İSG Ön Değerlendirme (Tekrar)','Ön bilgi ölçümü.',                                                       'tekrar', 'on_degerlendirme', 'online', 'gratis_magaza', 1, 20,  'active'),
(122, 1, 12, 'Gratis Mağaza — Az Tehlikeli İSG Genel Konular (Tekrar)', 'Çalışma mevzuatı, yasal haklar.',                                           'tekrar', 'genel',            'online', 'gratis_magaza', 2, 45,  'active'),
(123, 1, 12, 'Gratis Mağaza — Az Tehlikeli İSG Sağlık Konuları (Tekrar)','Meslek hastalıkları, korunma, ilk yardım.',                               'tekrar', 'saglik',           'online', 'gratis_magaza', 3, 90,  'active'),
(124, 1, 12, 'Gratis Mağaza — Az Tehlikeli İSG Teknik Konular (Tekrar)','Kimyasal/fiziksel riskler, elle taşıma, yangın.',                           'tekrar', 'teknik',           'online', 'gratis_magaza', 4, 135, 'active'),
(125, 1, 12, 'Gratis Mağaza — İşe ve İşyerine Özgü Konular (Tekrar)',  'Mağaza güvenliği, yüksekte çalışma, yangın, depolama, ergonomi.',             'tekrar', 'ise_ozgu',         'online', 'gratis_magaza', 5, 90,  'active'),
(126, 1, 12, 'Gratis Mağaza — Az Tehlikeli İSG Final Sınavı (Tekrar)', 'Final sınavı — min. 60 puan.',                                               'tekrar', 'final_sinav',      'online', 'gratis_magaza', 6, 30,  'active');

-- MODÜLLER — Paket 13: Mutfak Az Tehlikeli Temel (IDs 131–136)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(131, 1, 13, 'Mutfak — Az Tehlikeli İSG Ön Değerlendirme',               'Ön bilgi ölçümü.',                                                          'temel', 'on_degerlendirme', 'online', 'mutfak', 1, 20,  'active'),
(132, 1, 13, 'Mutfak — Az Tehlikeli İSG Genel Konular',                  'Çalışma mevzuatı, yasal haklar, işyeri temizliği.',                          'temel', 'genel',            'online', 'mutfak', 2, 45,  'active'),
(133, 1, 13, 'Mutfak — Az Tehlikeli İSG Sağlık Konuları',                'Meslek hastalıkları, korunma, ilk yardım.',                                  'temel', 'saglik',           'online', 'mutfak', 3, 90,  'active'),
(134, 1, 13, 'Mutfak — Az Tehlikeli İSG Teknik Konular',                 'Kimyasal/fiziksel riskler, elle taşıma, yangın.',                            'temel', 'teknik',           'online', 'mutfak', 4, 135, 'active'),
(135, 1, 13, 'Mutfak — İşe ve İşyerine Özgü Konular',                   'Kişisel hijyen, mutfak yangınları, kimyasal etkenler, mutfak ekipmanları.',   'temel', 'ise_ozgu',         'online', 'mutfak', 5, 90,  'active'),
(136, 1, 13, 'Mutfak — Az Tehlikeli İSG Final Sınavı',                   'Final sınavı — min. 60 puan.',                                               'temel', 'final_sinav',      'online', 'mutfak', 6, 30,  'active');

-- MODÜLLER — Paket 14: Mutfak Az Tehlikeli Tekrar (IDs 141–146)
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(141, 1, 14, 'Mutfak — Az Tehlikeli İSG Ön Değerlendirme (Tekrar)',     'Ön bilgi ölçümü.',                                                           'tekrar', 'on_degerlendirme', 'online', 'mutfak', 1, 20,  'active'),
(142, 1, 14, 'Mutfak — Az Tehlikeli İSG Genel Konular (Tekrar)',        'Çalışma mevzuatı, yasal haklar.',                                             'tekrar', 'genel',            'online', 'mutfak', 2, 45,  'active'),
(143, 1, 14, 'Mutfak — Az Tehlikeli İSG Sağlık Konuları (Tekrar)',      'Meslek hastalıkları, korunma, ilk yardım.',                                   'tekrar', 'saglik',           'online', 'mutfak', 3, 90,  'active'),
(144, 1, 14, 'Mutfak — Az Tehlikeli İSG Teknik Konular (Tekrar)',       'Kimyasal/fiziksel riskler, elle taşıma, yangın.',                             'tekrar', 'teknik',           'online', 'mutfak', 4, 135, 'active'),
(145, 1, 14, 'Mutfak — İşe ve İşyerine Özgü Konular (Tekrar)',         'Kişisel hijyen, mutfak yangınları, kimyasal etkenler, mutfak ekipmanları.',    'tekrar', 'ise_ozgu',         'online', 'mutfak', 5, 90,  'active'),
(146, 1, 14, 'Mutfak — Az Tehlikeli İSG Final Sınavı (Tekrar)',        'Final sınavı — min. 60 puan.',                                                 'tekrar', 'final_sinav',      'online', 'mutfak', 6, 30,  'active');

SET FOREIGN_KEY_CHECKS = 1;
