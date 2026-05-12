-- =============================================================
--  AutoBook — Car Booking System
--  MySQL schema + seed data (for phpMyAdmin / XAMPP)
-- =============================================================
--  How to use:
--    1) Open phpMyAdmin (http://localhost/phpmyadmin)
--    2) Create a new database (e.g. `car_booking`) using utf8mb4_unicode_ci
--    3) Select it, click "Import" and import this file
--    4) Update credentials in includes/db.php
-- =============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
--  Database (uncomment if you want this script to create it)
-- -------------------------------------------------------------
-- CREATE DATABASE IF NOT EXISTS `car_booking`
--   DEFAULT CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;
-- USE `car_booking`;

-- -------------------------------------------------------------
--  Drop existing tables (clean install)
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `cars`;
DROP TABLE IF EXISTS `users`;

-- -------------------------------------------------------------
--  USERS
-- -------------------------------------------------------------
CREATE TABLE `users` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120)  NOT NULL,
  `email`      VARCHAR(160)  NOT NULL,
  `password`   VARCHAR(255)  NOT NULL,
  `role`       ENUM('admin','user') NOT NULL DEFAULT 'user',
  `phone`      VARCHAR(30)   DEFAULT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  CARS
-- -------------------------------------------------------------
CREATE TABLE `cars` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120) NOT NULL,
  `license_plate` VARCHAR(40)  NOT NULL,
  `type`          VARCHAR(40)  NOT NULL,
  `seats`         SMALLINT UNSIGNED NOT NULL DEFAULT 4,
  `description`   TEXT         DEFAULT NULL,
  `image`         VARCHAR(255) DEFAULT NULL,
  `status`        ENUM('available','booked') NOT NULL DEFAULT 'available',
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cars_license` (`license_plate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  BOOKINGS
-- -------------------------------------------------------------
CREATE TABLE `bookings` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `car_id`      INT UNSIGNED NOT NULL,
  `start_date`  DATE         NOT NULL,
  `end_date`    DATE         NOT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `reason`      TEXT         NOT NULL,
  `phone`       VARCHAR(30)  NOT NULL,
  `status`      ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `admin_note`  TEXT         DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bookings_user`   (`user_id`),
  KEY `idx_bookings_car`    (`car_id`),
  KEY `idx_bookings_status` (`status`),
  CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_car`  FOREIGN KEY (`car_id`)  REFERENCES `cars`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  SEED DATA
-- =============================================================

-- Passwords are bcrypt-hashed ($2b$ prefix is fully supported by PHP password_verify).
--   admin@example.com  → admin123456
--   user@example.com   → user123456
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`) VALUES
('System Admin', 'admin@example.com', '$2b$10$VHkUdCB7.Cg9tr5eoHt5Ye8gTxOdxaYbmnJKcucvO8wD10.Lp4yay', 'admin', '0812345678'),
('Test User',    'user@example.com',  '$2b$10$c3GSipDJf9yKqdJWvQZBueyGntl4fveMhWd3qmittK9IYJYewHb12', 'user',  '0898765432');

-- Sample fleet (15 cars)
INSERT INTO `cars` (`name`, `license_plate`, `type`, `seats`, `description`, `status`) VALUES
('Toyota Commuter',   'ฮข 1234', 'Van',      12, 'รถตู้ 12 ที่นั่ง สำหรับเดินทางเป็นหมู่คณะ',          'available'),
('Honda Civic',       'กข 5678', 'Sedan',     4, 'รถเก๋ง 4 ที่นั่ง เหมาะสำหรับผู้บริหาร',              'booked'),
('Isuzu D-Max',       'บม 9999', 'Pickup',    4, 'รถกระบะ 4 ประตู สำหรับขนของและเดินทาง',              'available'),
('Toyota Fortuner',   'ฌญ 4321', 'SUV',       7, 'รถอเนกประสงค์ 7 ที่นั่ง',                            'available'),
('Hyundai Staria',    'ฮภ 8888', 'Minivan',  11, 'รถตู้ VIP 11 ที่นั่ง',                                'available'),
('Toyota Alphard',    'กง 1010', 'Minivan',   7, 'รถตู้ VIP บนสุด 7 ที่นั่ง เหมาะสำหรับผู้บริหารระดับสูง', 'available'),
('Toyota Camry',      'กง 2020', 'Sedan',     5, 'รถเก๋ง Sedan หรูหรา เหมาะสำหรับรับรองแขก VIP',       'available'),
('Mitsubishi Triton', 'กง 3030', 'Pickup',    4, 'รถกระบะ 4 ประตู ทนทาน เหมาะสำหรับงานภาคสนาม',       'available'),
('Ford Ranger',       'กง 4040', 'Pickup',    4, 'รถกระบะ 4 ประตู พร้อม Bi-Turbo สำหรับทุกเส้นทาง',    'available'),
('Mazda CX-5',        'กง 5050', 'SUV',       5, 'รถ SUV 5 ที่นั่ง ดีไซน์สปอร์ต ประหยัดเชื้อเพลิง',    'available'),
('Toyota Vios',       'กง 6060', 'Sedan',     5, 'รถเก๋งขนาดกลาง ประหยัดน้ำมัน เหมาะสำหรับในเมือง',   'available'),
('Isuzu MU-X',        'กง 7070', 'SUV',       7, 'รถ SUV 7 ที่นั่ง ขับเคลื่อน 4 ล้อ เหมาะสำหรับทุกสภาพถนน', 'available'),
('Honda CR-V',        'กง 8080', 'SUV',       5, 'รถ SUV 5 ที่นั่ง พร้อมระบบ Hybrid ประหยัดพลังงาน',   'available'),
('Nissan Navara',     'กง 9090', 'Pickup',    4, 'รถกระบะ Double Cab แข็งแกร่ง พร้อมตู้ท้ายขนาดใหญ่',  'available'),
('Toyota Hiace',      'กค 1111', 'Van',      12, 'รถตู้โดยสาร 12 ที่นั่ง สำหรับเดินทางหมู่คณะระยะไกล', 'available');

-- Sample bookings (different statuses)
INSERT INTO `bookings`
  (`user_id`, `car_id`, `start_date`, `end_date`, `destination`, `reason`, `phone`, `status`, `admin_note`)
VALUES
  -- PENDING: user1 → Van, 2 days from now
  (2, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY),
   'ชลบุรี', 'ไปดูงานที่ชลบุรี', '0898765432', 'pending', NULL),
  -- APPROVED: user1 → Sedan, 5 days from now (this is why car #2 is `booked`)
  (2, 2, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY),
   'เชียงใหม่', 'ประชุมสัมมนาประจำปี', '0898765432', 'approved',
   'อนุมัติเรียบร้อย โปรดมารับกุญแจก่อนเดินทาง 1 วัน'),
  -- COMPLETED: user1 → Pickup, 10 days ago
  (2, 3, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 9 DAY),
   'วิทยาเขตบางนา', 'ขนอุปกรณ์ IT', '0898765432', 'completed', NULL),
  -- REJECTED: user1 → SUV, 15 days ago
  (2, 4, DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY),
   'หัวหิน', 'พักผ่อนต่างจังหวัด', '0898765432', 'rejected',
   'ปฏิเสธ: ไม่อยู่ในวัตถุประสงค์การใช้รถส่วนกลาง');

SET FOREIGN_KEY_CHECKS = 1;
