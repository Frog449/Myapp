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

-- Default Users
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
('usr_6a963ebaa49a9', 'Safe', 'Safe@gmail.com', 'Safe2649', 'admin', NOW()),
('usr_admin', 'ผู้ดูแลระบบ (Admin)', 'admin@caffebook.com', 'admin123', 'admin', NOW()),
('usr_demo', 'สมชาย ใจดี', 'user@caffebook.com', 'user123', 'customer', NOW());

-- Full 15 Books Catalog
INSERT IGNORE INTO `books` (`id`, `title`, `author`, `price`, `stock`, `category`, `cover_url`, `description`, `rating`, `pages`, `is_featured`) VALUES
('b_6a9672e44c922', 'THE LITTLE FROG’S GUIDE TO SELF-CARE คู่มือดูแลใจฉบับกบน้อย (ปกแข็ง)', 'THE LITTLE FROG’S GUIDE TO SELF-CARE', 295.00, 52, 'พัฒนาตนเอง', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202608%2F713394%2F1000293298_front_XXL.jpg%3Fv%3D1787040630', 'พบกับเพื่อนซี้หน้าใหม่ผู้แสนดี: เจ้ากบน้อย ในวันที่คุณเหนื่อยล้า... ลองพักสักหน่อย แล้วมาดูแลตัวเองไปพร้อมกับเจ้ากบตัวน้อยกันนะ', 4.80, 96, 1),
('b_6a96720072bd0', 'โลกแห่งมหาศึกชิงบัลลังก์ (ใหม่/ปกแข็ง)', 'จอร์จ อาร์. อาร์. มาร์ติน', 1595.00, 54, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202205%2F548999%2F1000249072_front_XXL.jpg%3Fv%3D1725897175', 'ประวัติศาสตร์อันสมบูรณ์แบบของทวีปเวสเทอรอสและดินแดนโพ้นทะเล', 4.80, 332, 1),
('b_008', 'ดาบพิฆาตอสูร เล่ม 6', 'Koyoharu Gotouge', 95.00, 39, 'การ์ตูน/มังงะ', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202005%2F504935%2F6000039117_front_XXL.jpg%3Fv%3D1725985941', 'มังงะยอดฮิตตลอดกาล Demon Slayer เล่ม 6 การเดินทางของทันจิโร่', 4.00, 192, 1),
('b_003', 'The Priory of the Orange Tree อารามเวทมังกรไร้นาม เล่ม 2', 'Samantha Shannon', 499.00, 14, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202606%2F708876%2F1000292179_front_XXL.jpg%3Fv%3D1782188416', 'ผลงานจาก Samantha Shannon เจ้าของยอดขายกว่าหนึ่งล้านเล่มทั่วโลก', 4.80, 544, 1),
('b_001', 'กล้าที่จะถูกเกลียด', 'คิชิมิ อิชิโร, โคะกะ ฟุมิทะเกะ', 295.00, 24, 'พัฒนาตนเอง', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F201605%2F192079%2F1000185276_front_XXL.jpg%3Fv%3D1779253749', 'หนังสือจิตวิทยาขายดีอันดับหนึ่งจากญี่ปุ่น อิงตามคำสอนของอัลเฟรด แอดเลอร์', 5.00, 313, 1),
('b_6a96733ac7788', 'คิดแบบโสกราตีส', 'โดนัลด์ เจ. โรเบิร์ตสัน', 375.00, 97, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202510%2F686846%2F1000286007_front_XXL.jpg%3Fv%3D1760686206', 'หนังสือเล่มนี้พาผู้อ่านไปรู้จักกับโสกราตีสในฐานะครูผู้ยิ่งใหญ่', 4.80, 396, 0),
('b_6a96730e4267a', 'จิตวิทยาสายดาร์ก', 'Dr.Hiro', 250.00, 111, 'พัฒนาตนเอง', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202304%2F577274%2F1000260516_front_XXL.jpg%3Fv%3D1762440259', 'เทคนิคทางจิตวิทยาที่ช่วยให้คุณใช้คำพูดควบคุมจิตใจคน', 4.80, 280, 0),
('b_6a9672af12d31', 'Money Mastery มั่งคั่งทั้งชีวิต', 'ภัทรพล ศิลปาจารย์ (พอล)', 299.00, 144, 'ธุรกิจ/บริหาร', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202304%2F576827%2F1000260379_front_XXL.jpg%3Fv%3D1762440252', 'คู่มือการเงินที่อธิบายให้เห็นภาพชัดเจนว่าความรู้ทางการเงินที่ถูกต้องเปลี่ยนชีวิตคุณได้', 5.00, 222, 0),
('b_6a96723fc7a74', 'แฮร์รี่ พอตเตอร์กับศิลาอาถรรพ์ปกแข็ง', 'J.K. Rowling', 1750.00, 5, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202606%2F708700%2F1000292158_front_XXL.jpg%3Fv%3D1782959399', 'วรรณกรรมแฟนตาซีคลาสสิก แฮร์รี่ พอตเตอร์ ฉบับมินาลิมาพร้อมภาพประกอบสุดวิจิตร', 4.80, 360, 0),
('b_6a9671c7d5eab', 'มุมมองนักอ่านพระเจ้า เล่ม 23 (เล่มจบ)', 'sing N song', 475.00, 20, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202407%2F618516%2F1000273884_front_XXL.jpg%3Fv%3D1727869565', 'บทสรุปสุดท้ายของมหากาพย์มุมมองนักอ่านพระเจ้า', 4.80, 408, 0),
('b_007', 'JOJO ล่าข้ามศตวรรษ เครซี่ ไดอมอนด์ เล่ม 2', 'Kouhei Kadono', 155.00, 16, 'การ์ตูน/มังงะ', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202311%2F597696%2F1000267516_front_XXL.jpg%3Fv%3D1725921033', 'การผจญภัยของฮอล ฮอร์ส และฮิงาชิคาตะ โจสุเกะ', 4.70, 139, 0),
('b_006', 'JOJO ล่าข้ามศตวรรษ ภาค 5 สายลมทองคำ เล่ม 4', 'Hirohiko Araki', 275.00, 12, 'การ์ตูน/มังงะ', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202512%2F692368%2F1000287701_front_XXL.jpg%3Fv%3D1766633289', 'การเดินทางของโจรูโน โจบาน่า และกลุ่มบูจาราตี', 4.60, 376, 0),
('b_005', 'Psychology of Money จิตวิทยาว่าด้วยเรื่องเงิน', 'Morgan Housel', 290.00, 20, 'ธุรกิจ/บริหาร', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202403%2F607911%2F1000270966_front_XXL.jpg%3Fv%3D1776836961', 'จิตวิทยาว่าด้วยเงิน บทเรียนเหนือกาลเวลาเรื่องความมั่งคั่ง', 4.70, 304, 0),
('b_004', 'เกมล่าบัลลังก์ เล่ม 1.1 (re-newed)', 'จอร์จ อาร์. อาร์. มาร์ติน', 425.00, 30, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202603%2F700088%2F1000289738_front_XXL.jpg%3Fv%3D1775211429', 'มหากาพย์แฟนตาซีระดับโลก A Game of Thrones จุดเริ่มต้นสงครามชิงบัลลังก์เหล็ก', 4.90, 552, 0),
('b_002', 'คิดแบบยิวทำแบบญี่ปุ่น', 'ฮอนดะ เคน', 230.00, 16, 'ธุรกิจ/บริหาร', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202601%2F695304%2F6000124480_front_XXL.jpg%3Fv%3D1769769191', 'ยอดขายทะลุ 3 ล้านเล่มในญี่ปุ่น สร้างความมั่งคั่งและความสุขให้เกิดขึ้นในชีวิต', 4.90, 281, 0);

-- Initial Orders
INSERT IGNORE INTO `orders` (`id`, `user_id`, `user_name`, `total_amount`, `status`, `order_date`) VALUES
('ord_001', 'usr_demo', 'สมชาย ใจดี', 590.00, 'จัดส่งแล้ว', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('ord_002', 'usr_demo', 'สมชาย ใจดี', 295.00, 'ชำระเงินแล้ว', NOW());
