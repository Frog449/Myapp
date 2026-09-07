@echo off
chcp 65001 >nul
title ติดตั้ง Flutter SDK อัตโนมัติ - CaffeBook
echo ======================================================================
echo           🚀 ติดตั้ง Flutter SDK สำหรับ Build APK อัตโนมัติ
echo ======================================================================
echo.

set "TARGET_DIR=C:\tools\flutter"
if not exist "C:\tools" (
    mkdir "C:\tools" 2>nul
)

if exist "%TARGET_DIR%\bin\flutter.bat" (
    echo [OK] พบ Flutter SDK อยู่แล้วที่: %TARGET_DIR%
    goto :add_path
)

echo [1/3] กำลังดาวน์โหลด Flutter SDK จาก Google...
echo (ไฟล์ขนาดประมาณ 1 GB อาจใช้เวลาสักครู่ ขึ้นอยู่กับความเร็วอินเทอร์เน็ต)
curl.exe -L "https://storage.googleapis.com/flutter_infra_release/releases/stable/windows/flutter_windows_3.24.5-stable.zip" -o "%TEMP%\flutter_sdk.zip"

if %errorlevel% neq 0 (
    echo [ERROR] ดาวน์โหลดไม่สำเร็จ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต
    pause
    exit /b 1
)

echo.
echo [2/3] กำลังแตกไฟล์ไปยัง %TARGET_DIR% ...
tar.exe -xf "%TEMP%\flutter_sdk.zip" -C "C:\tools"

del "%TEMP%\flutter_sdk.zip" 2>nul

:add_path
echo.
echo [3/3] กำลังตั้งค่า Environment PATH...
setx PATH "%PATH%;C:\tools\flutter\bin" >nul 2>nul

echo.
echo ======================================================================
echo  🎉 ติดตั้ง Flutter SDK สำเร็จเรียบร้อยแล้ว!
echo  ตอนนี้คุณสามารถดับเบิลคลิก build_apk.bat เพื่อสร้าง APK ได้ทันที
echo ======================================================================
echo.
pause
