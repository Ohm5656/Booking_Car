# 🚗 AutoBook — Enterprise Vehicle Booking System

A **sleek, modern fleet management platform** built with PHP & MySQL. Designed for corporate teams to request, approve, and track vehicle bookings seamlessly.

> Ported from Next.js to **pure PHP** — same beautiful UI, zero build step, deploys anywhere.

![Status](https://img.shields.io/badge/status-production%20ready-brightgreen)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-005C87)
![License](https://img.shields.io/badge/license-MIT-blue)

### ✨ What's Inside
- **Responsive Admin Panel** — Hamburger menu on mobile, full sidebar on desktop
- **Car Image Persistence** — Images stored in database (never lost on migration)
- **Real-time Booking Approvals** — Manage requests with admin notes
- **Advanced Analytics** — Bar charts, donut charts, fleet stats
- **Thai Language Ready** — Full localization + Thai date formatting
- **Zero Build Step** — Upload files, it just works on any PHP hosting

---

## 🚀 Quick Start (5 minutes)

### Step 1: Install XAMPP
1. Download from [apachefriends.org](https://www.apachefriends.org/) — PHP 8.0+ required
2. Install and launch **XAMPP Control Panel**
3. Click **Start** for both **Apache** and **MySQL** (green lights = good)
4. Verify at http://localhost — you should see the XAMPP dashboard

### Step 2: Place Project in htdocs
Copy the `car-booking-system/` folder to:

**Windows:**
```
C:\xampp\htdocs\car-booking-system\
```

**Mac:**
```
/Applications/XAMPP/htdocs/car-booking-system/
```

### Step 3: Create Database
1. Open http://localhost/phpmyadmin
2. Click **New** (left panel) → Name it `car_booking`
3. Set Collation to **utf8mb4_unicode_ci**
4. Click **Create**

### Step 4: Import Schema
1. Select the `car_booking` database
2. Click **Import** tab → **Choose File** → select `database/database.sql`
3. Click **Go**
4. ✅ Tables and demo data are created automatically

### Step 5: Configure (Optional)
Edit `includes/db.php` if using different credentials:

```php
const DB_HOST = 'localhost';      // Your host
const DB_NAME = 'car_booking';    // Your database
const DB_USER = 'root';           // Your user
const DB_PASS = '';               // Your password
const BASE_URL = '/car-booking-system';  // Adjust if needed
```

### Step 6: Launch
Navigate to http://localhost/car-booking-system ✨

---

## 👤 Demo Accounts

Log in with these credentials:

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@example.com` | `admin123456` |
| **User** | `user@example.com` | `user123456` |

> Passwords are bcrypt-hashed. Change these immediately in production!

---

## 🎯 Features

### 👤 User Dashboard
- **Hero Homepage** — Beautiful landing with featured vehicles and quick stats
- **Fleet Catalog** — Advanced search and filters (status, vehicle type, keywords)
- **Booking Requests** — Intuitive form to request vehicles with dates and destination
- **Booking History** — Track all your requests with real-time status updates
- **Responsive UI** — Works flawlessly on desktop, tablet, and mobile

### 🛠 Admin Console
- **Fleet Management** — Add, edit, delete vehicles with image uploads
  - *✨ Images persist in the database — never lost on migration!*
- **Booking Approvals** — Review requests, approve/reject with admin notes
- **Booking History** — Full audit trail with completion tracking
- **Analytics Dashboard** — Visual charts of booking trends and fleet composition
- **Mobile-Friendly Sidebar** — Hamburger menu on phones, full sidebar on desktop

### 🔄 Booking Logic
1. User requests vehicle → Status: `pending`
2. Admin approves → Status: `approved` + Vehicle: `booked`
3. Admin completes return → Status: `completed` + Vehicle: `available` again
4. Prevents double-booking by checking date overlap for `pending` or `approved` bookings
5. All admin endpoints protected with role-based access control

---

## 🔐 Security & Best Practices

The system follows modern security standards:

- ✅ **PDO Prepared Statements** — All queries parameterized, immune to SQL injection
- ✅ **Bcrypt Password Hashing** — Industry-standard with salt-based verification
- ✅ **CSRF Protection** — Token validation on all POST requests
- ✅ **Session Management** — `session_regenerate_id()` after login
- ✅ **XSS Prevention** — Output escaped with `htmlspecialchars()` via `e()` helper
- ✅ **Role-Based Access Control** — Admin endpoints check permissions before granting access
- ✅ **Input Validation** — Email format, file type, file size verification
- ✅ **Secure Cookies** — HttpOnly + SameSite=Lax flags set

**For Production:**
- Change all demo passwords immediately
- Use HTTPS/SSL everywhere
- Set strong database credentials
- Keep PHP and MySQL updated
- Consider a Web Application Firewall (WAF)

---

## 🛠 Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| **Frontend** | HTML5 + Tailwind CSS (CDN) | v4 JIT |
| **Interactivity** | Vanilla JavaScript | ES6+ |
| **Icons** | Lucide SVG Icons | Latest |
| **Charts** | Chart.js | 4.4+ |
| **Fonts** | Google Fonts (Inter, Sarabun, Fraunces) | Variable |
| **Backend** | PHP | 8.0+ |
| **Database** | MySQL | 5.7+ |
| **Server** | Apache | 2.4+ |

---

## ✨ Recent Improvements (May 2026)

### 1. Mobile-Responsive Admin Panel
- **Before:** Admin sidebar always visible, desktop-only layout
- **Now:** Hamburger menu drawer on mobile, auto-collapses on navigation
- **How it works:** CSS transforms + vanilla JS toggle for smooth UX
- **Benefits:** Full admin control from phones and tablets

### 2. Image Persistence (Base64 in Database)
- **Before:** Car images stored as files on filesystem → lost during migration
- **Now:** Images stored as base64 data URIs in MySQL database
- **How it works:** When uploading, PHP encodes to base64 and saves directly to DB
- **Benefits:**
  - ✅ Images persist across server migrations
  - ✅ Works seamlessly on XAMPP, InfinityFree, Vercel, or any PHP hosting
  - ✅ Travels with database backups automatically
  - ✅ No filesystem dependency

**Technical Details:**
- Image column: `MEDIUMTEXT` (16 MB capacity)
- Max upload: 5 MB per image
- Supported formats: JPEG, PNG, WebP
- Backward compatible with legacy file-based images

---

## 📁 File Structure

```
car-booking-system/
├── assets/
│   ├── css/style.css              # Global styles + animations + mobile sidebar
│   ├── js/main.js                 # Toasts, modals, sidebar toggle, Lucide icons
│   └── images/                    # Hero images + favicon
│
├── includes/
│   ├── db.php                     # Database config + PDO connection
│   ├── auth.php                   # Session management + CSRF + authentication
│   ├── helpers.php                # Status badges + car images + date formatting
│   ├── header.php                 # <head> + Tailwind + Google Fonts
│   ├── navbar.php                 # User navigation bar (logged-in view)
│   ├── sidebar.php                # Admin sidebar + mobile hamburger menu
│   └── footer.php                 # </body> + script tags
│
├── auth/
│   ├── login.php                  # Login form
│   ├── register.php               # Registration form
│   └── logout.php                 # Logout handler
│
├── user/                          # User-only pages (require_user())
│   ├── dashboard.php              # Homepage with featured vehicles
│   ├── cars.php                   # Vehicle catalog with search/filters
│   ├── booking-create.php         # New booking form
│   ├── my-bookings.php            # User's booking history
│   └── _car_card.php              # Car card component (partial)
│
├── admin/                         # Admin-only pages (require_admin())
│   ├── dashboard.php              # Admin overview + stats
│   ├── cars.php                   # Fleet management (list/delete)
│   ├── car-create.php             # Add new vehicle + image upload
│   ├── car-edit.php               # Edit vehicle + image replacement
│   ├── _car_form.php              # Shared form component (create/edit)
│   ├── _layout_start.php          # Admin page wrapper (opening)
│   ├── _layout_end.php            # Admin page wrapper (closing)
│   ├── requests.php               # Pending booking approvals
│   ├── bookings.php               # Booking history + completion
│   └── reports.php                # Analytics charts
│
├── database/
│   └── database.sql               # MySQL schema + demo data
│
├── index.php                      # Smart redirect (login → dashboard)
└── README.md                      # This file
```

---

## 🎨 Design System

**Typography:**
- Inter (Latin) + Sarabun (Thai) + Fraunces (editorial serif) — all from Google Fonts
- Responsive text sizing with `tabular-nums` for metrics

**Color Palette:**
- Stone-50 background + stone-900 primary CTAs
- Copper-500 (#b45309) for accent rules and highlights
- Navy accent-600 (#1e40af) for status badges

**Components:**
- Status badges, stats cards, data tables, modals, car cards — all responsive
- Tailwind utility classes throughout (no custom frameworks)

**Animations:**
- Card hover: lift + shadow (CSS transitions)
- Fade-in / slide-up: IntersectionObserver-based reveals
- Sidebar: smooth transform transition on mobile
- Modals: scale-in + fade-up keyframes
- Respects `prefers-reduced-motion` globally

> **No dependencies.** Pure CSS animations + vanilla JavaScript. Works everywhere.

---

## 🚀 Deployment

**Zero build step.** Upload files, configure credentials, done.

### To InfinityFree / Shared Hosting
1. Upload all files via **File Manager** or **FTP** to `public_html/`
2. Create MySQL database via **cPanel → MySQL Databases**
3. Import `database/database.sql` via **phpMyAdmin**
4. Update credentials in `includes/db.php`:
   ```php
   const DB_HOST = 'localhost';          // or your host
   const DB_NAME = 'yourdb_carbooking';  // your database
   const DB_USER = 'youruser';           // your username
   const DB_PASS = 'yourpass';           // your password
   const BASE_URL = '';                  // empty if at document root
   ```
5. ⚠️ **Important:** Run this SQL command in phpMyAdmin (one-time):
   ```sql
   ALTER TABLE `cars` MODIFY `image` MEDIUMTEXT DEFAULT NULL;
   ```
   This allows the image persistence system to work.

6. **Change demo passwords immediately** (or delete demo users)

### Post-Deployment Checklist
- ✅ Homepage redirects to login
- ✅ Can log in as admin and user
- ✅ Can upload car images (persists in database)
- ✅ Can create and approve bookings
- ✅ HTTPS is enabled (free SSL via Let's Encrypt)

### Requirements
- PHP **8.0 or higher**
- MySQL **5.7 or higher**
- Apache with `mod_rewrite` enabled

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| **Database connection failed** | Check `DB_HOST/NAME/USER/PASS` in `includes/db.php` |
| **All redirects break** | Verify `BASE_URL` matches your actual path on server |
| **Images not showing after upload** | Did you run the `ALTER TABLE` command? Check if `image` column is `MEDIUMTEXT` |
| **Sidebar toggle not working** | Hard refresh browser (`Ctrl+Shift+R`), check JS console for errors |
| **Icons not showing** | Lucide CDN might be blocked; check Content Security Policy headers |
| **Thai fonts look wrong** | Some browsers need font subsetting; check Google Fonts link |

---

## 📞 Support & Contributing

Found a bug? Have suggestions? Feel free to:
1. Check existing issues
2. Open a new issue with details
3. Submit a pull request with improvements

---

## 📄 License & Attribution

This project is for **internal corporate use only.**

**Built by:** Ohm
**Email:** natdanailunaha@gmail.com  
**Year:** 2026

---


