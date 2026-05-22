# Elder Care Management System

A full-stack web application for managing elderly care with role-based access for **Admin**, **Caregiver**, and **Elderly User** roles.

## Features

- Session-based authentication (register / login / logout)
- **Elderly users**: view and update profile, health details, appointments
- **Caregivers**: view assigned elders, update health status, add health records
- **Admin**: user management, caregiver assignment, appointment management, dashboard statistics

## Tech Stack

| Layer    | Technology                    |
|----------|-------------------------------|
| Frontend | HTML, CSS, JavaScript         |
| Backend  | PHP 8+                        |
| Database | MySQL                         |
| Server   | Apache (XAMPP / WAMP / LAMP)  |

## Project Structure

```
├── public/              # Web root (Apache DocumentRoot)
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── admin/
│   ├── caregiver/
│   ├── elderly/
│   ├── actions/         # Form handlers
│   └── assets/
├── includes/            # Config, database, auth, layout
├── database/            # schema.sql, seed.php
└── docs/
```

## Prerequisites

- PHP 8.0 or higher (with PDO MySQL extension)
- MySQL 5.7+ or MariaDB
- Apache with `mod_rewrite` optional

## Setup (XAMPP)

### 1. Database

1. Start **Apache** and **MySQL** in XAMPP.
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Import [`database/schema.sql`](database/schema.sql) or run it in the SQL tab.
4. Seed demo data from the project root:

```bash
php database/seed.php
```

### 2. Configuration

1. Copy `includes/config.example.php` to `includes/config.php` if needed.
2. Edit database credentials in `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'eldercare_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. Apache document root

Point your virtual host or XAMPP project folder so the **web root is the `public` directory**.

Example: copy/link project to `C:\xampp\htdocs\eldercare\` and set DocumentRoot to `.../eldercare/public`.

Or access via: `http://localhost/eldercare/public/`

Adjust `PUBLIC_URL` in `includes/config.php` if the app is in a subfolder:

```php
define('PUBLIC_URL', '/eldercare/public/');
```

### 4. Open the application

Visit your configured URL, e.g. `http://localhost/eldercare/public/`

## Demo accounts (after seed)

Password for all accounts: **123456**

| Role      | Email                    |
|-----------|--------------------------|
| Admin     | admin@eldercare.com      |
| Caregiver | james.care@eldercare.com |
| Caregiver | maria.care@eldercare.com |
| Elderly   | robert@eldercare.com     |
| Elderly   | eleanor@eldercare.com    |

Admin accounts cannot be created through the public registration form.

## Usage flow

1. Register as **Elderly User** or **Caregiver**, or sign in with a demo account.
2. **Admin** assigns caregivers to elders from the dashboard.
3. **Admin** creates appointments linking elders and caregivers.
4. **Caregivers** update health status and add vitals for assigned elders.
5. **Elderly users** maintain their profile and view appointments and health history.

## License

MIT
