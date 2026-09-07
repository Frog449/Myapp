-- ===================================================
-- CaffeBook Database Schema & Seed Data
-- ===================================================

CREATE DATABASE IF NOT EXISTS `myapp` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `myapp`;

-- 1. Table: Users
CREATE TABLE IF NOT EXISTS `users` (
    `id` VARCHAR(50) PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL DEFAULT 'customer',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table: Books
CREATE TABLE IF NOT EXISTS `books` (
    `id` VARCHAR(50) PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `author` VARCHAR(255) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `stock` INT NOT NULL DEFAULT 0,
    `category` VARCHAR(100) NOT NULL DEFAULT 'ทั่วไป',
    `cover_url` TEXT,
    `description` TEXT,
    `rating` DECIMAL(3,2) DEFAULT 4.80,
    `pages` INT DEFAULT 200,
    `is_featured` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table: Orders
CREATE TABLE IF NOT EXISTS `orders` (
    `id` VARCHAR(50) PRIMARY KEY,
    `user_id` VARCHAR(50) NOT NULL,
    `user_name` VARCHAR(255) NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` VARCHAR(50) NOT NULL DEFAULT 'ชำระเงินแล้ว',
    `order_date` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- Initial Seed Data
-- ===================================================

-- Default Users (Passwords: 'admin123' and 'user123')
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
('usr_admin', 'ผู้ดูแลระบบ (Admin)', 'admin@caffebook.com', 'admin123', 'admin', NOW()),
('usr_demo', 'สมชาย ใจดี', 'user@caffebook.com', 'user123', 'customer', NOW());

-- Initial Books Catalog
INSERT IGNORE INTO `books` (`id`, `title`, `author`, `price`, `stock`, `category`, `cover_url`, `description`, `rating`, `pages`, `is_featured`) VALUES
('b_001', 'Atomic Habits เพราะชีวิตดีได้กว่าที่เป็น', 'James Clear', 295.00, 25, 'พัฒนาตนเอง', 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500', 'หนังสือขายดีระดับโลกที่จะเปลี่ยนนิสัยเล็กๆ ของคุณให้กลายเป็นความสำเร็จอันยิ่งใหญ่ ปรับเปลี่ยนพฤติกรรมอย่างยั่งยืน', 4.90, 328, 1),
('b_002', 'คิดแบบยิว ทำแบบญี่ปุ่น (Jewish Money Mindset)', 'Honda Ken', 260.00, 18, 'ธุรกิจ/บริหาร', 'https://images.unsplash.com/photo-1592496431122-2349e0fbc666?w=500', 'คัมภีร์สร้างความมั่งคั่งและความสุขที่แท้จริง ถ่ายทอดจากประสบการณ์ตรงของมหาเศรษฐีชาวยิวสู่ชาวญี่ปุ่น', 4.85, 280, 1),
('b_003', 'ปาฏิหาริย์ร้านชำของคุณนามิยะ', 'ฮิงาชิโนะ เคโงะ', 295.00, 15, 'นิยาย', 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=500', 'วรรณกรรมอบอุ่นหัวใจ จดหมายปรึกษาปัญหาชีวิตที่ส่งผ่านมิติเวลา และสายใยที่เชื่อมโยงผู้คนเข้าด้วยกัน', 4.95, 380, 1),
('b_004', 'เจ้าชายน้อย (The Little Prince)', 'Antoine de Saint-Exupéry', 185.00, 30, 'วรรณกรรม', 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=500', 'วรรณกรรมคลาสสิกระดับโลก เรื่องราวการเดินทางค้นหาความหมายของความรัก มิตรภาพ และดวงตาที่มองไม่เห็นสิ่งที่สำคัญด้วยใจ', 4.90, 140, 0),
('b_005', 'Psychology of Money จิตวิทยาว่าด้วยเรื่องเงิน', 'Morgan Housel', 290.00, 20, 'ธุรกิจ/บริหาร', 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?w=500', 'บทเรียนเหนือกาลเวลาเรื่องความมั่งคั่ง ความโลภ และความสุข การจัดการเงินไม่ใช่เรื่องของคณิตศาสตร์ แต่เป็นเรื่องของพฤติกรรม', 4.88, 304, 1),
('b_006', 'คินสึงิ ความงดงามของชีวิตที่แตกร้าว', 'Tomas Navarro', 275.00, 12, 'พัฒนาตนเอง', 'https://images.unsplash.com/photo-1506880018603-83d5b814b5a6?w=500', 'ศิลปะการเยียวยาจิตใจสไตล์ญี่ปุ่น เรียนรู้ที่จะโอบกอดรอยแผลเป็นและความล้มเหลวให้กลายเป็นจุดเด่นที่งดงาม', 4.75, 256, 0),
('b_007', 'ร้านอาหารมหัศจรรย์สำหรับคนใจสลาย', 'นัมซังซุน', 265.00, 16, 'นิยาย', 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=500', 'นิยายเกาหลีที่ฮีลใจผู้คน เมนูอาหารสุดพิเศษที่ปรุงขึ้นเพื่อเยียวยาบาดแผลในหัวใจของแขกผู้มาเยือน', 4.80, 290, 0),
('b_008', 'ดาบพิฆาตอสูร Demon Slayer เล่ม 1', 'Koyoharu Gotouge', 95.00, 40, 'การ์ตูน/มังงะ', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=500', 'มังงะยอดฮิตตลอดกาล การเดินทางของทันจิโร่เพื่อช่วยเหลือน้องสาวและปราบเหล่าอสูรร้าย', 4.92, 192, 1);

-- Initial Sample Orders
INSERT IGNORE INTO `orders` (`id`, `user_id`, `user_name`, `total_amount`, `status`, `order_date`) VALUES
('ord_001', 'usr_demo', 'สมชาย ใจดี', 590.00, 'จัดส่งแล้ว', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('ord_002', 'usr_demo', 'สมชาย ใจดี', 295.00, 'ชำระเงินแล้ว', NOW());
