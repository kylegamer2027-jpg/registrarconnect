CREATE DATABASE IF NOT EXISTS registrarconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE registrarconnect;
CREATE TABLE IF NOT EXISTS users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(150) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 student_no VARCHAR(60) NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('student','registrar') NOT NULL DEFAULT 'student',
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS document_types (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(150) NOT NULL,
 description VARCHAR(255) NOT NULL,
 icon_color VARCHAR(20) NOT NULL DEFAULT 'red',
 icon_symbol VARCHAR(10) NOT NULL DEFAULT '▤',
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS document_requests (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 student_id BIGINT UNSIGNED NOT NULL,
 document_type_id INT UNSIGNED NOT NULL,
 purpose VARCHAR(100) NOT NULL,
 academic_period VARCHAR(100) NULL,
 student_remarks TEXT NULL,
 registrar_remarks TEXT NULL,
 status ENUM('Pending','Under Review','Processing','Ready for Release','Completed','Rejected') NOT NULL DEFAULT 'Pending',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_request_student FOREIGN KEY(student_id) REFERENCES users(id),
 CONSTRAINT fk_request_document FOREIGN KEY(document_type_id) REFERENCES document_types(id),
 INDEX idx_request_student_date(student_id,created_at), INDEX idx_request_status(status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS request_status_history (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 request_id BIGINT UNSIGNED NOT NULL,
 old_status VARCHAR(40) NULL,
 new_status VARCHAR(40) NOT NULL,
 changed_by BIGINT UNSIGNED NULL,
 remarks VARCHAR(2000) NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_history_request FOREIGN KEY(request_id) REFERENCES document_requests(id) ON DELETE CASCADE,
 CONSTRAINT fk_history_user FOREIGN KEY(changed_by) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_history_request_date(request_id,created_at)
) ENGINE=InnoDB;
INSERT INTO document_types(name,description,icon_color,icon_symbol,sort_order) SELECT 'Certificate of Enrollment','Official proof of your current enrollment status.','red','▤',1 WHERE NOT EXISTS(SELECT 1 FROM document_types WHERE name='Certificate of Enrollment');
INSERT INTO document_types(name,description,icon_color,icon_symbol,sort_order) SELECT 'Certificate of Grades','Certified copy of grades for a selected term.','purple','▤',2 WHERE NOT EXISTS(SELECT 1 FROM document_types WHERE name='Certificate of Grades');
INSERT INTO document_types(name,description,icon_color,icon_symbol,sort_order) SELECT 'Transcript of Records','Complete record of courses and academic grades.','cyan','▣',3 WHERE NOT EXISTS(SELECT 1 FROM document_types WHERE name='Transcript of Records');
INSERT INTO document_types(name,description,icon_color,icon_symbol,sort_order) SELECT 'Good Moral Certificate','Certification of good conduct and character.','green','♢',4 WHERE NOT EXISTS(SELECT 1 FROM document_types WHERE name='Good Moral Certificate');
INSERT INTO document_types(name,description,icon_color,icon_symbol,sort_order) SELECT 'Other School Documents','Request certifications and other registrar records.','amber','···',5 WHERE NOT EXISTS(SELECT 1 FROM document_types WHERE name='Other School Documents');
