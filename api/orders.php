<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $user_id = $_GET['user_id'] ?? '';
    if (!empty($user_id)) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
    }
    $orders = $stmt->fetchAll();
    echo json_encode(["status" => "success", "data" => $orders]);
    exit();
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;

    $id = 'ord_' . uniqid();
    $user_id = trim($data['user_id'] ?? 'usr_guest');
    $user_name = trim($data['user_name'] ?? 'ลูกค้าทั่วไป');
    $total_amount = floatval($data['total_amount'] ?? 0);
    $status = 'ชำระเงินแล้ว';
    $order_date = date('Y-m-d H:i');
    
    // Process items detail
    $items = $data['items'] ?? [];
    if (is_array($items) && count($items) > 0) {
        $items_detail = json_encode($items, JSON_UNESCAPED_UNICODE);
    } else {
        $items_detail = is_string($data['items_detail'] ?? null) ? $data['items_detail'] : '[]';
    }

    if ($total_amount <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ยอดคำสั่งซื้อไม่ถูกต้อง"]);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO orders (id, user_id, user_name, total_amount, status, items_detail, order_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $success = $stmt->execute([$id, $user_id, $user_name, $total_amount, $status, $items_detail, $order_date]);

    // Stock update in database
    if (is_array($items)) {
        foreach ($items as $item) {
            $book_id = $item['id'] ?? '';
            $qty = intval($item['quantity'] ?? 1);
            if (!empty($book_id) && $qty > 0) {
                $uStmt = $pdo->prepare("UPDATE books SET stock = CASE WHEN stock >= ? THEN stock - ? ELSE 0 END WHERE id = ?");
                $uStmt->execute([$qty, $qty, $book_id]);
            }
        }
    }

    if ($success) {
        echo json_encode([
            "status" => "success",
            "message" => "สั่งซื้อหนังสือสำเร็จ ขอบคุณที่ใช้บริการ CaffeBook",
            "order_id" => $id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "สร้างคำสั่งซื้อล้มเหลว"]);
    }
    exit();
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
    $id = trim($data['id'] ?? '');
    $status = trim($data['status'] ?? 'จัดส่งแล้ว');

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ไม่ได้ระบุ Order ID"]);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $success = $stmt->execute([$status, $id]);

    if ($success) {
        echo json_encode(["status" => "success", "message" => "อัปเดตสถานะคำสั่งซื้อเรียบร้อยแล้ว"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "ไม่สามารถอัปเดตสถานะคำสั่งซื้อได้"]);
    }
    exit();
}

if ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = trim($_GET['id'] ?? ($data['id'] ?? ''));

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ไม่ได้ระบุ Order ID สำหรับลบ"]);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $success = $stmt->execute([$id]);

    if ($success) {
        echo json_encode(["status" => "success", "message" => "ลบรายการคำสั่งซื้อสำเร็จ"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "ไม่สามารถลบรายการคำสั่งซื้อได้"]);
    }
    exit();
}
