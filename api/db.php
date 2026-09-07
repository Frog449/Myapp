<?php
date_default_timezone_set('Asia/Bangkok');

// Global CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Origin, Accept");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 1. Determine Driver & Connection Parameters from Environment Variables
$dbDriver = getenv('DB_DRIVER') ?: getenv('DB_CONNECTION') ?: 'mysql';
$databaseUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: getenv('CLEARDB_DATABASE_URL') ?: getenv('JAWSDB_URL') ?: '';

$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';
$db   = getenv('DB_NAME') ?: getenv('DB_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'myapp';
$user = getenv('DB_USER') ?: getenv('DB_USERNAME') ?: getenv('MYSQLUSER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';
$charset = 'utf8mb4';

// If full DATABASE_URL connection string is provided
if (!empty($databaseUrl)) {
    $parsedUrl = parse_url($databaseUrl);
    if ($parsedUrl) {
        $scheme = $parsedUrl['scheme'] ?? '';
        if ($scheme === 'sqlite') {
            $dbDriver = 'sqlite';
        } else {
            $dbDriver = 'mysql';
            $host = $parsedUrl['host'] ?? $host;
            $port = isset($parsedUrl['port']) ? (string)$parsedUrl['port'] : $port;
            $user = isset($parsedUrl['user']) ? urldecode($parsedUrl['user']) : $user;
            $pass = isset($parsedUrl['pass']) ? urldecode($parsedUrl['pass']) : $pass;
            $dbPath = ltrim($parsedUrl['path'] ?? '', '/');
            if (!empty($dbPath)) {
                $db = $dbPath;
            }
        }
    }
}

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    if (strtolower($dbDriver) === 'sqlite') {
        // SQLite Driver (Zero-config, ideal for Render free tier or standalone testing)
        $sqlitePath = getenv('SQLITE_PATH') ?: __DIR__ . '/database.sqlite';
        $pdo = new PDO("sqlite:$sqlitePath", null, null, $options);
        $pdo->exec("PRAGMA journal_mode = WAL;");
    } else {
        // MySQL Driver
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                try {
                    $pdoServer = new PDO("mysql:host=$host;port=$port;charset=$charset", $user, $pass, $options);
                    $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo = new PDO($dsn, $user, $pass, $options);
                } catch (\Exception $createEx) {
                    throw $e;
                }
            } else {
                throw $e;
            }
        }
    }

    // 2. Ensure tables exist (Self-healing schema)
    if (strtolower($dbDriver) === 'sqlite') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'customer',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS books (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                author TEXT NOT NULL,
                price REAL NOT NULL DEFAULT 0.00,
                stock INTEGER NOT NULL DEFAULT 0,
                category TEXT NOT NULL DEFAULT 'ทั่วไป',
                cover_url TEXT,
                description TEXT,
                rating REAL DEFAULT 4.80,
                pages INTEGER DEFAULT 200,
                is_featured INTEGER DEFAULT 0
            );

            CREATE TABLE IF NOT EXISTS orders (
                id TEXT PRIMARY KEY,
                user_id TEXT NOT NULL,
                user_name TEXT NOT NULL,
                total_amount REAL NOT NULL DEFAULT 0.00,
                status TEXT NOT NULL DEFAULT 'ชำระเงินแล้ว',
                order_date TEXT NOT NULL
            );
        ");
    } else {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` VARCHAR(50) PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL,
                `role` VARCHAR(50) NOT NULL DEFAULT 'customer',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

            CREATE TABLE IF NOT EXISTS `orders` (
                `id` VARCHAR(50) PRIMARY KEY,
                `user_id` VARCHAR(50) NOT NULL,
                `user_name` VARCHAR(255) NOT NULL,
                `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `status` VARCHAR(50) NOT NULL DEFAULT 'ชำระเงินแล้ว',
                `order_date` VARCHAR(50) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    // 3. Ensure Default Users Exist
    $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount < 2) {
        $userStmt = $pdo->prepare("INSERT OR IGNORE INTO users (id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        if (strtolower($dbDriver) !== 'sqlite') {
            $userStmt = $pdo->prepare("INSERT IGNORE INTO users (id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        }
        $userStmt->execute(['usr_6a963ebaa49a9', 'Safe', 'Safe@gmail.com', 'Safe2649', 'admin']);
        $userStmt->execute(['usr_admin', 'ผู้ดูแลระบบ (Admin)', 'admin@caffebook.com', 'admin123', 'admin']);
        $userStmt->execute(['usr_demo', 'สมชาย ใจดี', 'user@caffebook.com', 'user123', 'customer']);
    }

    // 4. Auto-seed 15 Books Catalog if empty or less than 10 books
    $bookCount = (int)$pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    if ($bookCount < 10) {
        $initialBooks = [
            [
                'b_6a9672e44c922', 'THE LITTLE FROG’S GUIDE TO SELF-CARE คู่มือดูแลใจฉบับกบน้อย (ปกแข็ง)', 'THE LITTLE FROG’S GUIDE TO SELF-CARE', 295.00, 52, 'พัฒนาตนเอง',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202608%2F713394%2F1000293298_front_XXL.jpg%3Fv%3D1787040630',
                'พบกับเพื่อนซี้หน้าใหม่ผู้แสนดี: เจ้ากบน้อย ในวันที่คุณเหนื่อยล้า... ลองพักสักหน่อย แล้วมาดูแลตัวเองไปพร้อมกับเจ้ากบตัวน้อยกันนะ หนังสือเล่มเล็กอันแสนอบอุ่นเล่มนี้ จะพาคุณค่อยๆ ก้าวออกจากความวุ่นวาย แล้วออกเดินทางสู่วิถีแห่งการฮีลใจ',
                4.80, 96, 1
            ],
            [
                'b_6a96720072bd0', 'โลกแห่งมหาศึกชิงบัลลังก์ (ใหม่/ปกแข็ง)', 'จอร์จ อาร์. อาร์. มาร์ติน', 1595.00, 54, 'นิยาย',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202205%2F548999%2F1000249072_front_XXL.jpg%3Fv%3D1725897175',
                'ประวัติศาสตร์อันสมบูรณ์แบบของทวีปเวสเทอรอสและดินแดนโพ้นทะเล ถ่ายทอดเรื่องราวมหากาพย์สงคราม มังกร และราชบัลลังก์',
                4.80, 332, 1
            ],
            [
                'b_008', 'ดาบพิฆาตอสูร เล่ม 6', 'Koyoharu Gotouge', 95.00, 39, 'การ์ตูน/มังงะ',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202005%2F504935%2F6000039117_front_XXL.jpg%3Fv%3D1725985941',
                'มังงะยอดฮิตตลอดกาล Demon Slayer เล่ม 6 การเดินทางของทันจิโร่เพื่อปกป้องเนซึโกะและปราบเหล่าอสูรจันทรา',
                4.00, 192, 1
            ],
            [
                'b_003', 'The Priory of the Orange Tree อารามเวทมังกรไร้นาม เล่ม 2', 'Samantha Shannon', 499.00, 14, 'นิยาย',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202606%2F708876%2F1000292179_front_XXL.jpg%3Fv%3D1782188416',
                'ผลงานจาก Samantha Shannon เจ้าของยอดขายกว่าหนึ่งล้านเล่มทั่วโลก ไฟพุ่งขึ้นจากผืนพิภพ แสงส่องลงจากฟากฟ้า มหากาพย์เวทมนตร์และมังกรอันยิ่งใหญ่',
                4.80, 544, 1
            ],
            [
                'b_001', 'กล้าที่จะถูกเกลียด', 'คิชิมิ อิชิโร, โคะกะ ฟุมิทะเกะ', 295.00, 24, 'พัฒนาตนเอง',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F201605%2F192079%2F1000185276_front_XXL.jpg%3Fv%3D1779253749',
                'หนังสือจิตวิทยาขายดีอันดับหนึ่งจากญี่ปุ่น อิงตามคำสอนของอัลเฟรด แอดเลอร์ ที่จะช่วยปลดปล่อยชีวิตคุณจากความคาดหวังของผู้อื่น',
                5.00, 313, 1
            ],
            [
                'b_6a96733ac7788', 'คิดแบบโสกราตีส', 'โดนัลด์ เจ. โรเบิร์ตสัน', 375.00, 97, 'นิยาย',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202510%2F686846%2F1000286007_front_XXL.jpg%3Fv%3D1760686206',
                'หนังสือเล่มนี้พาผู้อ่านไปรู้จักกับโสกราตีสในฐานะครูผู้ยิ่งใหญ่ที่ไม่เคยให้คำตอบ แต่สอนให้เราตั้งคำถามกับตัวเองและโลกรอบข้าง',
                4.80, 396, 0
            ],
            [
                'b_6a96730e4267a', 'จิตวิทยาสายดาร์ก', 'Dr.Hiro', 250.00, 111, 'พัฒนาตนเอง',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202304%2F577274%2F1000260516_front_XXL.jpg%3Fv%3D1762440259',
                'พบกับเทคนิคทางจิตวิทยาที่ช่วยให้คุณใช้คำพูดควบคุมจิตใจคน ทำให้พวกเขาคล้อยตามและทำอย่างที่คุณต้องการโดยไม่รู้ตัว',
                4.80, 280, 0
            ],
            [
                'b_6a9672af12d31', 'Money Mastery มั่งคั่งทั้งชีวิต', 'ภัทรพล ศิลปาจารย์ (พอล)', 299.00, 144, 'ธุรกิจ/บริหาร',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202304%2F576827%2F1000260379_front_XXL.jpg%3Fv%3D1762440252',
                'คู่มือการเงินที่อธิบายให้เห็นภาพชัดเจนว่าความรู้ทางการเงินที่ถูกต้องสามารถเปลี่ยนชีวิตและความมั่งคั่งของคุณได้อย่างไร',
                5.00, 222, 0
            ],
            [
                'b_6a96723fc7a74', 'แฮร์รี่ พอตเตอร์กับศิลาอาถรรพ์ปกแข็ง', 'J.K. Rowling', 1750.00, 5, 'นิยาย',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202606%2F708700%2F1000292158_front_XXL.jpg%3Fv%3D1782959399',
                'วรรณกรรมแฟนตาซีคลาสสิก แฮร์รี่ พอตเตอร์ ในฉบับมินาลิมาพร้อมภาพประกอบและลูกเล่นงานกระดาษสุดวิจิตร',
                4.80, 360, 0
            ],
            [
                'b_6a9671c7d5eab', 'มุมมองนักอ่านพระเจ้า เล่ม 23 (เล่มจบ)', 'sing N song', 475.00, 20, 'นิยาย',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202407%2F618516%2F1000273884_front_XXL.jpg%3Fv%3D1727869565',
                'บทสรุปสุดท้ายของมหากาพย์มุมมองนักอ่านพระเจ้า ความปรารถนาของคิมดกจาและสายใยของเหล่าสมาชิกที่ไม่มีวันตัดขาด',
                4.80, 408, 0
            ],
            [
                'b_007', 'JOJO ล่าข้ามศตวรรษ เครซี่ ไดอมอนด์ เล่ม 2', 'Kouhei Kadono', 155.00, 16, 'การ์ตูน/มังงะ',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202311%2F597696%2F1000267516_front_XXL.jpg%3Fv%3D1725921033',
                'การผจญภัยของฮอล ฮอร์ส และฮิงาชิคาตะ โจสุเกะ ในการสืบหาความจริงเบื้องหลังพลังสแตนด์จำลองอดีต',
                4.70, 139, 0
            ],
            [
                'b_006', 'JOJO ล่าข้ามศตวรรษ ภาค 5 สายลมทองคำ เล่ม 4', 'Hirohiko Araki', 275.00, 12, 'การ์ตูน/มังงะ',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202512%2F692368%2F1000287701_front_XXL.jpg%3Fv%3D1766633289',
                'การเดินทางของโจรูโน โจบาน่า และกลุ่มบูจาราตีเพื่อคุ้มครอง ทริช อูน่า ท่ามกลางการต่อสู้ด้วยพลังสแตนด์สุดดุเดือด',
                4.60, 376, 0
            ],
            [
                'b_005', 'Psychology of Money จิตวิทยาว่าด้วยเรื่องเงิน', 'Morgan Housel', 290.00, 20, 'ธุรกิจ/บริหาร',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202403%2F607911%2F1000270966_front_XXL.jpg%3Fv%3D1776836961',
                'จิตวิทยาว่าด้วยเงิน บทเรียนเหนือกาลเวลาเรื่องความมั่งคั่ง ความโลภ และความสุข การตัดสินใจเรื่องเงินที่แท้จริง',
                4.70, 304, 0
            ],
            [
                'b_004', 'เกมล่าบัลลังก์ เล่ม 1.1 (re-newed)', 'จอร์จ อาร์. อาร์. มาร์ติน', 425.00, 30, 'นิยาย',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202603%2F700088%2F1000289738_front_XXL.jpg%3Fv%3D1775211429',
                'มหากาพย์แฟนตาซีระดับโลก A Game of Thrones จุดเริ่มต้นแห่งสงครามชิงบัลลังก์เหล็กของเจ็ดราชอาณาจักร',
                4.90, 552, 0
            ],
            [
                'b_002', 'คิดแบบยิวทำแบบญี่ปุ่น', 'ฮอนดะ เคน', 230.00, 16, 'ธุรกิจ/บริหาร',
                'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202601%2F695304%2F6000124480_front_XXL.jpg%3Fv%3D1769769191',
                'ยอดขายทะลุ 3 ล้านเล่มในญี่ปุ่น เคล็ดลับการผสานวิธีคิดแบบชาวยิวเข้ากับการลงมือทำแบบชาวญี่ปุ่นเพื่อสร้างความมั่งคั่งและความสุข',
                4.90, 281, 0
            ]
        ];

        $bSql = (strtolower($dbDriver) === 'sqlite')
            ? "INSERT OR REPLACE INTO books (id, title, author, price, stock, category, cover_url, description, rating, pages, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            : "INSERT INTO books (id, title, author, price, stock, category, cover_url, description, rating, pages, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=VALUES(title), author=VALUES(author), price=VALUES(price), stock=VALUES(stock), category=VALUES(category), cover_url=VALUES(cover_url), description=VALUES(description), rating=VALUES(rating), pages=VALUES(pages), is_featured=VALUES(is_featured)";
        
        $bStmt = $pdo->prepare($bSql);
        foreach ($initialBooks as $b) {
            $bStmt->execute($b);
        }
    }

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $e->getMessage(),
        "driver" => $dbDriver ?? 'unknown'
    ]);
    exit();
}
