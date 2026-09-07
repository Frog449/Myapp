<?php
require_once __DIR__ . '/db.php';

function cleanCoverUrl($url) {
    $url = trim($url);
    if (empty($url)) return 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500';

    if (strpos($url, 'images.weserv.nl') !== false) {
        return $url;
    }

    // Clean B2S / OFM CDN query parameters
    if (strpos($url, 'pim-cdn0.ofm.co.th') !== false) {
        if (($pos = strpos($url, '.jpg')) !== false) {
            $url = substr($url, 0, $pos + 4);
        } else if (($pos = strpos($url, '.png')) !== false) {
            $url = substr($url, 0, $pos + 4);
        }
    }

    // Fix double ?
    if (substr_count($url, '?') > 1) {
        $firstPos = strpos($url, '?');
        $before = substr($url, 0, $firstPos + 1);
        $after = str_replace('?', '&', substr($url, $firstPos + 1));
        $url = $before . $after;
    }

    // Wrap external domains with weserv CORS proxy so Flutter Web renders smoothly
    if (strpos($url, 'http') === 0 && strpos($url, '127.0.0.1') === false && strpos($url, 'images.unsplash.com') === false) {
        return 'https://images.weserv.nl/?url=' . urlencode($url);
    }

    return $url;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';

    $sql = "SELECT * FROM books WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (title LIKE ? OR author LIKE ? OR description LIKE ?)";
        $term = "%$search%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    if (!empty($category) && $category !== 'ทั้งหมด') {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    $sql .= " ORDER BY is_featured DESC, id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll();

    echo json_encode(["status" => "success", "data" => $books]);
    exit();
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;

    $id = 'b_' . uniqid();
    $title = trim($data['title'] ?? '');
    $author = trim($data['author'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $stock = intval($data['stock'] ?? 0);
    $category = trim($data['category'] ?? 'ทั่วไป');
    $raw_cover = trim($data['cover_url'] ?? '');
    $cover_url = cleanCoverUrl($raw_cover);
    $description = trim($data['description'] ?? '');
    $rating = floatval($data['rating'] ?? 4.8);
    $pages = intval($data['pages'] ?? 200);
    $is_featured = isset($data['is_featured']) && ($data['is_featured'] == 1 || $data['is_featured'] === true) ? 1 : 0;

    if (empty($title) || empty($author) || $price <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "กรุณากรอกข้อมูล ชื่อหนังสือ ผู้แต่ง และราคา ให้ถูกต้อง"]);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO books (id, title, author, price, stock, category, cover_url, description, rating, pages, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $success = $stmt->execute([$id, $title, $author, $price, $stock, $category, $cover_url, $description, $rating, $pages, $is_featured]);

    if ($success) {
        echo json_encode([
            "status" => "success",
            "message" => "เพิ่มหนังสือสำเร็จ",
            "data" => [
                "id" => $id, "title" => $title, "author" => $author, "price" => $price,
                "stock" => $stock, "category" => $category, "cover_url" => $cover_url,
                "description" => $description, "rating" => $rating, "pages" => $pages,
                "is_featured" => $is_featured
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "ไม่สามารถเพิ่มหนังสือได้"]);
    }
    exit();
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    $id = trim($data['id'] ?? '');
    $title = trim($data['title'] ?? '');
    $author = trim($data['author'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $stock = intval($data['stock'] ?? 0);
    $category = trim($data['category'] ?? 'ทั่วไป');
    $raw_cover = trim($data['cover_url'] ?? '');
    $cover_url = cleanCoverUrl($raw_cover);
    $description = trim($data['description'] ?? '');
    $rating = floatval($data['rating'] ?? 4.8);
    $pages = intval($data['pages'] ?? 200);
    $is_featured = isset($data['is_featured']) && ($data['is_featured'] == 1 || $data['is_featured'] === true) ? 1 : 0;

    if (empty($id) || empty($title) || empty($author)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบถ้วนสำหรับการแก้ไข"]);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, price=?, stock=?, category=?, cover_url=?, description=?, rating=?, pages=?, is_featured=? WHERE id=?");
    $success = $stmt->execute([$title, $author, $price, $stock, $category, $cover_url, $description, $rating, $pages, $is_featured, $id]);

    if ($success) {
        echo json_encode(["status" => "success", "message" => "อัปเดตข้อมูลหนังสือสำเร็จ"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "ไม่สามารถอัปเดตหนังสือได้"]);
    }
    exit();
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? '';
    }

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ไม่ได้ระบุ ID หนังสือ"]);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $success = $stmt->execute([$id]);

    if ($success) {
        echo json_encode(["status" => "success", "message" => "ลบหนังสือเรียบร้อยแล้ว"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "ไม่สามารถลบหนังสือได้"]);
    }
    exit();
}
