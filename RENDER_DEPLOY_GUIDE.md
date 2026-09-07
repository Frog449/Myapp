# 🚀 คู่มือการนำ Backend ขึ้น Render (Render.com Deployment Guide)

ระบบ Backend ของ **CaffeBook** ได้รับการออกแบบให้พร้อมสำหรับการนำขึ้นคลาวด์บน **[Render](https://render.com)** ผ่านระบบ **Docker (PHP 8.2 + Apache)** รองรับทั้ง:
- 🟢 **โหมด SQLite (แนะนำสำหรับ Free Tier)**: ใช้งานได้ฟรี 100% ทันที ไม่ต้องสร้างเซิร์ฟเวอร์ MySQL ภายนอก มีระบบ Auto-seed ข้อมูลหนังสือ 8 เล่มและบัญชี Admin ให้อัตโนมัติ
- 🔵 **โหมด MySQL**: รองรับการเชื่อมต่อกับ MySQL คลาวด์ภายนอก เช่น [TiDB Cloud](https://tidbcloud.com), [Aiven](https://aiven.io), [Railway](https://railway.app), [Clever Cloud](https://www.clever-cloud.com) ผ่าน `DATABASE_URL` หรือ Environment Variables

---

## 📋 ขั้นตอนการ Deploy ขึ้น Render ทีละขั้นตอน (Step-by-Step)

### ขั้นตอนที่ 1: นำโค้ดโปรเจกต์ขึ้น GitHub
1. ตรวจสอบว่าได้ Commit ไฟล์ทั้งหมด (รวมถึง `Dockerfile`, `docker-entrypoint.sh`, `render.yaml`, `api/`) ขึ้น GitHub Repository ของคุณเรียบร้อยแล้ว:
```bash
git add .
git commit -m "Add Render deployment support and Docker configuration"
git push origin main
```

---

### ขั้นตอนที่ 2: สร้าง Web Service บน Render
1. ไปที่เว็บไซต์ **[https://render.com](https://render.com)** และเข้าสู่ระบบ (เข้าสู่ระบบด้วยบัญชี GitHub ได้)
2. ที่หน้า Dashboard กดปุ่ม **"New +"** (มุมขวาบน) แล้วเลือก **"Web Service"**
3. เลือก **"Build and deploy from a Git repository"** แล้วกด **Next**
4. เลือก Repository โปรเจกต์ **`myapp` (CaffeBook)** ของคุณ แล้วกด **Connect**

---

### ขั้นตอนที่ 3: ตั้งค่า Web Service
กรอกข้อมูลการตั้งค่าดังนี้:

| ช่องการตั้งค่า | ค่าที่ต้องระบุ |
| :--- | :--- |
| **Name** | `caffebook-api` *(หรือชื่อที่คุณต้องการ)* |
| **Region** | `Singapore` *(แนะนำสำหรับความเร็วในไทย)* หรือ `Oregon` |
| **Branch** | `main` หรือ `master` |
| **Runtime** | **Docker** *(Render จะตรวจพบ Dockerfile อัตโนมัติ)* |
| **Instance Type** | **Free** ($0 / month) |

#### การตั้งค่าเพิ่มเติม (Advanced Settings):
- เลื่อนลงมาที่หัวข้อ **"Health Check Path"** ให้กรอก:
  ```text
  /health.php
  ```

---

### ขั้นตอนที่ 4: ตั้งค่า Environment Variables (ตัวแปรสภาพแวดล้อม)

เลื่อนลงมาที่ส่วน **"Environment Variables"** แล้วกด **"Add Environment Variable"**:

#### 💡 ตัวเลือก A: ใช้งาน SQLite (ฟรี 100% ทำงานได้ทันที ไม่ต้องมี DB ภายนอก)
| Key | Value |
| :--- | :--- |
| `DB_DRIVER` | `sqlite` |
| `APP_ENV` | `production` |

> [!NOTE]
> ระบบมี **Self-healing & Auto-seed** เมื่อเปิดใช้งานครั้งแรก ระบบจะสร้างตาราง `books`, `users`, `orders` และใส่ข้อมูลตัวอย่างหนังสือ 8 เล่ม พร้อมบัญชี Admin (`admin@caffebook.com` / `admin123`) ให้อัตโนมัติทันที

---

#### 💡 ตัวเลือก B: ใช้งาน Cloud MySQL ภายนอก (เช่น TiDB Cloud, Aiven, Railway)
หากมี MySQL บนคลาวด์ ให้ใส่ค่าดังนี้:
| Key | Value |
| :--- | :--- |
| `DB_DRIVER` | `mysql` |
| `DATABASE_URL` | `mysql://username:password@host:port/database_name` |
| `APP_ENV` | `production` |

*(หรือจะแยกใส่เป็น `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` ก็ได้เช่นกัน)*

---

### ขั้นตอนที่ 5: กด Deploy
1. กดปุ่ม **"Create Web Service"** ด้านล่างสุด
2. Render จะทำการ Build Docker Image และเริ่มรันเซิร์ฟเวอร์ (ใช้เวลาประมาณ 1-2 นาที)
3. เมื่อขึ้นสถานะ **`Live` (สีเขียว)** แสดงว่าเซิร์ฟเวอร์พร้อมใช้งานแล้ว!
4. คัดลอก URL ของคุณจากด้านบน เช่น:
   ```text
   https://caffebook-api.onrender.com
   ```

---

## 📱 การนำ URL ไปเชื่อมต่อในแอป Flutter / มือถือ

1. เปิดแอป CaffeBook (บนมือถือหรือเบราว์เซอร์)
2. คลิกที่ปุ่ม **ไอคอน Server (📶 / ⚙️)** บนแถบด้านบน
3. วาง URL ของ Render ที่ได้มา เช่น:
   ```text
   https://caffebook-api.onrender.com
   ```
4. กดปุ่ม **"ทดสอบการเชื่อมต่อ (Ping Server)"** ระบบจะขึ้น `✅ เชื่อมต่อกับเซิร์ฟเวอร์สำเร็จ!`
5. กด **"บันทึกและใช้งาน"** — แอปจะดึงข้อมูลหนังสือและสั่งซื้อสินค้าผ่าน Cloud ทันที!

---

## 💻 การเปิดหน้าจัดการหลังบ้าน (Web Admin) บน Render

คุณสามารถเปิดเบราว์เซอร์แล้วเข้าใช้งานหน้า Web Admin ผ่าน URL ของ Render ได้โดยตรง:
- **URL**: `https://caffebook-api.onrender.com`
- **บัญชี Admin เริ่มต้น**:
  - **อีเมล**: `admin@caffebook.com`
  - **รหัสผ่าน**: `admin123`

---

## 🛠️ ทดสอบ Docker บนเครื่องคอมพิวเตอร์ของคุณ (Local Testing)

หากต้องการทดสอบการทำงานของ Docker ก่อนขึ้น Render:
```bash
# รัน Backend + MySQL ด้วย Docker Compose
docker compose up --build

# เปิดใช้งาน Web Admin:
http://localhost:8000
```
