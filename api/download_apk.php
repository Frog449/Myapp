<?php
/**
 * CaffeBook - Android APK Automated Downloader & Sync Hub
 * รองรับการดาวน์โหลดไฟล์ APK อัตโนมัติ, ตรวจสอบสถานะไฟล์ (JSON), ซิงค์ และอัปโหลดไฟล์ APK
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Candidates path for APK file
$possiblePaths = [
    __DIR__ . '/caffebook.apk',
    __DIR__ . '/../build/app/outputs/flutter-apk/app-release.apk',
    __DIR__ . '/../build/app/outputs/flutter-apk/app-debug.apk',
    __DIR__ . '/../build/app/outputs/apk/release/app-release.apk',
    __DIR__ . '/../build/app/outputs/apk/debug/app-debug.apk',
    __DIR__ . '/../caffebook.apk',
    __DIR__ . '/../app-release.apk',
];

$apkPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path) && filesize($path) > 0) {
        $apkPath = $path;
        // Auto-copy to api/caffebook.apk if it was found in build folder
        $localApk = __DIR__ . '/caffebook.apk';
        if ($apkPath !== $localApk && (!file_exists($localApk) || filemtime($apkPath) > filemtime($localApk))) {
            @copy($apkPath, $localApk);
            if (file_exists($localApk)) {
                $apkPath = $localApk;
            }
        }
        break;
    }
}

// Helper: Format bytes to human readable string
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Helper: Time ago string in Thai
function timeAgoThai($timestamp) {
    $diff = time() - $timestamp;
    if ($diff < 60) return 'เมื่อสักครู่';
    if ($diff < 3600) return floor($diff / 60) . ' นาทีที่แล้ว';
    if ($diff < 86400) return floor($diff / 3600) . ' ชั่วโมงที่แล้ว';
    return date('d/m/Y H:i', $timestamp);
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Action: Check APK Status (JSON API)
if ($action === 'check' || isset($_GET['check']) || isset($_GET['json'])) {
    header('Content-Type: application/json; charset=UTF-8');
    if ($apkPath !== null && file_exists($apkPath)) {
        $size = filesize($apkPath);
        $mtime = filemtime($apkPath);
        echo json_encode([
            'status' => 'success',
            'exists' => true,
            'filename' => 'CaffeBook.apk',
            'size_bytes' => $size,
            'size_formatted' => formatBytes($size),
            'updated_at' => date('Y-m-d H:i:s', $mtime),
            'updated_thai' => timeAgoThai($mtime),
            'download_url' => 'download_apk.php?auto=1&t=' . $mtime
        ]);
    } else {
        echo json_encode([
            'status' => 'not_found',
            'exists' => false,
            'message' => 'ยังไม่พบไฟล์ APK บนเซิร์ฟเวอร์ กรุณารัน build_apk.bat หรืออัปโหลดไฟล์'
        ]);
    }
    exit;
}

// Action: Sync APK from build directory (JSON API)
if ($action === 'sync') {
    header('Content-Type: application/json; charset=UTF-8');
    $buildPath = __DIR__ . '/../build/app/outputs/flutter-apk/app-release.apk';
    if (!file_exists($buildPath)) {
        $buildPath = __DIR__ . '/../build/app/outputs/flutter-apk/app-debug.apk';
    }
    if (file_exists($buildPath) && filesize($buildPath) > 0) {
        $dest = __DIR__ . '/caffebook.apk';
        if (@copy($buildPath, $dest)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'ซิงค์ไฟล์ APK สำเร็จ (' . formatBytes(filesize($dest)) . ')',
                'size_formatted' => formatBytes(filesize($dest))
            ]);
            exit;
        }
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'ไม่พบไฟล์ APK ในโฟลเดอร์ build/app/outputs/flutter-apk/'
    ]);
    exit;
}

// Action: Upload APK File
if ($action === 'upload_apk' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=UTF-8');
    if (!isset($_SESSION['admin_user'])) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบในฐานะ Admin ก่อนดำเนินการ']);
        exit;
    }

    if (!isset($_FILES['apk_file']) || $_FILES['apk_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์']);
        exit;
    }

    $uploaded = $_FILES['apk_file'];
    $ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
    if ($ext !== 'apk') {
        echo json_encode(['status' => 'error', 'message' => 'กรุณาอัปโหลดเฉพาะไฟล์นามสกุล .apk เท่านั้น']);
        exit;
    }

    $dest = __DIR__ . '/caffebook.apk';
    if (move_uploaded_file($uploaded['tmp_name'], $dest)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'อัปโหลดและแทนที่ไฟล์ APK สำเร็จเรียบร้อย!',
            'size_formatted' => formatBytes(filesize($dest))
        ]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถบันทึกไฟล์ APK ได้']);
        exit;
    }
}

// Default Action: Trigger Instant Automatic File Download
if ($apkPath !== null && file_exists($apkPath)) {
    // Disable any output compression & clear output buffers
    if (ini_get('zlib.output_compression')) {
        @ini_set('zlib.output_compression', 'Off');
    }
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Set high-speed binary streaming headers
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.android.package-archive');
    header('Content-Disposition: attachment; filename="CaffeBook.apk"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($apkPath));

    // Stream file in 64KB chunks
    $fp = @fopen($apkPath, 'rb');
    if ($fp) {
        while (!feof($fp)) {
            echo fread($fp, 1024 * 64);
            flush();
        }
        fclose($fp);
    } else {
        readfile($apkPath);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ดาวน์โหลด CaffeBook APK - สำหรับมือถือ Android</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #6F4E37;
            --primary-dark: #3E2723;
            --primary-soft: #F5EBE6;
            --accent: #E67E22;
            --bg-main: #F8F5F0;
            --card-bg: #FFFFFF;
            --border-color: #EAE3D9;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Prompt', 'Outfit', sans-serif; }
        body {
            background-color: var(--bg-main);
            color: #2C2523;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .container {
            max-width: 540px;
            width: 100%;
            background: var(--card-bg);
            border-radius: 20px;
            padding: 36px 30px;
            box-shadow: 0 12px 36px rgba(62, 39, 35, 0.08);
            border: 1.5px solid var(--border-color);
            text-align: center;
        }
        .app-icon-wrap {
            width: 80px;
            height: 80px;
            margin: 0 auto 18px;
            background: linear-gradient(135deg, #6F4E37, #3E2723);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(111, 78, 55, 0.25);
            color: #FAF5EE;
            font-size: 38px;
        }
        h1 { font-size: 1.5rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 8px; }
        p.subtitle { color: #7A726D; font-size: 0.95rem; line-height: 1.6; margin-bottom: 24px; }
        .alert-box {
            background: #FEF5E7;
            border: 1px solid #F8C471;
            border-radius: 12px;
            padding: 14px 18px;
            color: #B9770E;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            margin-bottom: 24px;
        }
        .guide-box {
            background: #FAF8F5;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 20px;
            text-align: left;
            margin-bottom: 24px;
        }
        .guide-box h4 {
            color: var(--primary-dark);
            font-size: 0.95rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .guide-box ol {
            padding-left: 20px;
            font-size: 0.88rem;
            color: #554A43;
            line-height: 1.8;
        }
        .guide-box code {
            background: #EAE3D9;
            color: var(--primary-dark);
            padding: 2px 6px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 12px;
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6F4E37, #5A3D28);
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(111, 78, 55, 0.25);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(111, 78, 55, 0.35);
        }
        .btn-outline {
            background: transparent;
            color: var(--primary-dark);
            border: 1.5px solid var(--border-color);
        }
        .btn-outline:hover {
            background: var(--primary-soft);
            border-color: var(--primary);
        }
        .upload-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px dashed var(--border-color);
        }
        .file-drop-area {
            border: 2px dashed #D0C5B8;
            border-radius: 12px;
            padding: 18px;
            background: #FDFBF7;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 12px;
        }
        .file-drop-area:hover {
            border-color: var(--primary);
            background: var(--primary-soft);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="app-icon-wrap">
            <i class="fa-solid fa-mug-hot"></i>
        </div>
        <h1>ดาวน์โหลด CaffeBook APK</h1>
        <p class="subtitle">ระบบดาวน์โหลดแอปพลิเคชันสำหรับมือถือ Android อัตโนมัติ</p>

        <div class="alert-box">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:1.3rem;"></i>
            <div>
                <strong>ยังไม่พบไฟล์ APK บนเซิร์ฟเวอร์</strong><br>
                <span>กรุณาสร้างไฟล์ APK ก่อน หรืออัปโหลดไฟล์เข้าสู่ระบบ</span>
            </div>
        </div>

        <div class="guide-box">
            <h4><i class="fa-solid fa-laptop-code"></i> วิธีสร้างไฟล์ APK บนคอมพิวเตอร์:</h4>
            <ol>
                <li>เปิดโฟลเดอร์โปรเจกต์ <code>myapp</code></li>
                <li>ดับเบิลคลิกไฟล์ <code>build_apk.bat</code></li>
                <li>เมื่อ build เสร็จ ไฟล์จะถูกซิงค์มายังหน้านี้ และเริ่มดาวน์โหลดให้อัตโนมัติ</li>
            </ol>
        </div>

        <!-- Direct Upload Box -->
        <div class="upload-section">
            <h4 style="font-size:0.9rem; color:#7A726D; margin-bottom:10px;">หรืออัปโหลดไฟล์ .apk โดยตรง:</h4>
            <form id="uploadApkForm" onsubmit="handleUploadApk(event)">
                <label class="file-drop-area" id="dropLabel" style="display:block;">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.8rem; color:var(--primary); margin-bottom:6px;"></i>
                    <div style="font-weight:600; font-size:0.88rem; color:var(--primary-dark);" id="fileSelectedText">คลิกเลือกไฟล์ หรือลากไฟล์ .apk มาวางที่นี่</div>
                    <input type="file" id="apkFileInput" name="apk_file" accept=".apk" style="display:none;" onchange="onFileChosen(this)">
                </label>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary" id="btnUploadSubmit" style="display:none;">
                        <i class="fa-solid fa-upload"></i> บันทึกและเปิดให้ดาวน์โหลด
                    </button>
                    <button type="button" class="btn btn-outline" onclick="checkApkAgain()">
                        <i class="fa-solid fa-arrows-rotate"></i> ตรวจหาไฟล์ใหม่
                    </button>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fa-solid fa-arrow-left"></i> กลับหลังบ้าน
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function onFileChosen(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                document.getElementById('fileSelectedText').innerText = 'เลือกไฟล์: ' + file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)';
                document.getElementById('btnUploadSubmit').style.display = 'inline-flex';
            }
        }

        async function handleUploadApk(e) {
            e.preventDefault();
            const fileInput = document.getElementById('apkFileInput');
            if (!fileInput.files || !fileInput.files[0]) {
                Swal.fire('แจ้งเตือน', 'กรุณาเลือกไฟล์ .apk ก่อน', 'warning');
                return;
            }

            const fd = new FormData();
            fd.append('action', 'upload_apk');
            fd.append('apk_file', fileInput.files[0]);

            Swal.fire({
                title: 'กำลังอัปโหลด...',
                text: 'กรุณารอสักครู่ กำลังจัดเก็บไฟล์ APK',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const res = await fetch('download_apk.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.status === 'success') {
                    Swal.fire('สำเร็จ', data.message, 'success').then(() => {
                        window.location.href = 'download_apk.php?auto=1';
                    });
                } else {
                    Swal.fire('ข้อผิดพลาด', data.message, 'error');
                }
            } catch (err) {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถอัปโหลดไฟล์ได้: ' + err.message, 'error');
            }
        }

        async function checkApkAgain() {
            Swal.fire({
                title: 'กำลังตรวจสอบ...',
                didOpen: () => Swal.showLoading()
            });

            try {
                // Try sync first
                await fetch('download_apk.php?action=sync');
                const res = await fetch('download_apk.php?action=check');
                const data = await res.json();
                if (data.exists) {
                    Swal.fire('พบไฟล์ APK แล้ว!', 'กำลังเริ่มดาวน์โหลดไฟล์ให้อัตโนมัติ...', 'success').then(() => {
                        window.location.href = 'download_apk.php';
                    });
                } else {
                    Swal.fire('ยังไม่พบไฟล์', 'ยังไม่พบไฟล์ APK บนเซิร์ฟเวอร์ กรุณารัน build_apk.bat', 'info');
                }
            } catch (e) {
                Swal.fire('ข้อผิดพลาด', e.message, 'error');
            }
        }
    </script>
</body>
</html>
