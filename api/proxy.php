<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: *");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$url = $_GET['url'] ?? '';
if (empty($url)) {
    http_response_code(400);
    echo "Missing url parameter";
    exit();
}

// Fetch image via cURL or file_get_contents with user-agent
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

$imageData = curl_exec($ch);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

if ($httpCode === 200 && !empty($imageData)) {
    if (!empty($contentType)) {
        header("Content-Type: " . $contentType);
    } else {
        header("Content-Type: image/jpeg");
    }
    echo $imageData;
} else {
    http_response_code(404);
    echo "Image fetch failed";
}
