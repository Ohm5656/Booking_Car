# AutoBook — Car Booking System (PHP + MySQL)

ระบบจอง/ยืมรถสำหรับองค์กร — Stack ใหม่ที่พอร์ตมาจากเวอร์ชัน Next.js เดิม โดย **คงหน้าตา UI ทั้งหมดให้เหมือนเดิม** เปลี่ยนเฉพาะระบบหลังบ้าน

- **Frontend**: HTML + Tailwind CSS (Play CDN) + Vanilla JavaScript + Lucide icons + Chart.js
- **Backend**: PHP 8 (ไม่ต้องใช้ framework)
- **Database**: MySQL (InnoDB, utf8mb4)
- **Local server**: XAMPP (Apache + MySQL + phpMyAdmin)
- **Production**: deploy ได้บน PHP hosting / cPanel ทันที (รายละเอียดท้ายไฟล์)

---

## 1) ติดตั้ง XAMPP

1. ดาวน์โหลด XAMPP จาก https://www.apachefriends.org/ — เวอร์ชันที่มี **PHP 8.1 ขึ้นไป**
2. ติดตั้ง แล้วเปิด **XAMPP Control Panel**
3. กด **Start** ที่ทั้ง **Apache** และ **MySQL** ให้ไฟขึ้นสีเขียวทั้งคู่
4. เปิดเบราว์เซอร์ไปที่ http://localhost — ถ้าเห็นหน้า XAMPP แสดงว่าใช้งานได้

## 2) วางโปรเจคใน `htdocs`

คัดลอกโฟลเดอร์ `car-booking-system/` ทั้งโฟลเดอร์เข้าไปไว้ใน:

```
C:\xampp\htdocs\car-booking-system\
```

(บน Mac: `/Applications/XAMPP/htdocs/car-booking-system/`)

โครงสร้างที่ถูกต้อง:

```
car-booking-system/
├── assets/{css,js,images}/
├── includes/{db,auth,helpers,header,footer,navbar,sidebar}.php
├── auth/{login,register,logout}.php
├── user/{dashboard,cars,booking-create,my-bookings,_car_card}.php
├── admin/{dashboard,cars,car-create,car-edit,_car_form,_layout_start,_layout_end,requests,bookings,reports}.php
├── database/database.sql
├── index.php
└── README.md
```

## 3) สร้างฐานข้อมูลและ Import

1. เปิด http://localhost/phpmyadmin
2. คลิก **New** ที่แถบซ้าย → ตั้งชื่อ database ว่า **`car_booking`** → Collation = **`utf8mb4_unicode_ci`** → กด **Create**
3. เลือก database `car_booking` → คลิกแท็บ **Import**
4. กด **Choose File** เลือกไฟล์ `database/database.sql` ในโปรเจค → กด **Go**
5. ระบบจะสร้างตาราง `users`, `cars`, `bookings` และใส่ข้อมูล demo ให้อัตโนมัติ

> ถ้าใช้ MySQL ตัวอื่น หรือ database ชื่ออื่น — ให้ไปแก้ใน `includes/db.php`

## 4) ตั้งค่าการเชื่อมต่อ DB

เปิดไฟล์ `includes/db.php` แล้วแก้ค่าด้านบนตามต้องการ:

```php
const DB_HOST    = 'localhost';
const DB_NAME    = 'car_booking';
const DB_USER    = 'root';   // XAMPP default
const DB_PASS    = '';       // XAMPP default (ว่าง)
const DB_CHARSET = 'utf8mb4';

const BASE_URL = '/car-booking-system';  // ดูข้อ "Deploy" ด้านล่าง
```

> ถ้าคุณวางโปรเจคไว้ที่ document root (เช่น cPanel โดเมนตรง ๆ) ให้ตั้ง `BASE_URL = ''`

## 5) เปิดใช้งาน

ไปที่ http://localhost/car-booking-system

ระบบจะ redirect ไปหน้า login อัตโนมัติ

---

## บัญชี Demo

| Role  | Email                | Password       |
| ----- | -------------------- | -------------- |
| Admin | `admin@example.com`  | `admin123456`  |
| User  | `user@example.com`   | `user123456`   |

> รหัสผ่านถูก hash ด้วย bcrypt ($2b$ prefix — PHP `password_verify()` รองรับเต็มรูปแบบ)

---

## ฟังก์ชันของระบบ

### ฝั่ง User
- **Dashboard** — Hero landing + how-it-works + รถพร้อมจอง (3 คัน) + การจองล่าสุด
- **Vehicles** — Showroom พร้อม search / filter ตามสถานะ / filter ตามประเภท
- **Booking** — ส่งคำขอจอง (กรอกวันที่ ปลายทาง เหตุผล เบอร์โทร)
- **My Bookings** — ดูประวัติการจองทั้งหมด พร้อม filter ตามสถานะ

