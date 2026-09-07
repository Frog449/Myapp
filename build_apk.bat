@echo off
chcp 65001 >nul
title CaffeBook - Android APK Builder

echo ======================================================================
echo           📦 ระบบ Build Android APK อัตโนมัติ - CaffeBook
echo ======================================================================
echo.

cd /d "%~dp0"

:: Check Flutter in PATH or common paths
set "FLUTTER_CMD=flutter"
where flutter >nul 2>nul
if %errorlevel% neq 0 (
    if exist "D:\flutter\bin\flutter.bat" (
        set "FLUTTER_CMD=D:\flutter\bin\flutter.bat"
    ) else if exist "C:\flutter\bin\flutter.bat" (
        set "FLUTTER_CMD=C:\flutter\bin\flutter.bat"
    ) else if exist "C:\src\flutter\bin\flutter.bat" (
        set "FLUTTER_CMD=C:\src\flutter\bin\flutter.bat"
    ) else (
        echo [ERROR] ไม่พบ Flutter SDK ในระบบ
        echo.
        pause
        exit /b 1
    )
)

echo [1/3] กำลังเตรียมแพ็กเกจ (flutter pub get)...
call %FLUTTER_CMD% pub get
if %errorlevel% neq 0 (
    echo [ERROR] ไม่สามารถดาวน์โหลด Dependencies ได้
    pause
    exit /b 1
)

echo.
echo [2/3] กำลังสร้างไฟล์ APK (Release Mode)...
echo (ขั้นตอนนี้อาจใช้เวลาประมาณ 1-3 นาที)
call %FLUTTER_CMD% build apk --release

if %errorlevel% equ 0 (
    copy /y "build\app\outputs\flutter-apk\app-release.apk" "api\caffebook.apk" >nul 2>nul
    echo.
    echo ======================================================================
    echo  🎉 สร้างไฟล์ติดตั้ง APK สำเร็จเรียบร้อยแล้ว!
    echo  📁 ตำแหน่งไฟล์: build\app\outputs\flutter-apk\app-release.apk
    echo  🌐 โหลดผ่านเบราว์เซอร์มือถือได้ที่: http://[SERVER_IP]:8000/download_apk.php
    echo ======================================================================
    echo.
    explorer.exe "build\app\outputs\flutter-apk"
) else (
    echo.
    echo [WARN] Release build ไม่สำเร็จ กำลังลองสร้าง Debug APK...
    call %FLUTTER_CMD% build apk --debug
    if %errorlevel% equ 0 (
        copy /y "build\app\outputs\flutter-apk\app-debug.apk" "api\caffebook.apk" >nul 2>nul
        echo.
        echo  🎉 สร้างไฟล์ Debug APK สำเร็จ!
        echo  📁 ตำแหน่งไฟล์: build\app\outputs\flutter-apk\app-debug.apk
        explorer.exe "build\app\outputs\flutter-apk"
    ) else (
        echo [ERROR] ไม่สามารถสร้าง APK ได้ กรุณาตรวจสอบข้อผิดพลาดด้านบน
    )
)

pause