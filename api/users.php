<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
    echo json_encode(["status" => "success", "data" => $users]);
    exit();
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? '';
    $role = $data['role'] ?? 'customer';

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ไม่ได้ระบุ User ID"]);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $success = $stmt->execute([$role, $id]);

    if ($success) {
        echo json_encode(["status" => "success", "message" => "อัปเดตสิทธิ์ผู้ใช้สำเร็จ"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "อัปเดตสิทธิ์ล้มเหลว"]);
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
        echo json_encode(["status" => "error", "message" => "ไม่ได้ระบุ User ID"]);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $success = $stmt->execute([$id]);

    if ($success) {
        echo json_encode(["status" => "success", "message" => "ลบผู้ใช้สำเร็จ"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "ไม่สามารถลบผู้ใช้ได้"]);
    }
    exit();
}
