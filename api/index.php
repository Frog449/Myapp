<?php
session_start();
require_once __DIR__ . '/db.php';
header("Content-Type: text/html; charset=UTF-8");

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_user']);
    session_destroy();
    header("Location: index.php");
    exit();
}

// Handle Login POST
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_email'])) {
    $email = trim($_POST['login_email'] ?? '');
    $password = trim($_POST['login_password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
            if ($user['role'] === 'admin') {
                $_SESSION['admin_user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                header("Location: index.php");
                exit();
            } else {
                $login_error = 'บัญชีนี้ไม่มีสิทธิ์เข้าถึงระบบผู้ดูแล (ไม่ใช่ Role Admin)';
            }
        } else {
            $login_error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
        }
    } else {
        $login_error = 'กรุณากรอกอีเมลและรหัสผ่านให้ครบถ้วน';
    }
}

// If Not Logged In, Render Modern Luxury Admin Login Page
if (!isset($_SESSION['admin_user'])) {
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบผู้ดูแล - CaffeBook Admin</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #6F4E37;
            --primary-dark: #3E2723;
            --primary-hover: #5A3D28;
            --primary-light: #C4A482;
            --accent: #E67E22;
            --bg-main: #F8F5F0;
            --card-bg: #FFFFFF;
            --text-main: #2C2523;
            --text-muted: #7A726D;
            --border-color: #EAE3D9;
            --shadow-lg: 0 20px 45px rgba(62, 39, 35, 0.12);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Prompt', 'Outfit', sans-serif; }
        body {
            background: linear-gradient(135deg, #F8F5F0 0%, #EDE4DB 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 440px;
            padding: 2.5rem 2rem;
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #6F4E37, #E67E22, #C4A482);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            box-shadow: 0 8px 20px rgba(111, 78, 55, 0.25);
        }
        .login-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 4px;
        }
        .login-subtitle {
            font-size: 0.88rem;
            color: var(--text-muted);
        }
        .form-group {
            margin-bottom: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-main);
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrapper i.input-icon {
            position: absolute;
            left: 14px;
            color: var(--primary-light);
            font-size: 1rem;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.95rem;
            background: #FAF8F5;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(111, 78, 55, 0.15);
        }
        .toggle-password {
            position: absolute;
            right: 14px;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 0.95rem;
        }
        .alert-error {
            background: #FDEDEC;
            border: 1px solid #F5B7B1;
            color: #C0392B;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 0.88rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(111, 78, 55, 0.25);
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(111, 78, 55, 0.35);
        }
        .quick-login-box {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px dashed var(--border-color);
            text-align: center;
        }
        .quick-login-title {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .quick-btn-group {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-quick {
            padding: 6px 12px;
            background: #F5EBE6;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            font-size: 0.78rem;
            color: var(--primary-dark);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-quick:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo"><i class="fa-solid fa-mug-hot"></i></div>
            <h1 class="login-title">Caffe<b>Book</b> Admin</h1>
            <p class="login-subtitle">ระบบจัดการสต็อกหนังสือและคำสั่งซื้อหลังบ้าน</p>
        </div>

        <?php if (!empty($login_error)): ?>
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($login_error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <div class="form-group">
                <label for="login_email">อีเมลผู้ดูแลระบบ</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="email" id="login_email" name="login_email" class="form-control" placeholder="เช่น Safe@gmail.com" required autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <label for="login_password">รหัสผ่าน</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="login_password" name="login_password" class="form-control" placeholder="กรอกรหัสผ่าน" required autocomplete="current-password">
                    <i class="fa-solid fa-eye toggle-password" onclick="togglePass()"></i>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ
            </button>
        </form>

        <div class="quick-login-box">
            <div class="quick-login-title">คลิกเพื่อกรอกบัญชีผู้ดูแลอัตโนมัติ</div>
            <div class="quick-btn-group">
                <button type="button" class="btn-quick" onclick="fillAccount('Safe@gmail.com', 'Safe2649')">
                    👑 Safe (Safe@gmail.com)
                </button>
                <button type="button" class="btn-quick" onclick="fillAccount('admin@caffebook.com', 'admin123')">
                    ☕ Admin ทั่วไป
                </button>
            </div>
        </div>
    </div>

    <script>
        function togglePass() {
            const input = document.getElementById('login_password');
            const icon = document.querySelector('.toggle-password');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        function fillAccount(email, password) {
            document.getElementById('login_email').value = email;
            document.getElementById('login_password').value = password;
        }
    </script>
</body>
</html>
<?php
    exit();
}

$currentUser = $_SESSION['admin_user'];

// Fetch Statistics
$total_revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'ยกเลิก'")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$low_stock_books = $pdo->query("SELECT COUNT(*) FROM books WHERE stock <= 5 AND stock > 0")->fetchColumn();
$out_of_stock_books = $pdo->query("SELECT COUNT(*) FROM books WHERE stock = 0")->fetchColumn();
$featured_books = $pdo->query("SELECT COUNT(*) FROM books WHERE is_featured = 1")->fetchColumn();
$total_stock_units = $pdo->query("SELECT COALESCE(SUM(stock), 0) FROM books")->fetchColumn();
$avg_price = $pdo->query("SELECT COALESCE(AVG(price), 0) FROM books")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Fetch Data
$books = $pdo->query("SELECT * FROM books ORDER BY is_featured DESC, id DESC")->fetchAll();
$orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
$users = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();

// Category counts for dynamic filter chips
$category_counts_raw = $pdo->query("SELECT category, COUNT(*) as cnt FROM books GROUP BY category")->fetchAll(PDO::FETCH_KEY_PAIR);
$categories = ['ทั้งหมด', 'นิยาย', 'พัฒนาตนเอง', 'ธุรกิจ/บริหาร', 'วรรณกรรม', 'การ์ตูน/มังงะ'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaffeBook - ระบบจัดการหลังบ้าน (Web Admin)</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #6F4E37;
            --primary-dark: #3E2723;
            --primary-hover: #5A3D28;
            --primary-light: #C4A482;
            --primary-soft: #F5EBE6;
            --accent: #E67E22;
            --accent-gold: #F39C12;
            --bg-main: #F8F5F0;
            --card-bg: #FFFFFF;
            --text-main: #2C2523;
            --text-muted: #7A726D;
            --text-light: #A09891;
            --border-color: #EAE3D9;
            --border-hover: #D0C5B8;
            --success: #27AE60;
            --success-soft: #E8F8F0;
            --warning: #E67E22;
            --warning-soft: #FEF5E7;
            --danger: #E74C3C;
            --danger-soft: #FDEDEC;
            --info: #2980B9;
            --info-soft: #EBF5FB;
            --shadow-xs: 0 1px 3px rgba(62, 39, 35, 0.05);
            --shadow-sm: 0 4px 12px rgba(62, 39, 35, 0.06);
            --shadow-md: 0 8px 24px rgba(62, 39, 35, 0.08);
            --shadow-lg: 0 16px 36px rgba(62, 39, 35, 0.12);
            --shadow-hover: 0 12px 28px rgba(111, 78, 55, 0.18);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 18px;
            --radius-full: 9999px;
            --transition-fast: 0.15s ease;
            --transition-normal: 0.25s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Prompt', 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #F1ECE4; }
        ::-webkit-scrollbar-thumb { background: #C4A482; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #6F4E37; }

        /* Top Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 0.85rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 1.35rem;
            letter-spacing: -0.3px;
        }

        .brand-logo .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            box-shadow: 0 4px 10px rgba(111, 78, 55, 0.25);
            transition: transform 0.2s ease;
        }

        .brand-logo:hover .logo-icon {
            transform: scale(1.05) rotate(-3deg);
        }

        .brand-logo span b {
            color: var(--accent);
        }

        .nav-status {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--success-soft);
            color: var(--success);
            padding: 6px 14px;
            border-radius: var(--radius-full);
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid rgba(39, 174, 96, 0.2);
        }

        .status-badge .dot {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 1.8s infinite;
        }

        .btn-nav-apk {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #FDFBF7;
            color: var(--primary-dark);
            padding: 6px 14px;
            border-radius: var(--radius-full);
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-nav-apk:hover {
            background: var(--primary-soft);
            border-color: var(--primary-light);
            color: var(--primary);
            transform: translateY(-1px);
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(39, 174, 96, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(39, 174, 96, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(39, 174, 96, 0); }
        }

        /* Layout */
        .admin-container {
            display: flex;
            flex: 1;
            max-width: 1520px;
            margin: 0 auto;
            width: 100%;
            padding: 1.5rem 2rem;
            gap: 1.8rem;
        }

        /* Sidebar Tabs */
        .sidebar {
            width: 250px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.92rem;
            transition: all var(--transition-normal);
            cursor: pointer;
            border: 1px solid transparent;
            position: relative;
        }

        .nav-item:hover {
            background: rgba(111, 78, 55, 0.07);
            color: var(--primary-dark);
            transform: translateX(4px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 14px rgba(111, 78, 55, 0.25);
            font-weight: 600;
        }

        .nav-item i {
            font-size: 1.05rem;
            width: 22px;
            text-align: center;
        }

        .nav-item .badge-count {
            margin-left: auto;
            font-size: 0.72rem;
            padding: 2px 7px;
            border-radius: var(--radius-full);
            background: rgba(255, 255, 255, 0.25);
            color: white;
            font-weight: 600;
        }

        .nav-item:not(.active) .badge-count {
            background: #EAE3D9;
            color: var(--text-muted);
        }

        .sidebar-divider {
            height: 1px;
            background: var(--border-color);
            margin: 0.8rem 0;
        }

        .api-info-card {
            background: linear-gradient(135deg, #FFF9F2, #FFF3E6);
            border: 1px solid #F8E2CD;
            border-radius: var(--radius-md);
            padding: 1.1rem;
            font-size: 0.84rem;
            color: #8C531B;
            box-shadow: var(--shadow-xs);
        }

        .api-info-card h4 {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .api-info-card code {
            display: block;
            background: rgba(255,255,255,0.8);
            padding: 4px 8px;
            border-radius: 6px;
            margin-top: 4px;
            font-size: 0.78rem;
            color: var(--primary-dark);
            word-break: break-all;
            border: 1px solid #EFE2D3;
        }

        /* Content Area */
        .main-content {
            flex: 1;
            min-width: 0;
        }

        .tab-section {
            display: none;
            animation: fadeIn 0.25s ease-out;
        }

        .tab-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1.8rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.3rem 1.4rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 1.1rem;
            transition: all var(--transition-normal);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--border-hover);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .stat-icon.revenue { background: #EBF8F2; color: #27AE60; }
        .stat-icon.orders { background: #EBF5FB; color: #2980B9; }
        .stat-icon.books { background: #F5EEF8; color: #8E44AD; }
        .stat-icon.stock { background: #FEF5E7; color: #D35400; }
        .stat-icon.users { background: #EAECEE; color: #5D6D7E; }

        .stat-info h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.2;
            font-family: 'Outfit', 'Prompt', sans-serif;
        }

        .stat-info p {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Content Card */
        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.6rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.8rem;
            position: relative;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.2rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: var(--primary);
        }

        .card-title .title-count {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            background: var(--bg-main);
            padding: 2px 10px;
            border-radius: var(--radius-full);
            border: 1px solid var(--border-color);
        }

        .card-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Books KPI Summary Mini-Bar */
        .book-kpi-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 0.8rem;
            margin-bottom: 1.4rem;
        }

        .kpi-mini-card {
            background: #FCFAF7;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0.9rem 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all var(--transition-fast);
            user-select: none;
        }

        .kpi-mini-card:hover {
            background: white;
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-xs);
        }

        .kpi-mini-card.active {
            background: var(--primary-soft);
            border-color: var(--primary);
            box-shadow: 0 0 0 1.5px var(--primary);
        }

        .kpi-mini-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .kpi-mini-icon.all { background: #EFECE6; color: var(--primary-dark); }
        .kpi-mini-icon.stock { background: #E8F8F0; color: var(--success); }
        .kpi-mini-icon.low { background: #FEF5E7; color: #D35400; }
        .kpi-mini-icon.out { background: #FDEDEC; color: var(--danger); }
        .kpi-mini-icon.feat { background: #FFF8E7; color: var(--accent-gold); }

        .kpi-mini-info .val {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
            font-family: 'Outfit', 'Prompt', sans-serif;
            line-height: 1.2;
        }

        .kpi-mini-info .lbl {
            font-size: 0.76rem;
            color: var(--text-muted);
        }

        /* Advanced Search & Filter Toolbar */
        .toolbar-box {
            background: #FAF8F5;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        .toolbar-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-box-wrapper {
            position: relative;
            flex: 1;
            min-width: 260px;
        }

        .search-box-wrapper input {
            width: 100%;
            padding: 10px 38px 10px 38px;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            background: white;
            color: var(--text-main);
            outline: none;
            transition: all var(--transition-fast);
        }

        .search-box-wrapper input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(111, 78, 55, 0.12);
        }

        .search-box-wrapper .icon-search {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .search-box-wrapper .clear-search-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: #EAE3D9;
            border: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: var(--text-muted);
            cursor: pointer;
        }

        .search-box-wrapper .clear-search-btn:hover {
            background: var(--text-muted);
            color: white;
        }

        .filter-controls-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .select-filter {
            padding: 9px 14px;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            background: white;
            color: var(--text-main);
            outline: none;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .select-filter:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(111, 78, 55, 0.1);
        }

        .view-mode-toggle {
            display: inline-flex;
            background: #EBE5DC;
            padding: 3px;
            border-radius: var(--radius-md);
            gap: 2px;
        }

        .view-btn {
            background: none;
            border: none;
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all var(--transition-fast);
        }

        .view-btn.active {
            background: white;
            color: var(--primary-dark);
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        /* Category Chips Bar */
        .category-chips-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        .category-chip {
            background: white;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-full);
            padding: 6px 14px;
            font-size: 0.84rem;
            font-weight: 500;
            color: #5C554F;
            cursor: pointer;
            transition: all var(--transition-fast);
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            user-select: none;
        }

        .category-chip .chip-cnt {
            font-size: 0.72rem;
            padding: 1px 6px;
            border-radius: var(--radius-full);
            background: #EFECE6;
            color: var(--text-muted);
        }

        .category-chip:hover {
            border-color: var(--primary);
            color: var(--primary-dark);
            background: #FDFCFB;
            transform: translateY(-1px);
        }

        .category-chip.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-color: var(--primary-dark);
            color: white;
            box-shadow: 0 3px 10px rgba(111, 78, 55, 0.22);
            font-weight: 600;
        }

        .category-chip.active .chip-cnt {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all var(--transition-fast);
            text-decoration: none;
            user-select: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 12px rgba(111, 78, 55, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-hover), var(--primary-dark));
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(111, 78, 55, 0.3);
        }

        .btn-secondary {
            background: #EFECE6;
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: #E5E0D6;
            color: var(--primary-dark);
        }

        .btn-danger {
            background: var(--danger-soft);
            color: var(--danger);
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        .btn-danger:hover {
            background: var(--danger);
            color: white;
        }

        .btn-success {
            background: var(--success-soft);
            color: var(--success);
            border: 1px solid rgba(39, 174, 96, 0.2);
        }

        .btn-success:hover {
            background: var(--success);
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 8px;
        }

        .btn-icon {
            width: 34px;
            height: 34px;
            padding: 0;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.88rem;
        }

        /* Table Design */
        .table-responsive {
            overflow-x: auto;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            background: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: #FAF7F2;
            padding: 12px 16px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
            letter-spacing: 0.2px;
        }

        td {
            padding: 12px 16px;
            font-size: 0.88rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            transition: background 0.15s ease;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover td {
            background: #FDFBF8;
        }

        /* Checkbox */
        .custom-chk {
            width: 17px;
            height: 17px;
            accent-color: var(--primary);
            cursor: pointer;
            border-radius: 4px;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 0.76rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-category { background: #F5EBE1; color: #7B4B24; border: 1px solid #EADBCE; }
        .badge-success { background: #E8F8F0; color: #27AE60; border: 1px solid rgba(39,174,96,0.2); }
        .badge-warning { background: #FEF5E7; color: #D35400; border: 1px solid rgba(211,84,0,0.2); }
        .badge-danger { background: #FDEDEC; color: #E74C3C; border: 1px solid rgba(231,76,60,0.2); }
        .badge-info { background: #EBF5FB; color: #2980B9; border: 1px solid rgba(41,128,185,0.2); }
        .badge-admin { background: #EDE7F6; color: #673AB7; }
        .badge-customer { background: #E0F2F1; color: #00796B; }

        /* Book Title Cell */
        .book-thumb {
            width: 44px;
            height: 60px;
            border-radius: 6px;
            object-fit: cover;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
            background: #EDE6DD;
            flex-shrink: 0;
            transition: transform 0.2s ease;
            cursor: pointer;
        }

        .book-thumb:hover {
            transform: scale(1.15) rotate(-2deg);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .book-title-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .book-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .book-title {
            font-weight: 600;
            color: var(--text-main);
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: pointer;
            transition: color 0.15s ease;
        }

        .book-title:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        .book-author {
            font-size: 0.78rem;
            color: var(--text-muted);
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .book-id-badge {
            font-size: 0.68rem;
            font-family: monospace;
            color: var(--text-light);
        }

        /* Interactive Stock Stepper in Table */
        .stock-control-cell {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .stock-step-btn {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-main);
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .stock-step-btn:hover {
            background: var(--primary-soft);
            border-color: var(--primary);
            color: var(--primary);
            transform: scale(1.1);
        }

        .stock-step-btn:active {
            transform: scale(0.95);
        }

        /* 1-Click Interactive Star */
        .star-toggle-btn {
            background: none;
            border: none;
            font-size: 1.15rem;
            cursor: pointer;
            padding: 4px;
            transition: transform 0.2s ease;
            outline: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .star-toggle-btn.featured {
            color: var(--accent-gold);
            filter: drop-shadow(0 2px 4px rgba(243, 156, 18, 0.3));
        }

        .star-toggle-btn.not-featured {
            color: #D3C9BE;
        }

        .star-toggle-btn:hover {
            transform: scale(1.25);
        }

        .star-toggle-btn:active {
            transform: scale(0.9);
        }

        /* Grid View (Card Catalog) */
        .books-grid-view {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 1.4rem;
        }

        .book-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-xs);
            transition: all var(--transition-normal);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary-light);
        }

        .book-card-cover-wrapper {
            position: relative;
            width: 100%;
            height: 220px;
            background: #EFE8DF;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .book-card-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .book-card:hover .book-card-cover {
            transform: scale(1.06);
        }

        .book-card-badges {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            pointer-events: none;
        }

        .book-card-badges > * {
            pointer-events: auto;
        }

        .book-card-star {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .book-card-body {
            padding: 1.1rem;
            display: flex;
            flex-direction: column;
            flex: 1;
            gap: 6px;
        }

        .book-card-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-main);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.35;
            min-height: 2.7em;
            cursor: pointer;
        }

        .book-card-title:hover {
            color: var(--primary);
        }

        .book-card-author {
            font-size: 0.8rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .book-card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .book-card-price {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary-dark);
            font-family: 'Outfit', 'Prompt', sans-serif;
        }

        .stock-progress-bar {
            width: 100%;
            height: 5px;
            background: #EFECE6;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 4px;
        }

        .stock-progress-fill {
            height: 100%;
            border-radius: 3px;
        }

        .book-card-footer {
            padding: 0.8rem 1.1rem;
            border-top: 1px solid var(--border-color);
            background: #FCFAF7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Floating Bulk Actions Bar */
        .bulk-actions-bar {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #2C2523;
            color: white;
            padding: 10px 22px;
            border-radius: var(--radius-full);
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.35);
            display: flex;
            align-items: center;
            gap: 16px;
            z-index: 999;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            opacity: 0;
            pointer-events: none;
        }

        .bulk-actions-bar.active {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .bulk-count-badge {
            background: var(--accent);
            color: white;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: var(--radius-full);
            font-size: 0.82rem;
        }

        /* Empty State */
        .empty-state {
            padding: 3.5rem 1.5rem;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            color: #D5CCC0;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.15rem;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        .empty-state p {
            font-size: 0.88rem;
            margin-bottom: 1.2rem;
        }

        /* Modals */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(35, 25, 20, 0.65);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1.2rem;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 780px;
            max-height: 92vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            animation: popIn 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
        }

        @keyframes popIn {
            from { transform: scale(0.92); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-header {
            padding: 1.3rem 1.6rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #FAF8F5;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-header h3 {
            font-size: 1.2rem;
            color: var(--primary-dark);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--text-muted);
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .modal-close:hover {
            background: #EAE3D9;
            color: var(--danger);
        }

        .modal-body {
            padding: 1.6rem;
        }

        /* 2-Column Form Modal Layout */
        .modal-form-grid {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .modal-form-grid {
                grid-template-columns: 1fr;
            }
        }

        .cover-picker-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .cover-preview-card {
            width: 180px;
            height: 250px;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            background: #EDE6DD;
            border: 2px dashed var(--border-hover);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .cover-preview-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preset-covers-bar {
            width: 100%;
        }

        .preset-covers-bar p {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 6px;
            text-align: center;
        }

        .preset-thumbs {
            display: flex;
            gap: 6px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .preset-thumb-btn {
            width: 34px;
            height: 48px;
            border-radius: 4px;
            overflow: hidden;
            border: 1.5px solid var(--border-color);
            cursor: pointer;
            padding: 0;
            background: none;
            transition: all 0.15s ease;
        }

        .preset-thumb-btn img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preset-thumb-btn:hover {
            transform: scale(1.15);
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .form-fields-column {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            outline: none;
            transition: all var(--transition-fast);
            background: white;
            color: var(--text-main);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(111, 78, 55, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Toggle Switch */
        .toggle-switch-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: #FAF8F5;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            cursor: pointer;
        }

        .switch-input {
            appearance: none;
            width: 44px;
            height: 24px;
            background: #D5CCC0;
            border-radius: var(--radius-full);
            position: relative;
            cursor: pointer;
            outline: none;
            transition: background 0.2s ease;
        }

        .switch-input::before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: white;
            top: 3px;
            left: 3px;
            transition: transform 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .switch-input:checked {
            background: var(--accent-gold);
        }

        .switch-input:checked::before {
            transform: translateX(20px);
        }

        .modal-footer {
            padding: 1.1rem 1.6rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: #FAF8F5;
            position: sticky;
            bottom: 0;
        }

        /* Book Quick View Modal */
        .preview-modal-body {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 1.8rem;
            padding: 1.6rem;
        }

        @media (max-width: 600px) {
            .preview-modal-body {
                grid-template-columns: 1fr;
            }
        }

        .preview-cover-hd {
            width: 100%;
            height: 310px;
            border-radius: var(--radius-md);
            object-fit: cover;
            box-shadow: 0 10px 24px rgba(0,0,0,0.18);
        }

        .preview-details-wrap {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .preview-details-wrap h2 {
            font-size: 1.4rem;
            color: var(--primary-dark);
            line-height: 1.3;
        }

        .preview-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .preview-price-box {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-dark);
            font-family: 'Outfit', 'Prompt', sans-serif;
        }

        .preview-desc-box {
            background: #FAF7F2;
            border-radius: var(--radius-md);
            padding: 1rem;
            font-size: 0.88rem;
            line-height: 1.6;
            color: #4A3E39;
            border: 1px solid var(--border-color);
            max-height: 150px;
            overflow-y: auto;
        }

        /* Responsive Layout Breakpoints */
        @media (max-width: 992px) {
            .admin-container {
                flex-direction: column;
                padding: 1rem;
                gap: 1rem;
            }
            .sidebar {
                width: 100%;
                flex-direction: row;
                overflow-x: auto;
                padding-bottom: 6px;
            }
            .nav-item {
                white-space: nowrap;
            }
            .sidebar-divider, .api-info-card {
                display: none;
            }
            .navbar {
                padding: 0.8rem 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <header class="navbar">
        <a href="index.php" class="brand-logo">
            <div class="logo-icon"><i class="fa-solid fa-mug-hot"></i></div>
            <span>Caffe<b>Book</b> Admin</span>
        </a>
        <div class="nav-status">
            <div class="status-badge">
                <div class="dot"></div>
                <span>Server & Database Online</span>
            </div>
            <button class="btn btn-sm" onclick="syncFullCatalog()" style="background: #FEF5E7; color: #E67E22; border: 1px solid #FADBD8; font-weight: 600;" title="กู้คืนและนำเข้าหนังสือทั้ง 15 เล่มจากฐานข้อมูลเดิม">
                <i class="fa-solid fa-arrows-rotate"></i> ซิงค์หนังสือ 15 เล่ม
            </button>
            <a href="download_apk.php" class="btn-nav-apk">
                <i class="fa-solid fa-mobile-screen-button"></i> โหลดแอป APK
            </a>
            <button class="btn btn-primary btn-sm" onclick="openAddBookModal()">
                <i class="fa-solid fa-plus"></i> เพิ่มหนังสือ
            </button>
            <div class="user-profile-badge" style="display: flex; align-items: center; gap: 8px; margin-left: 6px; padding: 4px 12px; background: #F5EBE6; border-radius: 20px; font-size: 0.85rem; color: #3E2723; font-weight: 600; border: 1px solid #EAE3D9;">
                <i class="fa-solid fa-circle-user" style="color: #6F4E37; font-size: 1.15rem;"></i>
                <span><?= htmlspecialchars($currentUser['name'] ?? 'Admin') ?></span>
                <a href="index.php?action=logout" style="color: #E74C3C; text-decoration: none; margin-left: 4px; padding: 2px 6px; border-radius: 6px;" title="ออกจากระบบ">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="nav-item active" onclick="switchTab('dashboard', this)">
                <i class="fa-solid fa-chart-pie"></i>
                <span>ภาพรวม</span>
            </div>
            <div class="nav-item" onclick="switchTab('books', this)">
                <i class="fa-solid fa-book-open"></i>
                <span>จัดการหนังสือ</span>
                <span class="badge-count" id="sidebarBookCount"><?= count($books) ?></span>
            </div>
            <div class="nav-item" onclick="switchTab('orders', this)">
                <i class="fa-solid fa-receipt"></i>
                <span>รายการคำสั่งซื้อ</span>
                <span class="badge-count"><?= count($orders) ?></span>
            </div>
            <div class="nav-item" onclick="switchTab('users', this)">
                <i class="fa-solid fa-users-gear"></i>
                <span>จัดการผู้ใช้งาน</span>
                <span class="badge-count"><?= count($users) ?></span>
            </div>
            <div class="nav-item" onclick="switchTab('api-info', this)">
                <i class="fa-solid fa-code"></i>
                <span>การเชื่อมต่อ Flutter (API)</span>
            </div>

            <div class="sidebar-divider"></div>

            <div class="api-info-card">
                <h4><i class="fa-solid fa-mobile-screen"></i> ทดสอบบนมือถือ</h4>
                <p>เปิด URL นี้ในเบราว์เซอร์มือถือ:</p>
                <code>http://<?= $_SERVER['HTTP_HOST'] ?></code>
                <a href="download_apk.php" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;color:var(--primary);font-weight:600;text-decoration:none;font-size:0.84rem;">
                    <i class="fa-solid fa-download"></i> โหลดไฟล์ APK ทันที
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            
            <!-- TAB 1: DASHBOARD -->
            <section id="tab-dashboard" class="tab-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon revenue"><i class="fa-solid fa-coins"></i></div>
                        <div class="stat-info">
                            <h3>฿<?= number_format($total_revenue, 2) ?></h3>
                            <p>ยอดขายรวมทั้งหมด</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orders"><i class="fa-solid fa-bag-shopping"></i></div>
                        <div class="stat-info">
                            <h3><?= number_format($total_orders) ?></h3>
                            <p>คำสั่งซื้อทั้งหมด</p>
                        </div>
                    </div>
                    <div class="stat-card" style="cursor:pointer;" onclick="switchTab('books', document.querySelectorAll('.nav-item')[1])">
                        <div class="stat-icon books"><i class="fa-solid fa-book"></i></div>
                        <div class="stat-info">
                            <h3><?= number_format($total_books) ?></h3>
                            <p>หนังสือในระบบ (คลิกดู)</p>
                        </div>
                    </div>
                    <div class="stat-card" style="cursor:pointer;" onclick="switchTab('books', document.querySelectorAll('.nav-item')[1]); filterByQuickKPI('low');">
                        <div class="stat-icon stock"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div class="stat-info">
                            <h3><?= number_format($low_stock_books) ?></h3>
                            <p>สต็อกใกล้หมด (≤ 5)</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon users"><i class="fa-solid fa-users"></i></div>
                        <div class="stat-info">
                            <h3><?= number_format($total_users) ?></h3>
                            <p>สมาชิกทั้งหมด</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders in Dashboard -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> รายการสั่งซื้อล่าสุด</h2>
                        <button class="btn btn-secondary btn-sm" onclick="switchTab('orders', document.querySelectorAll('.nav-item')[2])">ดูทั้งหมด</button>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>รหัสคำสั่งซื้อ</th>
                                    <th>ลูกค้า</th>
                                    <th>ยอดชำระ</th>
                                    <th>วันที่สั่งซื้อ</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr><td colspan="5" style="text-align:center; color: var(--text-muted); padding: 2rem;">ยังไม่มีรายการสั่งซื้อ</td></tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($orders, 0, 5) as $ord): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($ord['id']) ?></strong></td>
                                        <td><?= htmlspecialchars($ord['user_name'] ?? 'ลูกค้า') ?></td>
                                        <td style="font-weight:600; color:var(--primary-dark);">฿<?= number_format($ord['total_amount'], 2) ?></td>
                                        <td style="color:var(--text-muted); font-size:0.85rem;"><?= htmlspecialchars($ord['order_date']) ?></td>
                                        <td>
                                            <span class="badge <?= $ord['status'] === 'จัดส่งแล้ว' || $ord['status'] === 'ชำระเงินแล้ว' ? 'badge-success' : ($ord['status'] === 'ยกเลิก' ? 'badge-danger' : 'badge-warning') ?>">
                                                <?= htmlspecialchars($ord['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- TAB 2: BOOKS MANAGEMENT (RE-DESIGNED & POWERFUL) -->
            <section id="tab-books" class="tab-section">
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fa-solid fa-book-open"></i> 
                            <span>จัดการคลังหนังสือ</span>
                            <span class="title-count" id="bookHeaderCount"><?= count($books) ?> รายการ</span>
                        </h2>
                        <div class="card-actions">
                            <!-- View Switcher -->
                            <div class="view-mode-toggle">
                                <button type="button" class="view-btn active" id="viewModeTableBtn" onclick="setViewMode('table')" title="มุมมองตาราง">
                                    <i class="fa-solid fa-list"></i> ตาราง
                                </button>
                                <button type="button" class="view-btn" id="viewModeGridBtn" onclick="setViewMode('grid')" title="มุมมองการ์ด/แคตตาล็อก">
                                    <i class="fa-solid fa-grip"></i> การ์ด
                                </button>
                            </div>
                            <button class="btn btn-primary" onclick="openAddBookModal()">
                                <i class="fa-solid fa-plus"></i> เพิ่มหนังสือใหม่
                            </button>
                        </div>
                    </div>

                    <!-- Quick KPI Mini-Bar for Books -->
                    <div class="book-kpi-bar">
                        <div class="kpi-mini-card active" id="kpiCardAll" onclick="filterByQuickKPI('all')">
                            <div class="kpi-mini-icon all"><i class="fa-solid fa-layer-group"></i></div>
                            <div class="kpi-mini-info">
                                <div class="val"><?= count($books) ?></div>
                                <div class="lbl">หนังสือทั้งหมด</div>
                            </div>
                        </div>
                        <div class="kpi-mini-card" id="kpiCardStock">
                            <div class="kpi-mini-icon stock"><i class="fa-solid fa-boxes-stacked"></i></div>
                            <div class="kpi-mini-info">
                                <div class="val" id="totalStockVal"><?= number_format($total_stock_units) ?></div>
                                <div class="lbl">สต็อกคงเหลือรวม</div>
                            </div>
                        </div>
                        <div class="kpi-mini-card" id="kpiCardLow" onclick="filterByQuickKPI('low')">
                            <div class="kpi-mini-icon low"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            <div class="kpi-mini-info">
                                <div class="val"><?= $low_stock_books ?></div>
                                <div class="lbl">สต็อกใกล้หมด (≤5)</div>
                            </div>
                        </div>
                        <div class="kpi-mini-card" id="kpiCardOut" onclick="filterByQuickKPI('out')">
                            <div class="kpi-mini-icon out"><i class="fa-solid fa-ban"></i></div>
                            <div class="kpi-mini-info">
                                <div class="val"><?= $out_of_stock_books ?></div>
                                <div class="lbl">สินค้าหมด (0)</div>
                            </div>
                        </div>
                        <div class="kpi-mini-card" id="kpiCardFeat" onclick="filterByQuickKPI('feat')">
                            <div class="kpi-mini-icon feat"><i class="fa-solid fa-star"></i></div>
                            <div class="kpi-mini-info">
                                <div class="val"><?= $featured_books ?></div>
                                <div class="lbl">แนะนำ ⭐</div>
                            </div>
                        </div>
                    </div>

                    <!-- Search & Filter Controls Toolbar -->
                    <div class="toolbar-box">
                        <div class="toolbar-top-row">
                            <div class="search-box-wrapper">
                                <i class="fa-solid fa-magnifying-glass icon-search"></i>
                                <input type="text" id="bookSearchInput" placeholder="ค้นหาชื่อหนังสือ, ผู้แต่ง, หมวดหมู่..." oninput="handleSearchInput()">
                                <button type="button" class="clear-search-btn" id="clearSearchBtn" onclick="clearSearch()" title="ล้างการค้นหา">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            
                            <div class="filter-controls-group">
                                <!-- Stock Filter Dropdown -->
                                <select id="bookStockFilter" class="select-filter" onchange="applyBooksFilter()">
                                    <option value="all">📦 สต็อก: ทั้งหมด</option>
                                    <option value="in_stock">✅ มีสินค้า (> 5)</option>
                                    <option value="low_stock">⚠️ สต็อกใกล้หมด (1 - 5)</option>
                                    <option value="out_of_stock">🚫 หมดสต็อก (0)</option>
                                </select>

                                <!-- Sort Dropdown -->
                                <select id="bookSortSelect" class="select-filter" onchange="applyBooksFilter()">
                                    <option value="featured">⭐ หนังสือแนะนำก่อน</option>
                                    <option value="price_asc">💰 ราคา: ต่ำ ➔ สูง</option>
                                    <option value="price_desc">💎 ราคา: สูง ➔ ต่ำ</option>
                                    <option value="stock_asc">📉 คงเหลือน้อยสุดก่อน</option>
                                    <option value="stock_desc">📈 คงเหลือมากสุดก่อน</option>
                                    <option value="rating_desc">🌟 คะแนนสูงสุด</option>
                                    <option value="title_asc">🔤 ชื่อหนังสือ ก-ฮ (A-Z)</option>
                                </select>

                                <button type="button" class="btn btn-secondary btn-sm" onclick="resetAllFilters()" title="รีเซ็ตตัวกรอง">
                                    <i class="fa-solid fa-arrow-rotate-left"></i> รีเซ็ต
                                </button>
                            </div>
                        </div>

                        <!-- Category Filter Chips Bar with Counts -->
                        <div class="category-chips-bar">
                            <?php foreach ($categories as $index => $cat): ?>
                                <?php 
                                    $cnt = ($cat === 'ทั้งหมด') ? count($books) : ($category_counts_raw[$cat] ?? 0);
                                ?>
                                <button type="button" class="category-chip <?= $index === 0 ? 'active' : '' ?>" data-category="<?= htmlspecialchars($cat) ?>" onclick="selectCategoryChip('<?= htmlspecialchars($cat) ?>', this)">
                                    <?= htmlspecialchars($cat) ?>
                                    <span class="chip-cnt"><?= $cnt ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- TABLE VIEW CONTAINER -->
                    <div id="booksTableView" class="table-responsive">
                        <table id="booksTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">
                                        <input type="checkbox" id="selectAllBooks" class="custom-chk" onchange="toggleSelectAllBooks(this)">
                                    </th>
                                    <th>หนังสือ</th>
                                    <th>หมวดหมู่</th>
                                    <th>ราคา</th>
                                    <th>คงเหลือ</th>
                                    <th>คะแนน</th>
                                    <th style="text-align: center;">แนะนำ ⭐</th>
                                    <th style="text-align: right;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="booksTableBody">
                                <?php foreach ($books as $b): ?>
                                <tr class="book-item-row" 
                                    id="bookRow_<?= $b['id'] ?>"
                                    data-id="<?= htmlspecialchars($b['id']) ?>"
                                    data-title="<?= strtolower(htmlspecialchars($b['title'])) ?>" 
                                    data-author="<?= strtolower(htmlspecialchars($b['author'])) ?>" 
                                    data-category="<?= htmlspecialchars($b['category']) ?>"
                                    data-price="<?= floatval($b['price']) ?>"
                                    data-stock="<?= intval($b['stock']) ?>"
                                    data-rating="<?= floatval($b['rating'] ?? 4.8) ?>"
                                    data-featured="<?= intval($b['is_featured']) ?>"
                                    data-json='<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>'>
                                    
                                    <td style="text-align: center;">
                                        <input type="checkbox" class="custom-chk book-select-chk" value="<?= htmlspecialchars($b['id']) ?>" onchange="handleBookSelectChange()">
                                    </td>

                                    <td>
                                        <div class="book-title-cell">
                                            <img src="<?= htmlspecialchars($b['cover_url']) ?>" alt="cover" class="book-thumb" onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500'" onclick="openQuickViewModal('<?= $b['id'] ?>')">
                                            <div class="book-details">
                                                <span class="book-title" title="<?= htmlspecialchars($b['title']) ?>" onclick="openQuickViewModal('<?= $b['id'] ?>')">
                                                    <?= htmlspecialchars($b['title']) ?>
                                                </span>
                                                <span class="book-author">ผู้แต่ง: <?= htmlspecialchars($b['author']) ?></span>
                                                <span class="book-id-badge">ID: <?= htmlspecialchars($b['id']) ?></span>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge badge-category"><?= htmlspecialchars($b['category']) ?></span>
                                    </td>

                                    <td>
                                        <span style="font-weight:700; color:var(--primary-dark); font-family:'Outfit','Prompt',sans-serif; font-size:0.95rem;">
                                            ฿<?= number_format($b['price'], 2) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="stock-control-cell">
                                            <button type="button" class="stock-step-btn" onclick="quickAdjustStock('<?= $b['id'] ?>', 'dec')" title="ลดสต็อก 1 เล่ม">-</button>
                                            <span id="stockBadge_<?= $b['id'] ?>" class="badge <?= $b['stock'] <= 0 ? 'badge-danger' : ($b['stock'] <= 5 ? 'badge-warning' : 'badge-success') ?>">
                                                <?= $b['stock'] <= 0 ? 'หมดสต็อก' : "{$b['stock']} เล่ม" ?>
                                            </span>
                                            <button type="button" class="stock-step-btn" onclick="quickAdjustStock('<?= $b['id'] ?>', 'inc')" title="เพิ่มสต็อก 1 เล่ม">+</button>
                                        </div>
                                    </td>

                                    <td>
                                        <span style="color:#D35400; font-weight:600; font-family:'Outfit','Prompt',sans-serif;">
                                            ⭐ <?= number_format($b['rating'] ?? 4.8, 1) ?>
                                        </span>
                                    </td>

                                    <td style="text-align: center;">
                                        <button type="button" class="star-toggle-btn <?= $b['is_featured'] == 1 ? 'featured' : 'not-featured' ?>" id="starBtn_<?= $b['id'] ?>" onclick="toggleFeaturedInline('<?= $b['id'] ?>')" title="คลิกเพื่อสลับสถานะแนะนำ">
                                            <i class="fa-solid fa-star"></i>
                                        </button>
                                    </td>

                                    <td style="text-align: right;">
                                        <div style="display:inline-flex; gap:4px;">
                                            <button class="btn btn-secondary btn-icon btn-sm" onclick="openQuickViewModal('<?= $b['id'] ?>')" title="ดูตัวอย่าง">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button class="btn btn-secondary btn-icon btn-sm" onclick='openEditBookModalById("<?= $b['id'] ?>")' title="แก้ไข">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button class="btn btn-secondary btn-icon btn-sm" onclick="duplicateBook('<?= $b['id'] ?>')" title="ทำสำเนา (Duplicate)">
                                                <i class="fa-solid fa-copy"></i>
                                            </button>
                                            <button class="btn btn-danger btn-icon btn-sm" onclick="deleteBook('<?= $b['id'] ?>', '<?= addslashes(htmlspecialchars($b['title'])) ?>')" title="ลบ">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- GRID VIEW CONTAINER (Alternative Showcase Mode) -->
                    <div id="booksGridView" class="books-grid-view" style="display: none;">
                        <?php foreach ($books as $b): ?>
                        <div class="book-card book-item-grid" 
                            id="bookCard_<?= $b['id'] ?>"
                            data-id="<?= htmlspecialchars($b['id']) ?>"
                            data-title="<?= strtolower(htmlspecialchars($b['title'])) ?>" 
                            data-author="<?= strtolower(htmlspecialchars($b['author'])) ?>" 
                            data-category="<?= htmlspecialchars($b['category']) ?>"
                            data-price="<?= floatval($b['price']) ?>"
                            data-stock="<?= intval($b['stock']) ?>"
                            data-rating="<?= floatval($b['rating'] ?? 4.8) ?>"
                            data-featured="<?= intval($b['is_featured']) ?>"
                            data-json='<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>'>
                            
                            <div class="book-card-cover-wrapper" onclick="openQuickViewModal('<?= $b['id'] ?>')">
                                <img src="<?= htmlspecialchars($b['cover_url']) ?>" alt="cover" class="book-card-cover" onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500'">
                                
                                <div class="book-card-badges">
                                    <span class="badge badge-category"><?= htmlspecialchars($b['category']) ?></span>
                                    <button type="button" class="star-toggle-btn <?= $b['is_featured'] == 1 ? 'featured' : 'not-featured' ?> book-card-star" id="gridStarBtn_<?= $b['id'] ?>" onclick="event.stopPropagation(); toggleFeaturedInline('<?= $b['id'] ?>')" title="สลับแนะนำ">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="book-card-body">
                                <div class="book-card-title" title="<?= htmlspecialchars($b['title']) ?>" onclick="openQuickViewModal('<?= $b['id'] ?>')">
                                    <?= htmlspecialchars($b['title']) ?>
                                </div>
                                <div class="book-card-author"><i class="fa-solid fa-feather"></i> <?= htmlspecialchars($b['author']) ?></div>
                                
                                <div class="book-card-meta">
                                    <span class="book-card-price">฿<?= number_format($b['price'], 2) ?></span>
                                    <span style="font-size:0.8rem; color:#D35400; font-weight:600;">
                                        ⭐ <?= number_format($b['rating'] ?? 4.8, 1) ?>
                                    </span>
                                </div>

                                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px; font-size:0.78rem;">
                                    <span style="color:var(--text-muted);">คงเหลือ:</span>
                                    <span id="gridStockBadge_<?= $b['id'] ?>" class="badge <?= $b['stock'] <= 0 ? 'badge-danger' : ($b['stock'] <= 5 ? 'badge-warning' : 'badge-success') ?>" style="font-size:0.72rem; padding:2px 7px;">
                                        <?= $b['stock'] <= 0 ? 'หมดสต็อก' : "{$b['stock']} เล่ม" ?>
                                    </span>
                                </div>
                                <div class="stock-progress-bar">
                                    <div class="stock-progress-fill" style="width: <?= min(100, $b['stock'] * 4) ?>%; background: <?= $b['stock'] <= 0 ? '#E74C3C' : ($b['stock'] <= 5 ? '#E67E22' : '#27AE60') ?>;"></div>
                                </div>
                            </div>

                            <div class="book-card-footer">
                                <div class="stock-control-cell">
                                    <button type="button" class="stock-step-btn" onclick="quickAdjustStock('<?= $b['id'] ?>', 'dec')">-</button>
                                    <span style="font-size:0.75rem; font-weight:600; color:var(--text-muted); min-width:20px; text-align:center;" id="gridStockNum_<?= $b['id'] ?>"><?= $b['stock'] ?></span>
                                    <button type="button" class="stock-step-btn" onclick="quickAdjustStock('<?= $b['id'] ?>', 'inc')">+</button>
                                </div>

                                <div style="display:inline-flex; gap:4px;">
                                    <button class="btn btn-secondary btn-icon btn-sm" onclick="openQuickViewModal('<?= $b['id'] ?>')" title="ดูตัวอย่าง">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="btn btn-secondary btn-icon btn-sm" onclick='openEditBookModalById("<?= $b['id'] ?>")' title="แก้ไข">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-icon btn-sm" onclick="deleteBook('<?= $b['id'] ?>', '<?= addslashes(htmlspecialchars($b['title'])) ?>')" title="ลบ">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Empty Search / Filter State -->
                    <div id="booksEmptyState" class="empty-state" style="display: none;">
                        <i class="fa-solid fa-book-bookmark"></i>
                        <h3>ไม่พบข้อมูลหนังสือ</h3>
                        <p>ไม่มีหนังสือที่ตรงกับเงื่อนไขการค้นหาหรือตัวกรองปัจจุบัน</p>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="resetAllFilters()">
                            <i class="fa-solid fa-filter-circle-xmark"></i> ล้างตัวกรองทั้งหมด
                        </button>
                    </div>
                </div>
            </section>

            <!-- TAB 3: ORDERS MANAGEMENT -->
            <section id="tab-orders" class="tab-section">
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fa-solid fa-receipt"></i> จัดการคำสั่งซื้อ (<?= count($orders) ?> รายการ)</h2>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>รหัสคำสั่งซื้อ</th>
                                    <th>ชื่อลูกค้า</th>
                                    <th>ยอดรวม</th>
                                    <th>วันที่สั่งซื้อ</th>
                                    <th>สถานะปัจจุบัน</th>
                                    <th>เปลี่ยนสถานะ</th>
                                    <th style="text-align: right;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($ord['id']) ?></strong></td>
                                    <td><?= htmlspecialchars($ord['user_name'] ?? 'ลูกค้า') ?></td>
                                    <td style="font-weight:700; color:var(--primary-dark); font-family:'Outfit','Prompt',sans-serif;">฿<?= number_format($ord['total_amount'], 2) ?></td>
                                    <td style="color:var(--text-muted); font-size:0.85rem;"><?= htmlspecialchars($ord['order_date']) ?></td>
                                    <td>
                                        <span class="badge <?= $ord['status'] === 'จัดส่งแล้ว' || $ord['status'] === 'ชำระเงินแล้ว' ? 'badge-success' : ($ord['status'] === 'ยกเลิก' ? 'badge-danger' : 'badge-warning') ?>">
                                            <?= htmlspecialchars($ord['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <select class="select-filter" style="padding: 6px 10px; font-size: 0.84rem;" onchange="updateOrderStatus('<?= $ord['id'] ?>', this.value)">
                                            <option value="รอชำระเงิน" <?= $ord['status'] === 'รอชำระเงิน' ? 'selected' : '' ?>>รอชำระเงิน</option>
                                            <option value="ชำระเงินแล้ว" <?= $ord['status'] === 'ชำระเงินแล้ว' ? 'selected' : '' ?>>ชำระเงินแล้ว</option>
                                            <option value="กำลังจัดส่ง" <?= $ord['status'] === 'กำลังจัดส่ง' ? 'selected' : '' ?>>กำลังจัดส่ง</option>
                                            <option value="จัดส่งแล้ว" <?= $ord['status'] === 'จัดส่งแล้ว' ? 'selected' : '' ?>>จัดส่งแล้ว</option>
                                            <option value="ยกเลิก" <?= $ord['status'] === 'ยกเลิก' ? 'selected' : '' ?>>ยกเลิก</option>
                                        </select>
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="btn btn-danger btn-sm" onclick="deleteOrder('<?= $ord['id'] ?>')" title="ลบออเดอร์">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- TAB 4: USERS MANAGEMENT -->
            <section id="tab-users" class="tab-section">
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fa-solid fa-users-gear"></i> สมาชิกและผู้ดูแลระบบ (<?= count($users) ?> คน)</h2>
                        <button class="btn btn-primary btn-sm" onclick="openAddUserModal()">
                            <i class="fa-solid fa-user-plus"></i> เพิ่มผู้ใช้ใหม่
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ชื่อ - นามสกุล</th>
                                    <th>อีเมล</th>
                                    <th>สิทธิ์การใช้งาน</th>
                                    <th>วันที่สมัคร</th>
                                    <th style="text-align: right;">จัดการสิทธิ์</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span class="badge <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-customer' ?>">
                                            <i class="fa-solid <?= $u['role'] === 'admin' ? 'fa-shield-halved' : 'fa-user' ?>"></i>
                                            <?= htmlspecialchars(strtoupper($u['role'])) ?>
                                        </span>
                                    </td>
                                    <td style="color:var(--text-muted); font-size:0.85rem;"><?= htmlspecialchars($u['created_at']) ?></td>
                                    <td style="text-align: right;">
                                        <button class="btn btn-secondary btn-sm" onclick="toggleUserRole('<?= $u['id'] ?>', '<?= $u['role'] === 'admin' ? 'customer' : 'admin' ?>')">
                                            เปลี่ยนเป็น <?= $u['role'] === 'admin' ? 'Customer' : 'Admin' ?>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteUser('<?= $u['id'] ?>', '<?= addslashes(htmlspecialchars($u['name'])) ?>')" title="ลบผู้ใช้">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- TAB 5: API INFO -->
            <section id="tab-api-info" class="tab-section">
                <div class="content-card">
                    <h2 class="card-title" style="margin-bottom: 1rem;"><i class="fa-solid fa-code"></i> ข้อมูล REST API สำหรับ Flutter Application</h2>
                    <p style="color: var(--text-muted); margin-bottom: 1.4rem;">
                        Backend ให้บริการทั้ง Web Dashboard และ REST API ผ่านพอร์ตเดียวกัน (<code>http://127.0.0.1:8000</code>) โดยแอป Flutter สามารถเรียกใช้งาน API ได้ดังนี้:
                    </p>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Method</th>
                                    <th>API Endpoint</th>
                                    <th>คำอธิบาย</th>
                                    <th>พารามิเตอร์</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-success">GET</span></td>
                                    <td><code>/books.php</code></td>
                                    <td>ดึงรายการหนังสือทั้งหมด (รองรับค้นหา/หมวดหมู่)</td>
                                    <td><code>search</code>, <code>category</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info">POST</span></td>
                                    <td><code>/books.php</code></td>
                                    <td>เพิ่มหนังสือใหม่จากแอป</td>
                                    <td>JSON Body</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning">PUT</span></td>
                                    <td><code>/books.php</code></td>
                                    <td>แก้ไขข้อมูลหนังสือ</td>
                                    <td>JSON Body</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-danger">DELETE</span></td>
                                    <td><code>/books.php?id={id}</code></td>
                                    <td>ลบหนังสือ</td>
                                    <td><code>id</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info">POST</span></td>
                                    <td><code>/auth.php?action=login</code></td>
                                    <td>เข้าสู่ระบบ (Login)</td>
                                    <td><code>email</code>, <code>password</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info">POST</span></td>
                                    <td><code>/auth.php?action=register</code></td>
                                    <td>สมัครสมาชิก (Register)</td>
                                    <td><code>name</code>, <code>email</code>, <code>password</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">GET</span></td>
                                    <td><code>/orders.php</code></td>
                                    <td>ดึงประวัติคำสั่งซื้อ</td>
                                    <td><code>user_id</code> (optional)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info">POST</span></td>
                                    <td><code>/orders.php</code></td>
                                    <td>สร้างคำสั่งซื้อใหม่ (Checkout)</td>
                                    <td>JSON Body (user_id, items, total_amount)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- FLOATING BULK ACTIONS BAR -->
    <div id="bulkActionsBar" class="bulk-actions-bar">
        <span class="bulk-count-badge" id="bulkSelectedCount">0 เล่ม</span>
        <span style="font-size:0.88rem;">รายการที่เลือก:</span>
        <button type="button" class="btn btn-secondary btn-sm" onclick="bulkChangeCategoryModal()" style="background:#4A3E39; color:white; border-color:#5E4F49;">
            <i class="fa-solid fa-tags"></i> เปลี่ยนหมวดหมู่
        </button>
        <button type="button" class="btn btn-danger btn-sm" onclick="bulkDeleteSelectedBooks()">
            <i class="fa-solid fa-trash"></i> ลบที่เลือก
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="clearBulkSelection()" style="background:transparent; color:#bbb; border:none;">
            <i class="fa-solid fa-xmark"></i> ยกเลิก
        </button>
    </div>

    <!-- MODAL: ADD / EDIT BOOK (2-COLUMN MODERN FORM) -->
    <div id="bookModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="bookModalTitle"><i class="fa-solid fa-book"></i> เพิ่มหนังสือใหม่</h3>
                <button class="modal-close" onclick="closeBookModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="bookForm" onsubmit="handleBookSubmit(event)">
                <div class="modal-body">
                    <input type="hidden" id="bookId" name="id">
                    
                    <div class="modal-form-grid">
                        <!-- Left Column: Cover Image Preview & Presets -->
                        <div class="cover-picker-column">
                            <div class="cover-preview-card">
                                <img id="coverPreview" src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500" alt="Preview" onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500'">
                            </div>

                            <div class="preset-covers-bar">
                                <p><i class="fa-solid fa-wand-magic-sparkles"></i> เลือกรูปตัวอย่างสวยๆ:</p>
                                <div class="preset-thumbs">
                                    <button type="button" class="preset-thumb-btn" onclick="applyPresetCover('https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500')" title="พัฒนาตนเอง">
                                        <img src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=100" alt="preset">
                                    </button>
                                    <button type="button" class="preset-thumb-btn" onclick="applyPresetCover('https://images.unsplash.com/photo-1592496431122-2349e0fbc666?w=500')" title="ธุรกิจ">
                                        <img src="https://images.unsplash.com/photo-1592496431122-2349e0fbc666?w=100" alt="preset">
                                    </button>
                                    <button type="button" class="preset-thumb-btn" onclick="applyPresetCover('https://images.unsplash.com/photo-1512820790803-83ca734da794?w=500')" title="นิยาย">
                                        <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?w=100" alt="preset">
                                    </button>
                                    <button type="button" class="preset-thumb-btn" onclick="applyPresetCover('https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=500')" title="วรรณกรรม">
                                        <img src="https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=100" alt="preset">
                                    </button>
                                    <button type="button" class="preset-thumb-btn" onclick="applyPresetCover('https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=500')" title="มังงะ">
                                        <img src="https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=100" alt="preset">
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Fields -->
                        <div class="form-fields-column">
                            <div class="form-group">
                                <label for="bookTitle">ชื่อหนังสือ <span style="color:var(--danger)">*</span></label>
                                <input type="text" id="bookTitle" name="title" class="form-control" placeholder="เช่น Atomic Habits เพราะชีวิตดีได้กว่าที่เป็น" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="bookAuthor">ผู้แต่ง / สำนักพิมพ์ <span style="color:var(--danger)">*</span></label>
                                    <input type="text" id="bookAuthor" name="author" class="form-control" placeholder="เช่น James Clear" required>
                                </div>
                                <div class="form-group">
                                    <label for="bookCategory">หมวดหมู่</label>
                                    <select id="bookCategory" name="category" class="form-control">
                                        <?php foreach ($categories as $cat): ?>
                                            <?php if ($cat !== 'ทั้งหมด'): ?>
                                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="bookPrice">ราคา (บาท) <span style="color:var(--danger)">*</span></label>
                                    <input type="number" step="0.01" min="0" id="bookPrice" name="price" class="form-control" placeholder="295.00" required>
                                </div>
                                <div class="form-group">
                                    <label for="bookStock">จำนวนคงเหลือ (สต็อก) <span style="color:var(--danger)">*</span></label>
                                    <input type="number" min="0" id="bookStock" name="stock" class="form-control" placeholder="20" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="bookRating">คะแนนรีวิว (1.0 - 5.0)</label>
                                    <input type="number" step="0.1" min="1" max="5" id="bookRating" name="rating" class="form-control" value="4.8">
                                </div>
                                <div class="form-group">
                                    <label for="bookPages">จำนวนหน้า</label>
                                    <input type="number" min="1" id="bookPages" name="pages" class="form-control" value="250">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="bookCover">ลิงก์รูปภาพหน้าปก (URL)</label>
                                <input type="url" id="bookCover" name="cover_url" class="form-control" placeholder="https://..." oninput="previewImage(this.value)">
                            </div>

                            <div class="form-group">
                                <label for="bookDesc">คำอธิบายย่อ / เรื่องย่อ</label>
                                <textarea id="bookDesc" name="description" class="form-control" rows="3" placeholder="รายละเอียดเนื้อหาโดยย่อของหนังสือ..."></textarea>
                            </div>

                            <!-- Featured Toggle -->
                            <label class="toggle-switch-wrapper" for="bookFeatured">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="color:var(--accent-gold); font-size:1.1rem;"><i class="fa-solid fa-star"></i></span>
                                    <div>
                                        <div style="font-weight:600; font-size:0.88rem;">ตั้งเป็นหนังสือแนะนำ (Featured)</div>
                                        <div style="font-size:0.75rem; color:var(--text-muted);">แสดงที่แถบไฮไลท์หน้าแรกของแอปพลิเคชัน</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="bookFeatured" name="is_featured" value="1" class="switch-input">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeBookModal()">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: QUICK VIEW BOOK DETAILS -->
    <div id="quickViewModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 680px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-circle-info"></i> รายละเอียดหนังสือ</h3>
                <button class="modal-close" onclick="closeQuickViewModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="preview-modal-body">
                <div>
                    <img id="qvCover" src="" alt="cover" class="preview-cover-hd" onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500'">
                </div>
                <div class="preview-details-wrap">
                    <div class="preview-meta-row">
                        <span id="qvCategory" class="badge badge-category">นิยาย</span>
                        <span id="qvFeaturedBadge" class="badge" style="background:#FFF8E7; color:#E67E22; border:1px solid #FCE5CD;">⭐ แนะนำ</span>
                        <span id="qvStockBadge" class="badge badge-success">คงเหลือ 20 เล่ม</span>
                    </div>

                    <h2 id="qvTitle">ชื่อหนังสือ</h2>
                    <div style="font-size:0.88rem; color:var(--text-muted);"><i class="fa-solid fa-feather"></i> ผู้แต่ง: <span id="qvAuthor" style="font-weight:600; color:var(--text-main);"></span></div>
                    
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:4px;">
                        <div class="preview-price-box" id="qvPrice">฿295.00</div>
                        <div style="font-size:0.88rem; color:#D35400; font-weight:600;" id="qvRating">⭐ 4.8 / 5.0</div>
                    </div>

                    <div style="font-size:0.8rem; color:var(--text-muted);">
                        <i class="fa-solid fa-file-lines"></i> ความยาว: <span id="qvPages">250</span> หน้า | <i class="fa-solid fa-barcode"></i> รหัสสินค้า: <code id="qvId">b_001</code>
                    </div>

                    <div class="preview-desc-box" id="qvDesc">
                        ไม่มีคำอธิบาย
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeQuickViewModal()">ปิด</button>
                <button type="button" class="btn btn-primary" id="qvEditBtn"><i class="fa-solid fa-pen-to-square"></i> แก้ไขข้อมูล</button>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD USER -->
    <div id="userModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-user-plus"></i> เพิ่มผู้ใช้งานใหม่</h3>
                <button class="modal-close" onclick="closeUserModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="userForm" onsubmit="handleUserSubmit(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="userName">ชื่อ - นามสกุล <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="userName" name="name" class="form-control" placeholder="เช่น สมชาย ใจดี" required>
                    </div>
                    <div class="form-group" style="margin-top: 10px;">
                        <label for="userEmail">อีเมล <span style="color:var(--danger)">*</span></label>
                        <input type="email" id="userEmail" name="email" class="form-control" placeholder="name@example.com" required>
                    </div>
                    <div class="form-group" style="margin-top: 10px;">
                        <label for="userPassword">รหัสผ่าน <span style="color:var(--danger)">*</span></label>
                        <input type="password" id="userPassword" name="password" class="form-control" placeholder="อย่างน้อย 4 ตัวอักษร" required minlength="4">
                    </div>
                    <div class="form-group" style="margin-top: 10px;">
                        <label for="userRole">สิทธิ์การใช้งาน</label>
                        <select id="userRole" name="role" class="form-control">
                            <option value="customer">Customer (ลูกค้าทั่วไป)</option>
                            <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeUserModal()">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-check"></i> สร้างผู้ใช้</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // State
        let currentCategoryFilter = 'ทั้งหมด';
        let currentKPIFilter = 'all'; // 'all', 'low', 'out', 'feat'
        let currentViewMode = 'table'; // 'table' or 'grid'

        // Tab Switching
        function switchTab(tabId, element) {
            document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            
            const target = document.getElementById('tab-' + tabId);
            if (target) target.classList.add('active');
            if (element) element.classList.add('active');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // View Mode (Table vs Grid)
        function setViewMode(mode) {
            currentViewMode = mode;
            const tableDiv = document.getElementById('booksTableView');
            const gridDiv = document.getElementById('booksGridView');
            const tableBtn = document.getElementById('viewModeTableBtn');
            const gridBtn = document.getElementById('viewModeGridBtn');

            if (mode === 'grid') {
                tableDiv.style.display = 'none';
                gridDiv.style.display = 'grid';
                tableBtn.classList.remove('active');
                gridBtn.classList.add('active');
            } else {
                tableDiv.style.display = 'block';
                gridDiv.style.display = 'none';
                tableBtn.classList.add('active');
                gridBtn.classList.remove('active');
            }
        }

        // Live Book Cover Preview
        function previewImage(url) {
            const preview = document.getElementById('coverPreview');
            if (url && url.trim() !== '') {
                preview.src = url;
            } else {
                preview.src = 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500';
            }
        }

        function applyPresetCover(url) {
            document.getElementById('bookCover').value = url;
            previewImage(url);
        }

        // Search Input Handling
        function handleSearchInput() {
            const val = document.getElementById('bookSearchInput').value;
            const clearBtn = document.getElementById('clearSearchBtn');
            clearBtn.style.display = val.length > 0 ? 'flex' : 'none';
            applyBooksFilter();
        }

        function clearSearch() {
            document.getElementById('bookSearchInput').value = '';
            document.getElementById('clearSearchBtn').style.display = 'none';
            applyBooksFilter();
        }

        // Quick KPI Filter
        function filterByQuickKPI(type) {
            currentKPIFilter = type;
            document.querySelectorAll('.kpi-mini-card').forEach(c => c.classList.remove('active'));
            
            if (type === 'all') {
                document.getElementById('kpiCardAll').classList.add('active');
                document.getElementById('bookStockFilter').value = 'all';
            } else if (type === 'low') {
                document.getElementById('kpiCardLow').classList.add('active');
                document.getElementById('bookStockFilter').value = 'low_stock';
            } else if (type === 'out') {
                document.getElementById('kpiCardOut').classList.add('active');
                document.getElementById('bookStockFilter').value = 'out_of_stock';
            } else if (type === 'feat') {
                document.getElementById('kpiCardFeat').classList.add('active');
            }
            applyBooksFilter();
        }

        function selectCategoryChip(cat, btn) {
            document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            currentCategoryFilter = cat;
            applyBooksFilter();
        }

        function resetAllFilters() {
            document.getElementById('bookSearchInput').value = '';
            document.getElementById('clearSearchBtn').style.display = 'none';
            document.getElementById('bookStockFilter').value = 'all';
            document.getElementById('bookSortSelect').value = 'featured';
            currentCategoryFilter = 'ทั้งหมด';
            currentKPIFilter = 'all';
            
            document.querySelectorAll('.category-chip').forEach((c, idx) => {
                if (idx === 0) c.classList.add('active');
                else c.classList.remove('active');
            });
            document.querySelectorAll('.kpi-mini-card').forEach(c => c.classList.remove('active'));
            document.getElementById('kpiCardAll').classList.add('active');

            applyBooksFilter();
        }

        // Master Filter & Sort function
        function applyBooksFilter() {
            const search = document.getElementById('bookSearchInput').value.trim().toLowerCase();
            const stockFilter = document.getElementById('bookStockFilter').value;
            const sortMode = document.getElementById('bookSortSelect').value;

            const tableRows = Array.from(document.querySelectorAll('.book-item-row'));
            const gridCards = Array.from(document.querySelectorAll('.book-item-grid'));

            let visibleCount = 0;

            // Filter logic
            function testItem(el) {
                const title = el.getAttribute('data-title') || '';
                const author = el.getAttribute('data-author') || '';
                const cat = el.getAttribute('data-category') || '';
                const stock = parseInt(el.getAttribute('data-stock') || '0', 10);
                const isFeatured = parseInt(el.getAttribute('data-featured') || '0', 10);

                // Search match
                const matchSearch = search === '' || title.includes(search) || author.includes(search) || cat.toLowerCase().includes(search);
                
                // Category match
                const matchCat = (currentCategoryFilter === 'ทั้งหมด' || cat === currentCategoryFilter);

                // Stock match
                let matchStock = true;
                if (stockFilter === 'in_stock') matchStock = (stock > 5);
                else if (stockFilter === 'low_stock') matchStock = (stock > 0 && stock <= 5);
                else if (stockFilter === 'out_of_stock') matchStock = (stock === 0);

                // KPI match
                let matchKPI = true;
                if (currentKPIFilter === 'feat') matchKPI = (isFeatured === 1);
                else if (currentKPIFilter === 'low') matchKPI = (stock > 0 && stock <= 5);
                else if (currentKPIFilter === 'out') matchKPI = (stock === 0);

                return matchSearch && matchCat && matchStock && matchKPI;
            }

            // Filter Table Rows
            tableRows.forEach(row => {
                const show = testItem(row);
                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            // Filter Grid Cards
            gridCards.forEach(card => {
                const show = testItem(card);
                card.style.display = show ? 'flex' : 'none';
            });

            // Sorting
            const tableBody = document.getElementById('booksTableBody');
            const gridView = document.getElementById('booksGridView');

            function sortCompare(a, b) {
                const priceA = parseFloat(a.getAttribute('data-price') || 0);
                const priceB = parseFloat(b.getAttribute('data-price') || 0);
                const stockA = parseInt(a.getAttribute('data-stock') || 0);
                const stockB = parseInt(b.getAttribute('data-stock') || 0);
                const ratingA = parseFloat(a.getAttribute('data-rating') || 0);
                const ratingB = parseFloat(b.getAttribute('data-rating') || 0);
                const titleA = a.getAttribute('data-title') || '';
                const titleB = b.getAttribute('data-title') || '';
                const featA = parseInt(a.getAttribute('data-featured') || 0);
                const featB = parseInt(b.getAttribute('data-featured') || 0);

                switch (sortMode) {
                    case 'featured':
                        return featB - featA;
                    case 'price_asc':
                        return priceA - priceB;
                    case 'price_desc':
                        return priceB - priceA;
                    case 'stock_asc':
                        return stockA - stockB;
                    case 'stock_desc':
                        return stockB - stockA;
                    case 'rating_desc':
                        return ratingB - ratingA;
                    case 'title_asc':
                        return titleA.localeCompare(titleB, 'th');
                    default:
                        return 0;
                }
            }

            tableRows.sort(sortCompare).forEach(row => tableBody.appendChild(row));
            gridCards.sort(sortCompare).forEach(card => gridView.appendChild(card));

            // Empty state display
            const emptyState = document.getElementById('booksEmptyState');
            if (visibleCount === 0) {
                emptyState.style.display = 'block';
                document.getElementById('booksTableView').style.display = 'none';
                document.getElementById('booksGridView').style.display = 'none';
            } else {
                emptyState.style.display = 'none';
                if (currentViewMode === 'grid') {
                    document.getElementById('booksGridView').style.display = 'grid';
                    document.getElementById('booksTableView').style.display = 'none';
                } else {
                    document.getElementById('booksTableView').style.display = 'block';
                    document.getElementById('booksGridView').style.display = 'none';
                }
            }

            // Update Count Display
            document.getElementById('bookHeaderCount').innerText = `${visibleCount} รายการ`;
        }

        // Quick 1-Click Toggle Featured
        async function toggleFeaturedInline(id) {
            try {
                const fd = new FormData();
                fd.append('action', 'toggle_featured');
                fd.append('id', id);

                const res = await fetch('admin_actions.php', { method: 'POST', body: fd });
                const data = await res.json();

                if (data.status === 'success') {
                    const isFeat = data.is_featured === 1;
                    
                    // Update table star
                    const starBtn = document.getElementById(`starBtn_${id}`);
                    if (starBtn) {
                        starBtn.className = `star-toggle-btn ${isFeat ? 'featured' : 'not-featured'}`;
                    }

                    // Update grid star
                    const gridStarBtn = document.getElementById(`gridStarBtn_${id}`);
                    if (gridStarBtn) {
                        gridStarBtn.className = `star-toggle-btn ${isFeat ? 'featured' : 'not-featured'} book-card-star`;
                    }

                    // Update row & card data attribute
                    const row = document.getElementById(`bookRow_${id}`);
                    if (row) row.setAttribute('data-featured', isFeat ? '1' : '0');
                    const card = document.getElementById(`bookCard_${id}`);
                    if (card) card.setAttribute('data-featured', isFeat ? '1' : '0');

                    Swal.fire({
                        icon: isFeat ? 'success' : 'info',
                        title: data.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1600
                    });
                } else {
                    Swal.fire('ข้อผิดพลาด', data.message, 'error');
                }
            } catch (err) {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            }
        }

        // Quick Stock Increment / Decrement
        async function quickAdjustStock(id, type) {
            try {
                const fd = new FormData();
                fd.append('action', 'update_stock');
                fd.append('id', id);
                fd.append('type', type);
                fd.append('delta', '1');

                const res = await fetch('admin_actions.php', { method: 'POST', body: fd });
                const data = await res.json();

                if (data.status === 'success') {
                    const newStock = data.stock;
                    
                    // Update Table badge
                    const badge = document.getElementById(`stockBadge_${id}`);
                    if (badge) {
                        badge.className = `badge ${newStock <= 0 ? 'badge-danger' : (newStock <= 5 ? 'badge-warning' : 'badge-success')}`;
                        badge.innerText = newStock <= 0 ? 'หมดสต็อก' : `${newStock} เล่ม`;
                    }

                    // Update Grid badge and number
                    const gridBadge = document.getElementById(`gridStockBadge_${id}`);
                    if (gridBadge) {
                        gridBadge.className = `badge ${newStock <= 0 ? 'badge-danger' : (newStock <= 5 ? 'badge-warning' : 'badge-success')}`;
                        gridBadge.innerText = newStock <= 0 ? 'หมดสต็อก' : `${newStock} เล่ม`;
                    }
                    const gridNum = document.getElementById(`gridStockNum_${id}`);
                    if (gridNum) gridNum.innerText = newStock;

                    // Update row & card data-stock
                    const row = document.getElementById(`bookRow_${id}`);
                    if (row) row.setAttribute('data-stock', newStock);
                    const card = document.getElementById(`bookCard_${id}`);
                    if (card) card.setAttribute('data-stock', newStock);

                    Swal.fire({
                        icon: 'success',
                        title: data.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1400
                    });
                } else {
                    Swal.fire('ข้อผิดพลาด', data.message, 'error');
                }
            } catch (err) {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถปรับสต็อกได้', 'error');
            }
        }

        // Quick Duplicate Book
        async function duplicateBook(id) {
            Swal.fire({
                title: 'คัดลอกหนังสือนี้?',
                text: 'ระบบจะสร้างรายการหนังสือสำเนาขึ้นมาใหม่ทันที',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6F4E37',
                cancelButtonColor: '#888',
                confirmButtonText: 'ใช่, ทำสำเนา',
                cancelButtonText: 'ยกเลิก'
            }).then(async (res) => {
                if (res.isConfirmed) {
                    const fd = new FormData();
                    fd.append('action', 'duplicate_book');
                    fd.append('id', id);

                    const response = await fetch('admin_actions.php', { method: 'POST', body: fd });
                    const data = await response.json();
                    if (data.status === 'success') {
                        Swal.fire('สำเร็จ', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('ข้อผิดพลาด', data.message, 'error');
                    }
                }
            });
        }

        // Quick View Modal
        function openQuickViewModal(id) {
            const row = document.getElementById(`bookRow_${id}`);
            if (!row) return;
            const book = JSON.parse(row.getAttribute('data-json'));

            document.getElementById('qvCover').src = book.cover_url || 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500';
            document.getElementById('qvTitle').innerText = book.title;
            document.getElementById('qvAuthor').innerText = book.author;
            document.getElementById('qvCategory').innerText = book.category;
            document.getElementById('qvPrice').innerText = `฿${parseFloat(book.price).toFixed(2)}`;
            document.getElementById('qvRating').innerText = `⭐ ${parseFloat(book.rating || 4.8).toFixed(1)} / 5.0`;
            document.getElementById('qvPages').innerText = book.pages || 200;
            document.getElementById('qvId').innerText = book.id;
            document.getElementById('qvDesc').innerText = book.description || 'ไม่มีคำอธิบายรายละเอียด';

            const featBadge = document.getElementById('qvFeaturedBadge');
            featBadge.style.display = (book.is_featured == 1) ? 'inline-flex' : 'none';

            const stockBadge = document.getElementById('qvStockBadge');
            stockBadge.className = `badge ${book.stock <= 0 ? 'badge-danger' : (book.stock <= 5 ? 'badge-warning' : 'badge-success')}`;
            stockBadge.innerText = book.stock <= 0 ? 'หมดสต็อก' : `คงเหลือ ${book.stock} เล่ม`;

            document.getElementById('qvEditBtn').onclick = () => {
                closeQuickViewModal();
                openEditBookModal(book);
            };

            document.getElementById('quickViewModal').classList.add('active');
        }

        function closeQuickViewModal() {
            document.getElementById('quickViewModal').classList.remove('active');
        }

        // Bulk Selection Logic
        function toggleSelectAllBooks(masterChk) {
            const chks = document.querySelectorAll('.book-select-chk');
            chks.forEach(c => {
                // only check if row is visible
                const row = c.closest('.book-item-row');
                if (row && row.style.display !== 'none') {
                    c.checked = masterChk.checked;
                }
            });
            handleBookSelectChange();
        }

        function handleBookSelectChange() {
            const selected = document.querySelectorAll('.book-select-chk:checked');
            const bulkBar = document.getElementById('bulkActionsBar');
            const countBadge = document.getElementById('bulkSelectedCount');

            if (selected.length > 0) {
                countBadge.innerText = `${selected.length} เล่ม`;
                bulkBar.classList.add('active');
            } else {
                bulkBar.classList.remove('active');
                const master = document.getElementById('selectAllBooks');
                if (master) master.checked = false;
            }
        }

        function clearBulkSelection() {
            document.querySelectorAll('.book-select-chk').forEach(c => c.checked = false);
            const master = document.getElementById('selectAllBooks');
            if (master) master.checked = false;
            handleBookSelectChange();
        }

        function getSelectedBookIds() {
            return Array.from(document.querySelectorAll('.book-select-chk:checked')).map(c => c.value);
        }

        // Bulk Delete
        function bulkDeleteSelectedBooks() {
            const ids = getSelectedBookIds();
            if (ids.length === 0) return;

            Swal.fire({
                title: `ยืนยันการลบ ${ids.length} เล่ม?`,
                text: 'การลบนี้จะไม่สามารถเรียกคืนได้',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E74C3C',
                cancelButtonColor: '#6F4E37',
                confirmButtonText: `ใช่, ลบ ${ids.length} เล่ม`,
                cancelButtonText: 'ยกเลิก'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('action', 'bulk_delete_books');
                    fd.append('ids', JSON.stringify(ids));

                    const res = await fetch('admin_actions.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Swal.fire('ลบสำเร็จ', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('ข้อผิดพลาด', data.message, 'error');
                    }
                }
            });
        }

        // Bulk Change Category
        function bulkChangeCategoryModal() {
            const ids = getSelectedBookIds();
            if (ids.length === 0) return;

            const categoryOptions = {
                'นิยาย': 'นิยาย',
                'พัฒนาตนเอง': 'พัฒนาตนเอง',
                'ธุรกิจ/บริหาร': 'ธุรกิจ/บริหาร',
                'วรรณกรรม': 'วรรณกรรม',
                'การ์ตูน/มังงะ': 'การ์ตูน/มังงะ'
            };

            Swal.fire({
                title: `เปลี่ยนหมวดหมู่ (${ids.length} เล่ม)`,
                input: 'select',
                inputOptions: categoryOptions,
                inputPlaceholder: 'เลือกหมวดหมู่ใหม่',
                showCancelButton: true,
                confirmButtonColor: '#6F4E37',
                confirmButtonText: 'บันทึกการเปลี่ยน',
                cancelButtonText: 'ยกเลิก',
                inputValidator: (value) => {
                    if (!value) return 'กรุณาเลือกหมวดหมู่';
                }
            }).then(async (res) => {
                if (res.isConfirmed && res.value) {
                    const fd = new FormData();
                    fd.append('action', 'bulk_update_category');
                    fd.append('ids', JSON.stringify(ids));
                    fd.append('category', res.value);

                    const response = await fetch('admin_actions.php', { method: 'POST', body: fd });
                    const data = await response.json();
                    if (data.status === 'success') {
                        Swal.fire('สำเร็จ', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('ข้อผิดพลาด', data.message, 'error');
                    }
                }
            });
        }

        // Modals: Add / Edit Book
        function openAddBookModal() {
            document.getElementById('bookForm').reset();
            document.getElementById('bookId').value = '';
            document.getElementById('bookModalTitle').innerHTML = '<i class="fa-solid fa-plus"></i> เพิ่มหนังสือใหม่';
            previewImage('');
            document.getElementById('bookFeatured').checked = false;
            document.getElementById('bookModal').classList.add('active');
        }

        function openEditBookModalById(id) {
            const row = document.getElementById(`bookRow_${id}`);
            if (row) {
                const book = JSON.parse(row.getAttribute('data-json'));
                openEditBookModal(book);
            }
        }

        function openEditBookModal(book) {
            document.getElementById('bookId').value = book.id;
            document.getElementById('bookTitle').value = book.title;
            document.getElementById('bookAuthor').value = book.author;
            document.getElementById('bookCategory').value = book.category;
            document.getElementById('bookPrice').value = book.price;
            document.getElementById('bookStock').value = book.stock;
            document.getElementById('bookRating').value = book.rating;
            document.getElementById('bookPages').value = book.pages;
            document.getElementById('bookCover').value = book.cover_url;
            document.getElementById('bookDesc').value = book.description;
            document.getElementById('bookFeatured').checked = (book.is_featured == 1);
            previewImage(book.cover_url);
            document.getElementById('bookModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square"></i> แก้ไขข้อมูลหนังสือ';
            document.getElementById('bookModal').classList.add('active');
        }

        function closeBookModal() {
            document.getElementById('bookModal').classList.remove('active');
        }

        function openAddUserModal() {
            document.getElementById('userForm').reset();
            document.getElementById('userModal').classList.add('active');
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.remove('active');
        }

        // Submit Book Form (Add / Edit)
        async function handleBookSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('bookForm');
            const formData = new FormData(form);
            const isEdit = document.getElementById('bookId').value !== '';
            formData.append('action', isEdit ? 'edit_book' : 'add_book');

            try {
                const res = await fetch('admin_actions.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('ข้อผิดพลาด', data.message, 'error');
                }
            } catch (err) {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            }
        }

        // Delete Book
        function deleteBook(id, title) {
            Swal.fire({
                title: 'ยืนยันการลบหนังสือ?',
                text: `คุณต้องการลบหนังสือ "${title}" หรือไม่?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E74C3C',
                cancelButtonColor: '#6F4E37',
                confirmButtonText: 'ใช่, ลบเลย',
                cancelButtonText: 'ยกเลิก'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('action', 'delete_book');
                    fd.append('id', id);
                    const res = await fetch('admin_actions.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Swal.fire('ลบสำเร็จ', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('ข้อผิดพลาด', data.message, 'error');
                    }
                }
            });
        }

        // Update Order Status
        async function updateOrderStatus(id, status) {
            const fd = new FormData();
            fd.append('action', 'update_order_status');
            fd.append('id', id);
            fd.append('status', status);

            const res = await fetch('admin_actions.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'อัปเดตสถานะสำเร็จ',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                Swal.fire('ข้อผิดพลาด', data.message, 'error');
            }
        }

        // Delete Order
        function deleteOrder(id) {
            Swal.fire({
                title: 'ยืนยันการลบออเดอร์?',
                text: `คุณต้องการลบคำสั่งซื้อ #${id} หรือไม่?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E74C3C',
                confirmButtonText: 'ใช่, ลบเลย',
                cancelButtonText: 'ยกเลิก'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('action', 'delete_order');
                    fd.append('id', id);
                    const res = await fetch('admin_actions.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Swal.fire('ลบสำเร็จ', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('ข้อผิดพลาด', data.message, 'error');
                    }
                }
            });
        }

        // Handle Add User
        async function handleUserSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('userForm');
            const formData = new FormData(form);
            formData.append('action', 'add_user');

            const res = await fetch('admin_actions.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'success') {
                Swal.fire('สำเร็จ', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('ข้อผิดพลาด', data.message, 'error');
            }
        }

        // Toggle User Role
        async function toggleUserRole(id, newRole) {
            const fd = new FormData();
            fd.append('action', 'update_user_role');
            fd.append('id', id);
            fd.append('role', newRole);

            const res = await fetch('admin_actions.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'success') {
                Swal.fire('สำเร็จ', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('ข้อผิดพลาด', data.message, 'error');
            }
        }

        // Delete User
        function deleteUser(id, name) {
            Swal.fire({
                title: 'ยืนยันการลบผู้ใช้?',
                text: `คุณต้องการลบผู้ใช้ "${name}" หรือไม่?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E74C3C',
                confirmButtonText: 'ใช่, ลบเลย',
                cancelButtonText: 'ยกเลิก'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('action', 'delete_user');
                    fd.append('id', id);
                    const res = await fetch('admin_actions.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Swal.fire('ลบสำเร็จ', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('ข้อผิดพลาด', data.message, 'error');
                    }
                }
            });
        }

        // Sync and Restore Full 15 Books Catalog
        function syncFullCatalog() {
            Swal.fire({
                title: 'ซิงค์หนังสือครบทั้ง 15 เล่ม?',
                text: 'ระบบจะทำการนำเข้าหนังสือทั้งหมด 15 เล่ม พร้อมรูปภาพปกและรายละเอียดสมบูรณ์เข้าสู่ฐานข้อมูล',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6F4E37',
                cancelButtonColor: '#7A726D',
                confirmButtonText: '<i class="fa-solid fa-arrows-rotate"></i> ซิงค์ข้อมูลเดี๋ยวนี้',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังซิงค์ข้อมูล...',
                        text: 'กรุณารอสักครู่',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    fetch('admin_actions.php?action=restore_full_catalog')
                        .then(r => r.json())
                        .then(res => {
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'สำเร็จ!',
                                    text: res.message,
                                    confirmButtonColor: '#6F4E37'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('เกิดข้อผิดพลาด', res.message || 'ไม่สามารถซิงค์ได้', 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('ข้อผิดพลาด', err.message, 'error');
                        });
                }
            });
        }
    </script>
</body>
</html>