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

        case 'update_price':
            $id = trim($_POST['id'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            if (empty($id) || $price <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุราคาที่ถูกต้อง']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE books SET price = ? WHERE id = ?");
            $stmt->execute([$price, $id]);
            echo json_encode(['status' => 'success', 'price' => $price, 'message' => 'อัปเดตราคาสำเร็จ ฿' . number_format($price, 2)]);
            break;

        case 'update_category':
            $id = trim($_POST['id'] ?? '');
            $category = trim($_POST['category'] ?? '');
            if (empty($id) || empty($category)) {
                echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE books SET category = ? WHERE id = ?");
            $stmt->execute([$category, $id]);
            echo json_encode(['status' => 'success', 'category' => $category, 'message' => "เปลี่ยนหมวดหมู่เป็น '{$category}' สำเร็จ"]);
            break;

        case 'quick_add_sample':
            $samples = [
                ['title' => 'กาแฟกับหนังสือ: ศิลปะแห่งการใช้ชีวิตช้าๆ', 'author' => 'CaffeBook Studio', 'price' => 280, 'stock' => 25, 'category' => 'พัฒนาตนเอง', 'cover' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500', 'desc' => 'เรื่องราวความสุขง่ายๆ ในร้านกาแฟและหนังสือดีๆ สักเล่ม'],
                ['title' => 'Deep Work พลังแห่งการจดจ่อ', 'author' => 'Cal Newport', 'price' => 320, 'stock' => 18, 'category' => 'ธุรกิจ/บริหาร', 'cover' => 'https://images.unsplash.com/photo-1592496431122-2349e0fbc666?w=500', 'desc' => 'วิธีฝึกสมาธิในการทำงานขั้นสูงเพื่อสร้างสรรค์ผลงานที่ยอดเยี่ยม'],
                ['title' => 'มหัศจรรย์ร้านหนังสือยามค่ำคืน', 'author' => 'Aoi Morita', 'price' => 265, 'stock' => 30, 'category' => 'นิยาย', 'cover' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=500', 'desc' => 'เรื่องราวอบอุ่นหัวใจของร้านหนังสือเปิดเฉพาะหลังเที่ยงคืน'],
                ['title' => 'เจ้าชายน้อย (The Little Prince)', 'author' => 'Antoine de Saint-Exupéry', 'price' => 195, 'stock' => 40, 'category' => 'วรรณกรรม', 'cover' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=500', 'desc' => 'วรรณกรรมเยาวชนคลาสสิกระดับโลกแปลกใหม่ที่ครองใจคนทั่วโลก'],
                ['title' => 'Demon Slayer: คิมัตสึ โนะ ไอยบะ', 'author' => 'Koyoharu Gotouge', 'price' => 95, 'stock' => 50, 'category' => 'การ์ตูน/มังงะ', 'cover' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=500', 'desc' => 'การเดินทางต่อสู้กับเหล่าอสูรของทันจิโร่เพื่อช่วยเหลือน้องสาว']
            ];
            $sample = $samples[array_rand($samples)];
            $id = 'b_' . uniqid();
            $stmt = $pdo->prepare("INSERT INTO books (id, title, author, price, stock, category, cover_url, description, rating, pages, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $sample['title'], $sample['author'], $sample['price'], $sample['stock'], $sample['category'], cleanCoverUrlInternal($sample['cover']), $sample['desc'], 4.9, 240, 1]);
            echo json_encode(['status' => 'success', 'message' => "เพิ่มสินค้าตัวอย่าง '{$sample['title']}' เรียบร้อยแล้ว!"]);
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
            echo json_encode(['status' => 'success', 'status_val' => $status, 'message' => "อัปเดตสถานะออเดอร์ #{$id} เป็น '{$status}' เรียบร้อยแล้ว"]);
            break;

        case 'bulk_update_order_status':
            $ids = json_decode($_POST['ids'] ?? '[]', true);
            $status = trim($_POST['status'] ?? '');
            if (empty($ids) || !is_array($ids) || empty($status)) {
                echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
                exit;
            }
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$status], $ids);
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id IN ($inQuery)");
            $stmt->execute($params);
            $count = count($ids);
            echo json_encode(['status' => 'success', 'message' => "เปลี่ยนสถานะคำสั่งซื้อ {$count} รายการเป็น '{$status}' เรียบร้อยแล้ว"]);
            break;

        case 'delete_order':
            $id = trim($_POST['id'] ?? $_GET['id'] ?? '');
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID คำสั่งซื้อ']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => "ลบรายการสั่งซื้อ #{$id} เรียบร้อยแล้ว"]);
            break;

        case 'bulk_delete_orders':
            $ids = json_decode($_POST['ids'] ?? '[]', true);
            if (empty($ids) || !is_array($ids)) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่มีรายการที่เลือก']);
                exit;
            }
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id IN ($inQuery)");
            $stmt->execute($ids);
            $count = count($ids);
            echo json_encode(['status' => 'success', 'message' => "ลบคำสั่งซื้อที่เลือกจำนวน {$count} รายการเรียบร้อยแล้ว"]);
            break;

        case 'quick_add_sample_order':
            $bStmt = $pdo->query("SELECT id, title, price, cover_url FROM books LIMIT 3");
            $sampleBooks = $bStmt->fetchAll();
            if (empty($sampleBooks)) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่มีหนังสือในคลัง กรุณาเพิ่มหนังสือก่อน']);
                exit;
            }
            $orderId = 'CB-' . rand(100000, 999999);
            $customerNames = ['คุณสมชาย รักษ์ดี', 'คุณกนกวรรณ จันทร์เพ็ญ', 'คุณเอกชัย ศิริสุข', 'คุณศิริพร สว่างจิต', 'คุณธนพล มั่งคั่ง', 'Safe User'];
            $custName = $customerNames[array_rand($customerNames)];
            $payments = ['สแกน QR Code', 'บัตรเครดิต/เดบิต', 'เก็บเงินปลายทาง'];
            $payMethod = $payments[array_rand($payments)];
            $statuses = ['รอชำระเงิน', 'ชำระเงินแล้ว', 'กำลังจัดส่ง'];
            $initStatus = ($payMethod === 'เก็บเงินปลายทาง') ? 'รอเก็บเงินปลายทาง' : $statuses[array_rand($statuses)];

            $orderItems = [];
            $totalAmount = 0;
            // pick 1-2 random books
            $pickCount = min(count($sampleBooks), rand(1, 2));
            shuffle($sampleBooks);
            for ($i = 0; $i < $pickCount; $i++) {
                $sb = $sampleBooks[$i];
                $qty = rand(1, 2);
                $orderItems[] = [
                    'id' => $sb['id'],
                    'title' => $sb['title'],
                    'price' => (float)$sb['price'],
                    'quantity' => $qty,
                    'cover_url' => $sb['cover_url']
                ];
                $totalAmount += $qty * (float)$sb['price'];
            }

            $stmt = $pdo->prepare("INSERT INTO orders (id, user_id, user_name, total_amount, status, payment_method, items_detail, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $orderId,
                'usr_customer',
                $custName,
                $totalAmount,
                $initStatus,
                $payMethod,
                json_encode($orderItems, JSON_UNESCAPED_UNICODE),
                date('Y-m-d H:i:s')
            ]);

            echo json_encode(['status' => 'success', 'message' => "สร้างคำสั่งซื้อตัวอย่าง #{$orderId} ยอดรวม ฿" . number_format($totalAmount, 2) . " สำเร็จ!"]);
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
