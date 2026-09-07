@echo off
chcp 65001 >nul
title CaffeBook Backend Server (Port 8000)

echo ======================================================================
echo           ☕ CaffeBook Backend Server and Mobile API
echo ======================================================================
echo.

cd /d "%~dp0"

:: 1. Search for PHP executable
set "PHP_BIN="

:: Check Laragon PHP
for /d %%D in (C:\laragon\bin\php\php-*) do (
    if exist "%%D\php.exe" set "PHP_BIN=%%D\php.exe"
)

:: Check XAMPP PHP
if "%PHP_BIN%"=="" (
    if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
)

:: Check PATH
if "%PHP_BIN%"=="" (
    for %%X in (php.exe) do (
        if not "%%~$PATH:X"=="" set "PHP_BIN=%%~$PATH:X"
    )
)

if "%PHP_BIN%"=="" (
    echo [ERROR] ไม่พบ php.exe ในระบบ กรุณาติดตั้ง Laragon หรือ XAMPP
    echo.
    pause
    exit /b 1
)

:: 2. Find Current IPv4
set "LOCAL_IP=127.0.0.1"
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /i "IPv4"') do (
    for /f "tokens=1" %%b in ("%%a") do (
        set "LOCAL_IP=%%b"
    )
)

echo [OK] พบ PHP: "%PHP_BIN%"
echo [OK] IP เครื่องนี้: %LOCAL_IP%
echo.
echo ======================================================================
echo  - เปิดจัดการหลังบ้าน (Web Admin): http://127.0.0.1:8000
echo  - ลิงก์สำหรับมือถือใน Wi-Fi เดียวกัน: http://%LOCAL_IP%:8000
echo ======================================================================
echo.
echo กำลังเปิดเซิร์ฟเวอร์... (กด Ctrl+C เพื่อหยุด)
echo.

start http://127.0.0.1:8000
"%PHP_BIN%" -S 0.0.0.0:8000 -t api
pause