<?php
// APK Downloader script for CaffeBook
$possiblePaths = [
    __DIR__ . '/caffebook.apk',
    __DIR__ . '/../build/app/outputs/flutter-apk/app-release.apk',
    __DIR__ . '/../build/app/outputs/flutter-apk/app-debug.apk',
];

$apkPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path) && filesize($path) > 0) {
        $apkPath = $path;
        break;
    }
}

if ($apkPath !== null) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.android.package-archive');
    header('Content-Disposition: attachment; filename="CaffeBook.apk"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($apkPath));
    readfile($apkPath);
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ดาวน์โหลด CaffeBook APK</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #fdfbf7; color: #3c2a21; text-align: center; padding: 40px 20px; }
        .card { max-width: 500px; margin: 0 auto; background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .icon { font-size: 50px; margin-bottom: 15px; }
        h1 { color: #c67c4e; font-size: 24px; margin-bottom: 10px; }
        p { color: #75655b; line-height: 1.6; font-size: 15px; }
        .btn { display: inline-block; background: #c67c4e; color: white; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-weight: bold; margin-top: 20px; }
        .btn:hover { background: #3c2a21; }
        .steps { text-align: left; background: #faf4ed; padding: 15px 20px; border-radius: 10px; margin-top: 20px; font-size: 14px; }
        .steps li { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">☕📱</div>
        <h1>ดาวน์โหลดแอป CaffeBook (Android APK)</h1>
        <p>ยังไม่พบไฟล์ APK ที่สร้างเสร็จแล้วบนเครื่องเซิร์ฟเวอร์</p>
        <div class="steps">
            <strong>วิธีสร้างไฟล์ APK บนคอมพิวเตอร์:</strong>
            <ol>
                <li>เปิดโฟลเดอร์โปรเจกต์บนเครื่องคอมพิวเตอร์</li>
                <li>ดับเบิลคลิกไฟล์ <code>build_apk.bat</code></li>
                <li>เมื่อสร้างเสร็จ ให้กลับมารีเฟรชหน้านี้เพื่อดาวน์โหลดลงมือถือได้ทันที</li>
            </ol>
        </div>
        <a href="/" class="btn">กลับหน้าหลัก Web Admin</a>
    </div>
</body>
</html>
