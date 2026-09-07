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

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
