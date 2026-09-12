# Mini X 📱 – Full-Stack Microblogging Web App

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat&logo=javascript&logoColor=black)](https://developer.mozilla.org/)
[![CSS3](https://img.shields.io/badge/CSS3-Responsive-1572B6?style=flat&logo=css3&logoColor=white)](https://www.w3.org/Style/CSS/)

> A responsive, full-stack microblogging platform (inspired by Twitter/X) built from scratch without heavy frameworks. Designed to demonstrate core backend architecture, relational database management, application security, and asynchronous UI updates.

*(Note: Application UI and mock data are localized in Finnish as part of the initial release.)*

---

## 🚀 Key Features

- **Authentication & Account Lifecycle:**
  - Secure registration and login with BCRYPT password hashing (`password_hash`).
  - Token-based email password reset workflow via `PHPMailer`.
  - **Soft delete & account reactivation:** Users can safely deactivate accounts and restore data upon re-login.
  - Avatar management (upload validation, MIME-type inspection, secure file storage, and fallback initials).
- **Post Management (CRUD):**
  - Create, read, edit, and delete posts (140-character limit with multibyte UTF-8 support).
  - Search and filter posts by username.
- **Social Interactions:**
  - Real-time like/unlike toggle and post commenting with ownership validation.
- **Asynchronous Notifications:**
  - Background polling mechanism built with vanilla JavaScript (`Fetch API`) providing real-time notification updates without page reloads.

---

## 🔒 Security & Engineering Best Practices

- **SQL Injection Prevention:** All database operations strictly use parameterized queries (`mysqli::prepare` and `bind_param`).
- **Cross-Site Scripting (XSS) Defense:** All dynamic output is escaped using `htmlspecialchars()`.
- **Open Redirect Protection:** Safe redirect validator verifies referer hostnames before executing header redirections.
- **UTF-8 Multibyte Safety:** Text truncation and initials generation use `mb_*` functions to support Scandinavian (`ä`, `ö`) and international characters without byte corruption.
- **Input Validation:** Multi-layer validation on both client-side (`JustValidate`) and server-side.

---

## 🛠️ Tech Stack

- **Backend:** PHP 8.x (Procedural / Modular Architecture)
- **Database:** MySQL (phpMyAdmin / XAMPP)
- **Frontend:** Semantic HTML5, Modern CSS3 (Dark Theme, Flexbox, Grid, Mobile Drawer), Vanilla JavaScript (ES6+)
- **Dependencies:** PHPMailer, JustValidate (via Composer / CDN)

---

## ⚡ Quick Setup (Local Development)

### Prerequisites
- XAMPP (Apache + MySQL with PHP 8.2+)

### Installation
1. Clone the repository into your XAMPP `htdocs` directory:
   ```bash
   git clone https://github.com/AlexNtrx/mini-X.git C:\xampp\htdocs\minisome
   ```
2. Start **Apache** and **MySQL** from XAMPP Control Panel.
3. Import the database:
   - Open `http://localhost/phpmyadmin/`
   - Create a database named `minisome` (`utf8mb4_unicode_ci`)
   - Import `minisome.sql`
4. Open the application:
   ```
   http://localhost/minisome/
   ```

### 🔑 Demo Credentials
- **Username:** `admin`
- **Password:** `admin123`
*(Or create a new account directly on the sign-up page)*
