# Hawassa Luxury Real Estate Portal

This repository contains a complete PHP/MySQL real estate portal for premium listings, agent management, client messaging, and administrative controls.

The system is built for local deployment and academic demonstration, with distinct user roles, responsive front-end pages, and backend API endpoints.

---

## 📖 Table of Contents
1. [Overview](#-overview)
2. [User Roles](#-user-roles)
3. [Core Features](#-core-features)
4. [Architecture & Components](#-architecture--components)
5. [Project Structure](#-project-structure)
6. [Installation & Setup](#-installation--setup)
7. [Configuration](#-configuration)
8. [Usage](#-usage)
9. [Database Schema](#-database-schema)
10. [Security](#-security)
11. [Troubleshooting](#-troubleshooting)
12. [Developer Notes](#-developer-notes)

---

## 🌟 Overview

Hawassa Luxury Real Estate is a modern web application for showcasing luxury properties, enabling customer-agent communications, and administering listings.

The application includes:
* Public-facing property search and listing discovery
* Registered client portal for saved searches, saved properties, and chat
* Agent portal for managing assigned properties and conversations
* Admin dashboard for site and content management

---

## 👥 User Roles

### Guests
* Browse listings
* Read blog posts
* View announcements
* Use search filters

### Registered Clients
* Save favorite properties
* Save searches
* Chat with agents
* Access personal dashboard

### Agents
* Access agent dashboard
* Review assigned listings
* Manage active conversations
* Write blog posts via the advanced admin portal

### Administrators
* Manage users, agents, properties, blogs, announcements, and testimonials
* Monitor dashboard analytics
* Configure site settings

---

## ✨ Core Features

* Responsive landing page with hero slider and featured listings
* Property search with filters for type, location, and status
* Leaflet-based map integration for property coordinates
* User account registration and secure login
* Agent/client chat interface
* Saved properties and saved searches functionality
* Admin portal with CRUD operations and analytics
* Custom 404 page and security rules via `.htaccess`

---

## 🏗 Architecture & Components

### Frontend
* HTML5 semantic structure
* CSS3 with responsive media queries
* JavaScript for interactivity, AJAX, and visual enhancements

### Backend
* PHP for page rendering and business logic
* PDO for secure database access
* REST-style API endpoints in `api/`

### Database
* MySQL relational database
* `database.sql` schema export
* `setup/install.php` to create tables and load demo data

---

## 📁 Project Structure

```text
realstate/
├── .htaccess
├── 404.php
├── admin/
│   ├── agents.php
│   ├── announcements.php
│   ├── announcement_form.php
│   ├── blog.php
│   ├── blog_form.php
│   ├── chat.php
│   ├── chats.php
│   ├── dashboard.php
│   ├── index.php
│   ├── logout.php
│   ├── properties.php
│   ├── property_form.php
│   ├── testimonials.php
│   ├── testimonial_form.php
│   └── users.php
├── api/
│   ├── agents.php
│   ├── announcements.php
│   ├── blog.php
│   ├── chat.php
│   ├── contact.php
│   ├── output.txt
│   ├── properties.php
│   ├── save_property.php
│   ├── save_search.php
│   ├── search.php
│   ├── testimonials.php
│   └── users.php
├── assets/
│   ├── css/
│   │   ├── luxury.css
│   │   ├── responsive.css
│   │   └── style.css
│   ├── images/
│   │   └── uploads/properties/
│   └── js/
│       ├── admin.js
│       ├── main.js
│       └── search.js
├── includes/
│   ├── agent_footer.php
│   ├── agent_header.php
│   ├── auth.php
│   ├── bootstrap.php
│   ├── config.php
│   ├── core.php
│   ├── database.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   ├── images.php
│   ├── mailer.php
│   └── PHPMailer/
├── outside/
│   ├── check_admin.php
│   ├── check_time.php
│   ├── index.php
│   ├── migrations_combined.php
│   ├── run_migrations.php
│   ├── run_seeds.php
│   └── seeds_combined.php
├── pages/
│   ├── about.php
│   ├── agent_dashboard.php
│   ├── agents.php
│   ├── announcements.php
│   ├── blog.php
│   ├── chat.php
│   ├── dashboard.php
│   ├── home.php
│   ├── login.php
│   ├── logout.php
│   ├── payment_demo.php
│   ├── property.php
│   ├── register.php
│   ├── search.php
│   └── verify.php
├── scripts/
│   ├── create_demo_users.php
│   └── create_test_users.php
├── setup/
│   └── install.php
├── database.sql
├── index.php
├── list_admins.php
├── list_agents.php
├── list_users.php
├── migrate_rebrand_hawassa.php
├── test_mail.php
└── README.md
```

---

## ⚙️ Installation & Setup

### Requirements
* PHP 8+ with PDO extension
* MySQL / MariaDB
* Apache web server
* Local server stack like Ampps, XAMPP, or WampServer

### Installation Steps

1. Copy the `realstate` folder into the server root.
2. Set file permissions where required.
3. Create or update the MySQL database.
4. Open `includes/config.php` and update credentials.
5. Run `setup/install.php` in your browser.
6. Visit `http://localhost/realstate/`.

### Example Database Config
Update `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'bhs_clone');
define('DB_USER', 'root');
define('DB_PASS', 'mysql');
define('DB_CHARSET', 'utf8mb4');

define('SITE_URL', 'http://localhost/realstate');
```

> Note: Replace the SMTP credentials with your own if you want email support.

---

## 🧩 Setup Script

The installer in `setup/install.php` will:
* Create the `bhs_clone` database if it does not exist
* Create all main tables
* Insert demo users, categories, properties, blog posts, testimonials, and site settings
* Redirect to the home page once complete

Use the installer only once per installation.

---

## 🚀 How to Run

### Homepage
* `http://localhost/realstate/`
* Root `index.php` loads `outside/index.php`.

### Admin access
* `http://localhost/realstate/admin/index.php`

### Agent portal
* `http://localhost/realstate/pages/agent_dashboard.php`

### User pages
* Search: `http://localhost/realstate/pages/search.php`
* Property detail: `http://localhost/realstate/pages/property.php?slug=<slug>`
* Login: `http://localhost/realstate/pages/login.php`
* Register: `http://localhost/realstate/pages/register.php`

---

## 🗄️ Database Tables Overview

### Main tables
* `users` — authentication, profile, and role data
* `properties` — listing details, pricing, coordinates, status
* `categories` — listing categories and neighborhoods
* `blog_posts` — published and draft blog content
* `testimonials` — client reviews
* `announcements` — special event or promotion records
* `site_settings` — key/value configuration values

### Relationship tables
* `property_features` — feature list for each property
* `saved_properties` — bookmarked listings by users
* `saved_searches` — saved search filters by users
* `chat_messages` — agent/client chat histories

---

## 🔒 Security Notes

* Uses PDO prepared statements for database access.
* Uses `htmlspecialchars()` in page rendering to prevent XSS.
* Protects include files via `.htaccess`.
* Session handling begins in `includes/config.php` and `includes/core.php`.

---

## 🛠️ Developer Notes

### Key files
* `includes/functions.php` — helper functions and role checks
* `includes/auth.php` — authentication flows
* `admin/dashboard.php` — admin dashboard summary
* `pages/agent_dashboard.php` — agent user dashboard
* `assets/css/responsive.css` — responsive behavior and mobile fixes

### Improvements
* Add built-in agent role in the database schema
* Improve chat persistence and message loading
* Add richer media upload handling for property galleries
* Add stronger validation and form sanitization
* Document API endpoints with example requests

---

## 🧪 Testing & Troubleshooting

### Common issues
* **404 page** — verify `404.php` exists and `.htaccess` contains `ErrorDocument 404 /404.php`
* **Database error** — verify MySQL credentials and database name in `includes/config.php`
* **Login issues** — verify session is started and `user_role` is correctly set by `auth.php`
* **Admin portal broken** — ensure `admin/index.php` correctly redirects to `admin/dashboard.php`

### Default admin account
* Email: `admin@bhsusa.com`
* Password: `admin123`

---

## 📌 Notes

This README is designed to capture the entire project from start to finish:
* structure
* setup
* core pages
* database overview
* usage guide
* troubleshooting

If you want the README extended with schema diagrams, workflow diagrams, or developer task lists, I can add those as well.