### ฝั่ง Admin
- **Overview** — สถิติรวม + คำขอที่รออนุมัติ
- **Vehicles** — เพิ่ม/แก้ไข/ลบรถในระบบ
- **Requests** — ตรวจสอบ + อนุมัติ/ปฏิเสธคำขอ + ใส่ admin note
- **History** — ประวัติการจองทั้งหมด + filter + mark as completed
- **Reports** — แผนภูมิ booking by status (bar) + cars by type (donut) + สรุปยอด

### Logic การจอง (สำคัญ)
- รถ 1 คันมี booking ได้หลายรายการ
- ห้ามมีช่วงวันที่ทับซ้อนกัน ถ้า status เป็น `pending` หรือ `approved`
- ส่งคำขอ → `pending`
- อนุมัติ → `approved` (รถเปลี่ยนเป็น `booked` ทันที)
- ไม่อนุมัติ → `rejected`
- ทำเครื่องหมายเสร็จสิ้น → `completed` (รถกลับมา `available`)
- ทุก endpoint admin มี `require_admin()` กั้นไว้ ผู้ใช้ทั่วไปเข้าไม่ได้

---

## Security

ระบบนี้ปฏิบัติตามแนวปฏิบัติพื้นฐาน:

- ✅ **PDO + Prepared Statements** ทุก query — ปลอดภัยจาก SQL Injection
- ✅ **password_hash()** ตอนสมัคร + `password_verify()` ตอน login (bcrypt)
- ✅ **PHP Session** + `session_regenerate_id()` หลัง login เพื่อป้องกัน Session Fixation
- ✅ **CSRF token** บนทุก POST form (ฟอร์ม + AJAX)
- ✅ **HttpOnly + SameSite=Lax cookie**
- ✅ **HTML escape (`htmlspecialchars`)** ผ่าน helper `e()` กับทุก dynamic output
- ✅ **Role guard** ทุกหน้า admin เช็ก role ก่อนเข้า

---

## โครงสร้างไฟล์

```
car-booking-system/
├── assets/
│   ├── css/style.css         # globals.css → CSS เดียวกัน + animations
│   ├── js/main.js            # toast, modal, lucide, navbar scroll, reveal
│   └── images/               # corporate-hero.png, hero-cars.png, icon.png
│
├── includes/
│   ├── db.php                # PDO connection + config + e() + url()
│   ├── auth.php              # session, current_user(), require_login/admin/user, flash, csrf
│   ├── helpers.php           # status_badge(), car_image(), thai_date(), has_overlap()
│   ├── header.php            # <head> + Tailwind config + fonts
│   ├── footer.php            # </body> + main.js
│   ├── navbar.php            # User top nav (เทียบเท่า Navbar.tsx)
│   └── sidebar.php           # Admin sidebar (เทียบเท่า Sidebar.tsx)
│
├── auth/
│   ├── login.php             # Sign in
│   ├── register.php          # Request access
│   └── logout.php            # POST → destroy session
│
├── user/                     # ต้อง login (USER role)
│   ├── dashboard.php         # Hero + how-it-works + featured + recent
│   ├── cars.php              # Showroom (filter sidebar + grid)
│   ├── booking-create.php    # ส่งคำขอจอง (รับ ?car_id=)
│   ├── my-bookings.php       # ประวัติของฉัน + filter pills
│   └── _car_card.php         # Partial (เทียบเท่า CarCard.tsx)
│
├── admin/                    # ต้อง login + admin role
│   ├── dashboard.php         # Stats + pending table
│   ├── cars.php              # List + delete
│   ├── car-create.php
│   ├── car-edit.php
│   ├── _car_form.php         # Partial form
│   ├── _layout_start.php     # Sidebar shell open
│   ├── _layout_end.php       # Sidebar shell close
│   ├── requests.php          # Pending list + review modal (approve/reject)
│   ├── bookings.php          # History + filter + mark complete
│   └── reports.php           # Chart.js: bar + donut + summary
│
├── database/
│   └── database.sql          # Schema + seed
│
├── index.php                 # Smart redirect → login / dashboard
└── README.md
```

---

## หน้าตา (UI Parity กับเวอร์ชัน Next.js เดิม)

