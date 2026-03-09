<div align="center">

<h1>📦 PUPT SMIS</h1>
<h3>Supply Management Information System</h3>
<p><em>PUP Taguig — Group Gina's Astral Angels</em></p>

<p>
  <a href="https://www.php.net/">
    <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.x">
  </a>
  <a href="https://laravel.com/">
    <img src="https://img.shields.io/badge/Laravel-Framework-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Framework">
  </a>
  <a href="https://www.mysql.com/">
    <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL Database">
  </a>
  <a href="LICENSE">
    <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT License">
  </a>
</p>

<p>
  A web-based <strong>Supply Management Information System</strong> designed for Pamantasan ng Lungsod ng Pasig (PUPT), enabling efficient tracking, requesting, and management of institutional supplies.
</p>

<p>
  <a href="#-features">
    <img src="https://img.shields.io/badge/View-Features-6A5ACD?style=flat-square" alt="View Features">
  </a>
  <a href="#-getting-started">
    <img src="https://img.shields.io/badge/Open-Getting_Started-0A7EA4?style=flat-square" alt="Open Getting Started">
  </a>
  <a href="#-user-roles">
    <img src="https://img.shields.io/badge/Check-User_Roles-2E8B57?style=flat-square" alt="Check User Roles">
  </a>
</p>

</div>

---

## 📋 Table of Contents

- [About the Project](#-about-the-project)
- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [System Requirements](#-system-requirements)
- [Getting Started](#-getting-started)
- [Project Structure](#-project-structure)
- [User Roles](#-user-roles)
- [Contributors](#-contributors)
- [License](#-license)

---

## 🏫 About the Project

**PUPT SMIS** (Supply Management Information System) is a full-stack web application built to streamline the supply requisition and inventory management process at PUPT. It replaces manual paperwork with a digital workflow that supports multiple roles — from regular staff requesting supplies to administrators managing inventory and approving requests.

The system provides a transparent, traceable, and efficient way to handle supplies — from request submission all the way to issuance and archiving.

## 🌟 At a Glance

| 🎯 Focus | 📌 What the system delivers |
|----------|-----------------------------|
| **Request Automation** | Replaces paper-based supply requests with a guided digital workflow |
| **Inventory Visibility** | Gives administrators a clear view of stock levels and supply movement |
| **Role-Based Access** | Keeps actions organized and secure for staff and administrators |
| **Audit & Reporting** | Preserves request history, approvals, issuance, and archived records |

---

## ✨ Features

### 🔐 Authentication & Security
- Secure login with Gmail-based email validation
- Role-Based Access Control (RBAC) using Spatie Permissions
- Login cooldown on failed attempts
- Forgot password with email reset link
- CSRF protection and session management

### 📊 Admin Dashboard
- At-a-glance supply inventory overview
- Pending requests counter and notifications
- Audit logging for all admin actions
- Real-time notification system with counters

### 📦 Supply Inventory Management
- Add, edit, and delete supply items
- Track stock numbers, descriptions, quantities, and units
- Organize supplies by categories
- Monitor available stock vs. issued quantities

### 📝 Supply Request Workflow
| Step | Description |
|------|-------------|
| **Submit** | Users submit supply requests with quantity and purpose |
| **Review** | Admin reviews pending requests |
| **Approve/Reject** | Admin approves or rejects with remarks |
| **Issue** | Approved requests are issued and tracked |
| **Archive** | Completed requests are archived for records |

### 🔄 Supply Request Journey

| 1️⃣ Submit | 2️⃣ Review | 3️⃣ Decide | 4️⃣ Issue | 5️⃣ Archive |
|-----------|------------|------------|----------|-------------|
| Staff creates a request with quantity and purpose | Admin checks stock availability and request details | Request is approved or rejected with remarks | Approved supplies are released and recorded | Finished transactions are stored for reporting and history |

### 👥 User Management
- Create, edit, and deactivate user accounts
- Assign roles and permissions per user
- Manage office/department affiliations

### 📄 Reports & Records
- Generate supply inventory reports
- Requisition and Issue Slip (RIS) file tracking
- Archived records management
- Request history with full timestamps

### ❓ Help & Support
- User-facing FAQ page
- Admin FAQ for supply management guidance

---

## 🛠 Technology Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP / Laravel Framework |
| **Frontend** | Blade Templates, CSS, JavaScript |
| **Database** | MYSQL Workbench |
| **Auth & Permissions** | Laravel Auth + Spatie Permission |
| **Email** | Laravel Mailer (password reset, notifications) |
| **Session & Cache** | Laravel Session / Cache drivers |

---

## 💻 System Requirements

- **PHP** >= 8.0
- **Composer** (PHP dependency manager)
- **Node.js** & **npm** (for frontend assets)
- **MySQL** >= 5.7 or **MariaDB** >= 10.3 (a running MySQL/MariaDB server is required)
- A web server such as **Apache** or **Nginx** (or use `php artisan serve` for local development)

---

## 🚀 Getting Started

> **Quick Start Flow:** Install dependencies → configure `.env` → migrate and seed the database → launch the Laravel development server.

### 1. Clone the Repository

```bash
git clone https://github.com/MidnightAzzura/PUPT_SMIS-Group-Astral-Angels-.git
cd PUPT_SMIS-Group-Astral-Angels-
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install && npm run build
```

### 4. Set Up Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure the Database

The application uses **MySQL**. Open MySQL Workbench (or any MySQL client) and create a new database, then update your `.env` file with your credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pupt_smis
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password
```

### 6. Run Migrations & Seed the Database

```bash
php artisan migrate --seed
```

### 7. Start the Development Server

```bash
php artisan serve
```

Open your browser at **http://localhost:8000**

---

## 📁 Project Structure

```
PUPT_SMIS-Group-Astral-Angels-/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Application controllers (Auth, Admin, Supply, etc.)
│   │   └── Middleware/        # Custom middleware
│   ├── Models/                # Eloquent models (User, Supply, Requests, etc.)
│   ├── Exports/               # Report export classes
│   ├── Mail/                  # Mailable classes (password reset, notifications)
│   └── Notifications/         # Notification classes
├── config/                    # Application configuration files
├── database/
│   ├── migrations/            # Database schema migrations
│   └── seeders/               # Database seeders
├── public/                    # Publicly accessible assets
│   └── css/                   # Stylesheets
├── resources/
│   ├── css/                   # Additional frontend styles
│   ├── js/                    # Frontend scripts
│   ├── templates/             # Reusable view fragments/templates
│   └── views/                 # Blade templates (UI pages)
├── routes/
│   └── web.php                # All application routes
├── storage/                   # Logs, cache, uploaded files
└── tests/                     # Automated tests
```

---

## 👤 User Roles

| Role | Permissions |
|------|-------------|
| **Administrator** | Full system access — manage users, supplies, requests, roles, and reports |
| **Staff / User** | Submit supply requests, view request history, access FAQs |

Permissions are managed granularly using the **Spatie Laravel Permission** package, supporting fine-grained access control per action (e.g., `add-user`, `edit-supply`, `approve-request`).

<details>
<summary><strong>Role overview</strong></summary>

- **Administrators** keep inventory accurate, process requests, manage users, and maintain records.
- **Staff members** create supply requests, monitor their status, and reference the system FAQs when needed.

</details>

---

## 👩‍💻 Contributors

<div align="center">

<strong>Group Gina's Astral Angels</strong><br>
<em>PUP Taguig</em>

</div>

---

## 📄 License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).

---

<div align="center">

Made with ❤️ by Group Astral Angels · PUPT

</div>
