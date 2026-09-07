<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=UTF-8');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

function cleanCoverUrlInternal($url) {
    $url = trim($url);
    if (empty($url)) return 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500';
    if (strpos($url, 'images.weserv.nl') !== false) return $url;
    if (strpos($url, 'pim-cdn0.ofm.co.th') !== false) {
        if (($pos = strpos($url, '.jpg')) !== false) {
            $url = substr($url, 0, $pos + 4);
        } else if (($pos = strpos($url, '.png')) !== false) {
            $url = substr($url, 0, $pos + 4);
        }
    }
    if (substr_count($url, '?') > 1) {
        $firstPos = strpos($url, '?');
        $before = substr($url, 0, $firstPos + 1);
        $after = str_replace('?', '&', substr($url, $firstPos + 1));
        $url = $before . $after;
    }
    if (strpos($url, 'http') === 0 && strpos($url, '127.0.0.1') === false && strpos($url, 'images.unsplash.com') === false) {
        return 'https://images.weserv.nl/?url=' . urlencode($url);
    }
    return $url;
}

try {
    switch ($action) {
        // --- BOOKS ACTIONS ---
        case 'add_book':
            $id = 'b_' . uniqid();
            $title = trim($_POST['title'] ?? '');
            $author = trim($_POST['author'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $category = trim($_POST['category'] ?? 'นิยาย');
            $raw_cover = trim($_POST['cover_url'] ?? '');
            $cover_url = cleanCoverUrlInternal($raw_cover);
            $description = trim($_POST['description'] ?? '');
            $rating = floatval($_POST['rating'] ?? 4.8);
            $pages = intval($_POST['pages'] ?? 200);
            $is_featured = isset($_POST['is_featured']) && ($_POST['is_featured'] == 1 || $_POST['is_featured'] === 'on') ? 1 : 0;

            if (empty($title) || empty($author) || $price <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อหนังสือ ผู้แต่ง และราคา ให้ถูกต้อง']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO books (id, title, author, price, stock, category, cover_url, description, rating, pages, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $title, $author, $price, $stock, $category, $cover_url, $description, $rating, $pages, $is_featured]);
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มหนังสือใหม่เรียบร้อยแล้ว']);
            break;

        case 'edit_book':
            $id = trim($_POST['id'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $author = trim($_POST['author'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $category = trim($_POST['category'] ?? 'ทั่วไป');
            $raw_cover = trim($_POST['cover_url'] ?? '');
            $cover_url = cleanCoverUrlInternal($raw_cover);
            $description = trim($_POST['description'] ?? '');
            $rating = floatval($_POST['rating'] ?? 4.8);
            $pages = intval($_POST['pages'] ?? 200);
            $is_featured = isset($_POST['is_featured']) && ($_POST['is_featured'] == 1 || $_POST['is_featured'] === 'on') ? 1 : 0;

            if (empty($id) || empty($title) || empty($author) || $price <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, price=?, stock=?, category=?, cover_url=?, description=?, rating=?, pages=?, is_featured=? WHERE id=?");
            $stmt->execute([$title, $author, $price, $stock, $category, $cover_url, $description, $rating, $pages, $is_featured, $id]);
            echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลหนังสือเรียบร้อยแล้ว']);
            break;

        case 'delete_book':
            $id = trim($_POST['id'] ?? $_GET['id'] ?? '');
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID หนังสือที่ต้องการลบ']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'ลบหนังสือเรียบร้อยแล้ว']);
            break;

        case 'toggle_featured':
            $id = trim($_POST['id'] ?? '');
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID หนังสือ']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE books SET is_featured = 1 - is_featured WHERE id = ?");
            $stmt->execute([$id]);
            
            // Get new status
            $chk = $pdo->prepare("SELECT is_featured FROM books WHERE id = ?");
            $chk->execute([$id]);
            $is_featured = $chk->fetchColumn();

            echo json_encode([
                'status' => 'success',
                'is_featured' => (int)$is_featured,
                'message' => $is_featured ? 'ตั้งเป็นหนังสือแนะนำแล้ว ⭐' : 'ยกเลิกการเป็นหนังสือแนะนำแล้ว'
            ]);
            break;

        case 'update_stock':
            $id = trim($_POST['id'] ?? '');
            $type = trim($_POST['type'] ?? 'set'); // 'set', 'inc', 'dec'
            $delta = intval($_POST['delta'] ?? 1);
            $stock = intval($_POST['stock'] ?? 0);

            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID หนังสือ']);
                exit;
            }

            if ($type === 'inc') {
                $stmt = $pdo->prepare("UPDATE books SET stock = stock + ? WHERE id = ?");
                $stmt->execute([$delta, $id]);
            } else if ($type === 'dec') {
                $stmt = $pdo->prepare("UPDATE books SET stock = CASE WHEN stock >= ? THEN stock - ? ELSE 0 END WHERE id = ?");
                $stmt->execute([$delta, $delta, $id]);
            } else {
                $cleanStock = max(0, $stock);
                $stmt = $pdo->prepare("UPDATE books SET stock = ? WHERE id = ?");
                $stmt->execute([$cleanStock, $id]);
            }

            $chk = $pdo->prepare("SELECT stock FROM books WHERE id = ?");
            $chk->execute([$id]);
            $new_stock = $chk->fetchColumn();

            echo json_encode([
                'status' => 'success',
                'stock' => (int)$new_stock,
                'message' => "อัปเดตสต็อกคงเหลือเป็น {$new_stock} เล่ม"
            ]);
            break;

        case 'duplicate_book':
            $id = trim($_POST['id'] ?? '');
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID หนังสือ']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
            $stmt->execute([$id]);
            $src = $stmt->fetch();
            if (!$src) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบหนังสือต้นฉบับ']);
                exit;
            }

            $new_id = 'b_' . uniqid();
            $new_title = $src['title'] . ' (สำเนา)';
            $ins = $pdo->prepare("INSERT INTO books (id, title, author, price, stock, category, cover_url, description, rating, pages, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([
                $new_id,
                $new_title,
                $src['author'],
                $src['price'],
                $src['stock'],
                $src['category'],
                $src['cover_url'],
                $src['description'],
                $src['rating'],
                $src['pages'],
                0 // duplicate not featured by default
            ]);
            echo json_encode(['status' => 'success', 'message' => "คัดลอกหนังสือ '{$new_title}' เรียบร้อยแล้ว"]);
            break;

        case 'bulk_delete_books':
            $ids = json_decode($_POST['ids'] ?? '[]', true);
            if (empty($ids) || !is_array($ids)) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่มีรายการที่เลือก']);
                exit;
            }
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM books WHERE id IN ($inQuery)");
            $stmt->execute($ids);
            $count = count($ids);
            echo json_encode(['status' => 'success', 'message' => "ลบหนังสือที่เลือกจำนวน {$count} เล่มเรียบร้อยแล้ว"]);
            break;

        case 'bulk_update_category':
            $ids = json_decode($_POST['ids'] ?? '[]', true);
            $category = trim($_POST['category'] ?? '');
            if (empty($ids) || !is_array($ids) || empty($category)) {
                echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
                exit;
            }
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$category], $ids);
            $stmt = $pdo->prepare("UPDATE books SET category = ? WHERE id IN ($inQuery)");
            $stmt->execute($params);
            $count = count($ids);
            echo json_encode(['status' => 'success', 'message' => "เปลี่ยนหมวดหมู่ {$count} รายการเป็น '{$category}' เรียบร้อยแล้ว"]);
            break;

        // --- ORDERS ACTIONS ---
        case 'update_order_status':
            $id = trim($_POST['id'] ?? '');
            $status = trim($_POST['status'] ?? '');
            if (empty($id) || empty($status)) {
                echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['status' => 'success', 'message' => 'อัปเดตสถานะออเดอร์เรียบร้อยแล้ว']);
            break;

        case 'delete_order':
            $id = trim($_POST['id'] ?? $_GET['id'] ?? '');
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID คำสั่งซื้อ']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'ลบรายการสั่งซื้อเรียบร้อยแล้ว']);
            break;

        // --- USERS ACTIONS ---
        case 'update_user_role':
            $id = trim($_POST['id'] ?? '');
            $role = trim($_POST['role'] ?? 'customer');
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID ผู้ใช้']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $id]);
            echo json_encode(['status' => 'success', 'message' => 'เปลี่ยนสิทธิ์ผู้ใช้เรียบร้อยแล้ว']);
            break;

        case 'delete_user':
            $id = trim($_POST['id'] ?? $_GET['id'] ?? '');
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID ผู้ใช้']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'ลบผู้ใช้เรียบร้อยแล้ว']);
            break;

        case 'add_user':
            $id = 'usr_' . uniqid();
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $role = trim($_POST['role'] ?? 'customer');

            if (empty($name) || empty($email) || empty($password)) {
                echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
                exit;
            }

            // Check duplicate email
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $chk->execute([$email]);
            if ($chk->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'อีเมลนี้ถูกใช้งานแล้ว']);
                exit;
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $name, $email, $password_hash, $role]);
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว']);
            break;

        case 'restore_full_catalog':
            $fullBooks = [
                ['b_6a9672e44c922', 'THE LITTLE FROG’S GUIDE TO SELF-CARE คู่มือดูแลใจฉบับกบน้อย (ปกแข็ง)', 'THE LITTLE FROG’S GUIDE TO SELF-CARE', 295.00, 52, 'พัฒนาตนเอง', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202608%2F713394%2F1000293298_front_XXL.jpg%3Fv%3D1787040630', 'พบกับเพื่อนซี้หน้าใหม่ผู้แสนดี: เจ้ากบน้อย ในวันที่คุณเหนื่อยล้า... ลองพักสักหน่อย แล้วมาดูแลตัวเองไปพร้อมกับเจ้ากบตัวน้อยกันนะ', 4.80, 96, 1],
                ['b_6a96720072bd0', 'โลกแห่งมหาศึกชิงบัลลังก์ (ใหม่/ปกแข็ง)', 'จอร์จ อาร์. อาร์. มาร์ติน', 1595.00, 54, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202205%2F548999%2F1000249072_front_XXL.jpg%3Fv%3D1725897175', 'ประวัติศาสตร์อันสมบูรณ์แบบของทวีปเวสเทอรอสและดินแดนโพ้นทะเล', 4.80, 332, 1],
                ['b_008', 'ดาบพิฆาตอสูร เล่ม 6', 'Koyoharu Gotouge', 95.00, 39, 'การ์ตูน/มังงะ', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202005%2F504935%2F6000039117_front_XXL.jpg%3Fv%3D1725985941', 'มังงะยอดฮิตตลอดกาล Demon Slayer เล่ม 6 การเดินทางของทันจิโร่', 4.00, 192, 1],
                ['b_003', 'The Priory of the Orange Tree อารามเวทมังกรไร้นาม เล่ม 2', 'Samantha Shannon', 499.00, 14, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202606%2F708876%2F1000292179_front_XXL.jpg%3Fv%3D1782188416', 'ผลงานจาก Samantha Shannon เจ้าของยอดขายกว่าหนึ่งล้านเล่มทั่วโลก', 4.80, 544, 1],
                ['b_001', 'กล้าที่จะถูกเกลียด', 'คิชิมิ อิชิโร, โคะกะ ฟุมิทะเกะ', 295.00, 24, 'พัฒนาตนเอง', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F201605%2F192079%2F1000185276_front_XXL.jpg%3Fv%3D1779253749', 'หนังสือจิตวิทยาขายดีอันดับหนึ่งจากญี่ปุ่น อิงตามคำสอนของอัลเฟรด แอดเลอร์', 5.00, 313, 1],
                ['b_6a96733ac7788', 'คิดแบบโสกราตีส', 'โดนัลด์ เจ. โรเบิร์ตสัน', 375.00, 97, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202510%2F686846%2F1000286007_front_XXL.jpg%3Fv%3D1760686206', 'หนังสือเล่มนี้พาผู้อ่านไปรู้จักกับโสกราตีสในฐานะครูผู้ยิ่งใหญ่', 4.80, 396, 0],
                ['b_6a96730e4267a', 'จิตวิทยาสายดาร์ก', 'Dr.Hiro', 250.00, 111, 'พัฒนาตนเอง', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202304%2F577274%2F1000260516_front_XXL.jpg%3Fv%3D1762440259', 'เทคนิคทางจิตวิทยาที่ช่วยให้คุณใช้คำพูดควบคุมจิตใจคน', 4.80, 280, 0],
                ['b_6a9672af12d31', 'Money Mastery มั่งคั่งทั้งชีวิต', 'ภัทรพล ศิลปาจารย์ (พอล)', 299.00, 144, 'ธุรกิจ/บริหาร', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202304%2F576827%2F1000260379_front_XXL.jpg%3Fv%3D1762440252', 'คู่มือการเงินที่อธิบายให้เห็นภาพชัดเจนว่าความรู้ทางการเงินที่ถูกต้องเปลี่ยนชีวิตคุณได้', 5.00, 222, 0],
                ['b_6a96723fc7a74', 'แฮร์รี่ พอตเตอร์กับศิลาอาถรรพ์ปกแข็ง', 'J.K. Rowling', 1750.00, 5, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202606%2F708700%2F1000292158_front_XXL.jpg%3Fv%3D1782959399', 'วรรณกรรมแฟนตาซีคลาสสิก แฮร์รี่ พอตเตอร์ ฉบับมินาลิมาพร้อมภาพประกอบสุดวิจิตร', 4.80, 360, 0],
                ['b_6a9671c7d5eab', 'มุมมองนักอ่านพระเจ้า เล่ม 23 (เล่มจบ)', 'sing N song', 475.00, 20, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202407%2F618516%2F1000273884_front_XXL.jpg%3Fv%3D1727869565', 'บทสรุปสุดท้ายของมหากาพย์มุมมองนักอ่านพระเจ้า', 4.80, 408, 0],
                ['b_007', 'JOJO ล่าข้ามศตวรรษ เครซี่ ไดอมอนด์ เล่ม 2', 'Kouhei Kadono', 155.00, 16, 'การ์ตูน/มังงะ', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202311%2F597696%2F1000267516_front_XXL.jpg%3Fv%3D1725921033', 'การผจญภัยของฮอล ฮอร์ส และฮิงาชิคาตะ โจสุเกะ', 4.70, 139, 0],
                ['b_006', 'JOJO ล่าข้ามศตวรรษ ภาค 5 สายลมทองคำ เล่ม 4', 'Hirohiko Araki', 275.00, 12, 'การ์ตูน/มังงะ', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202512%2F692368%2F1000287701_front_XXL.jpg%3Fv%3D1766633289', 'การเดินทางของโจรูโน โจบาน่า และกลุ่มบูจาราตี', 4.60, 376, 0],
                ['b_005', 'Psychology of Money จิตวิทยาว่าด้วยเรื่องเงิน', 'Morgan Housel', 290.00, 20, 'ธุรกิจ/บริหาร', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202403%2F607911%2F1000270966_front_XXL.jpg%3Fv%3D1776836961', 'จิตวิทยาว่าด้วยเงิน บทเรียนเหนือกาลเวลาเรื่องความมั่งคั่ง', 4.70, 304, 0],
                ['b_004', 'เกมล่าบัลลังก์ เล่ม 1.1 (re-newed)', 'จอร์จ อาร์. อาร์. มาร์ติน', 425.00, 30, 'นิยาย', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202603%2F700088%2F1000289738_front_XXL.jpg%3Fv%3D1775211429', 'มหากาพย์แฟนตาซีระดับโลก A Game of Thrones จุดเริ่มต้นสงครามชิงบัลลังก์เหล็ก', 4.90, 552, 0],
                ['b_002', 'คิดแบบยิวทำแบบญี่ปุ่น', 'ฮอนดะ เคน', 230.00, 16, 'ธุรกิจ/บริหาร', 'https://images.weserv.nl/?url=https%3A%2F%2Fstorage.naiin.com%2Fsystem%2Fapplication%2Fbookstore%2Fresource%2Fproduct%2F202601%2F695304%2F6000124480_front_XXL.jpg%3Fv%3D1769769191', 'ยอดขายทะลุ 3 ล้านเล่มในญี่ปุ่น สร้างความมั่งคั่งและความสุขให้เกิดขึ้นในชีวิต', 4.90, 281, 0]
            ];

            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $sql = ($driver === 'sqlite')
                ? "INSERT OR REPLACE INTO books (id, title, author, price, stock, category, cover_url, description, rating, pages, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                : "INSERT INTO books (id, title, author, price, stock, category, cover_url, description, rating, pages, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=VALUES(title), author=VALUES(author), price=VALUES(price), stock=VALUES(stock), category=VALUES(category), cover_url=VALUES(cover_url), description=VALUES(description), rating=VALUES(rating), pages=VALUES(pages), is_featured=VALUES(is_featured)";

            $stmt = $pdo->prepare($sql);
            foreach ($fullBooks as $b) {
                $stmt->execute($b);
            }

            // Also ensure Safe Admin exists
            $uSql = ($driver === 'sqlite')
                ? "INSERT OR IGNORE INTO users (id, name, email, password, role) VALUES (?, ?, ?, ?, ?)"
                : "INSERT IGNORE INTO users (id, name, email, password, role) VALUES (?, ?, ?, ?, ?)";
            $uStmt = $pdo->prepare($uSql);
            $uStmt->execute(['usr_6a963ebaa49a9', 'Safe', 'Safe@gmail.com', 'Safe2649', 'admin']);
            $uStmt->execute(['usr_admin', 'ผู้ดูแลระบบ (Admin)', 'admin@caffebook.com', 'admin123', 'admin']);

            echo json_encode(['status' => 'success', 'message' => 'กู้คืนและนำเข้าข้อมูลหนังสือครบทั้ง 15 เล่มเรียบร้อยแล้ว!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
