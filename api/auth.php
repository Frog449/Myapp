<?php
require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents("php://input"), true) ?? $_POST;

if ($action === 'login') {
    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "กรุณากรอก อีเมล และ รหัสผ่าน"]);
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
        unset($user['password']);
        echo json_encode([
            "status" => "success",
            "message" => "เข้าสู่ระบบสำเร็จ",
            "user" => $user
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "อีเมลหรือรหัสผ่านไม่ถูกต้อง"]);
    }
    exit();
}

if ($action === 'register') {
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "กรุณากรอกข้อมูลให้ครบถ้วน"]);
        exit();
    }

    // Check existing email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "อีเมลนี้ถูกใช้งานแล้วในระบบ"]);
        exit();
    }

    $id = 'usr_' . uniqid();
    $role = 'customer'; // Default role is customer
    $createdAt = date('Y-m-d');

    $stmt = $pdo->prepare("INSERT INTO users (id, name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $success = $stmt->execute([$id, $name, $email, $password, $role, $createdAt]);

    if ($success) {
        echo json_encode([
            "status" => "success",
            "message" => "สมัครสมาชิกสำเร็จ",
            "user" => [
                "id" => $id,
                "name" => $name,
                "email" => $email,
                "role" => $role
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "ไม่สามารถสร้างบัญชีได้"]);
    }
    exit();
}

http_response_code(400);
echo json_encode(["status" => "error", "message" => "Invalid action"]);