- **Font**: Inter (Latin) + Sarabun (Thai) + Fraunces (display serif, italic) ผ่าน Google Fonts
- **Color tokens**: stone-50 background, stone-900 primary CTA, copper-500 (#b45309) accent rule/dot, navy accent-600 (#1e40af) สำหรับ status
- **Editorial elements**: `.display-serif`, `.section-number`, `.eyebrow` ใช้คลาสและฟอนต์เดียวกัน
- **Components**: StatusBadge, StatsCard, DataTable, Modal, CarCard, BookingForm — ทั้งหมดถูกพอร์ตมาเป็น PHP partial หรือ inline markup โดยคง Tailwind class เดิมทุกตัว
- **Animations**: 
  - hover scale/lift ของการ์ด → `.card-hover` (CSS transitions)
  - marquee → CSS keyframes (โครงสร้างเดิม)
  - fade-in / slide-up → `.reveal` (IntersectionObserver)
  - sidebar transitions → CSS transitions
  - loading spinner → `.animate-spin-slow`
  - modal open/close → scale-in + fade-up keyframes
  - prefers-reduced-motion ทำงานครบ

> Framer Motion ไม่มีอยู่แล้วในเวอร์ชันนี้ — animation ทุกตัวถูกแทนด้วย CSS หรือ Vanilla JS

---

## การ Deploy ขึ้น PHP Hosting / cPanel

โปรเจคถูกออกแบบให้ deploy ได้ทันที ไม่มี build step

### ขั้นตอน:
1. **อัปโหลด** โฟลเดอร์ทั้งหมดผ่าน FTP / File Manager ของ cPanel
   - ถ้าโดเมนหลัก: ให้วางใน `public_html/`
   - ถ้า subdomain หรือ subfolder: วางใน path ที่ตรงกับ domain
2. **สร้าง MySQL database** ใน cPanel → MySQL Databases:
   - สร้าง database
   - สร้าง user + รหัสผ่าน
   - Add user to database (ให้ ALL PRIVILEGES)
3. **Import SQL** ผ่าน phpMyAdmin ของ cPanel:
   - เลือก database ที่สร้าง
   - Import → เลือก `database/database.sql`
4. **แก้ไฟล์ `includes/db.php`** ใส่:
   ```php
   const DB_HOST = 'localhost';                  // หรือ host ที่โฮสต์ระบุ
   const DB_NAME = 'youraccount_carbooking';     // ชื่อ database ของจริง
   const DB_USER = 'youraccount_appuser';
   const DB_PASS = 'แอบไม่ใช่นี้ครับ';
   const BASE_URL = '';                          // ตั้งเป็น '' ถ้าวางบน document root
   ```
5. **เปลี่ยนรหัสผ่าน admin demo** ทันทีหลัง deploy (login ด้วย admin → สร้าง user ใหม่ที่เป็น admin ผ่าน DB หรือเปลี่ยน password hash)

### Checklist หลัง deploy
- ✅ เข้าหน้าแรกแล้ว redirect ไป login ได้
- ✅ login admin / user demo ได้
- ✅ ส่งคำขอจอง → admin approve → status เปลี่ยนถูกต้อง
- ✅ ลบ user demo และ admin demo ออก (หรือเปลี่ยนรหัส)
- ✅ ตั้ง HTTPS (Let's Encrypt บน cPanel ฟรี) — ระบบจะใช้ secure cookie อัตโนมัติเมื่อเป็น HTTPS

### Tips
- ถ้าโฮสต์บังคับ PHP version เก่ากว่า 8.0 ระบบจะใช้งานไม่ได้ (มี typed properties + `match` ในบางจุด) — กรุณาใช้ PHP 8.0+
- ถ้าไม่อยากให้ Tailwind CDN โหลด CSS ขนาดใหญ่บน production ให้ build เป็น CSS static แล้วค่อยอ้างใน `includes/header.php` — แต่ตอน demo จะใช้ CDN ก็เพียงพอ

---

## Troubleshooting

| ปัญหา | สาเหตุ / วิธีแก้ |
| ----- | ---------------- |
| `Database connection failed` ตอนเข้าหน้าแรก | แก้ค่า `DB_HOST/NAME/USER/PASS` ใน `includes/db.php` |
| ทุกหน้า redirect ไป `/auth/login.php` แต่ลิงก์ผิด | ตรวจ `BASE_URL` ใน `db.php` ให้ตรงกับ path จริงบน server |
| Login แล้ว session หาย / โดน redirect กลับ | บางโฮสต์ปิด `session.save_path` — เช็คกับโฮสต์, ปกติ cPanel ใช้ได้เลย |
| Status badge ไม่แสดง icon | Lucide CDN ถูกบล็อก — ตรวจ network tab, ถ้าโดน CSP ให้ทำ self-host |
| ฟ้อนต์ Thai ไม่สวย | บางบราว์เซอร์เก่าไม่รองรับ Sarabun variable — เพิ่ม subset เพิ่มในลิงก์ Google Fonts หรือ self-host |

---

## License

Internal use only — เหมือนเวอร์ชันต้นฉบับ Next.js
#   B o o k i n g _ C a r  
 