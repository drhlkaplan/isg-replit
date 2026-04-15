# İSG Eğitim Platformu — Turkish Occupational Health & Safety LMS

## Overview

A Turkish ISG (İş Sağlığı ve Güvenliği — Occupational Health & Safety) Online Training Platform. This is an LMS (Learning Management System) designed to deliver, track, and certify safety training in compliance with Turkish regulations (Law No. 6331).

## Tech Stack

- **Backend:** PHP 8.2 (native PHP with a custom MVC-like structure)
- **Database:** MySQL 8.0 (running locally via socket)
- **Frontend:** HTML5, CSS3, Vanilla JavaScript + Bootstrap 5
- **SCORM:** Supports SCORM 1.2 and 2004 content packages
- **PDF Generation:** FPDF, TCPDF (for certificates)
- **QR Codes:** endroid/qr-code (for certificate verification)
- **Spreadsheets:** PHPOffice/PhpSpreadsheet (for reporting)
- **Package Manager:** Composer

## Project Structure

```
/
├── index.php          - Main entry point / router
├── router.php         - PHP built-in server router (static file serving)
├── config.php         - App configuration (DB, URLs, paths)
├── start.sh           - Startup script (MySQL + PHP server)
├── controllers/       - MVC controllers by role/feature
├── src/               - Core classes (DB, Auth, SCORM, Audit, etc.)
├── views/             - PHP templates (admin/, student/, firma/, public/)
├── assets/            - Static files (CSS, JS)
├── db/                - Database SQL files
│   ├── schema.sql     - Full database schema + seed data
│   └── seed_courses.sql - Course seed data
├── scorm/             - SCORM package storage
├── uploads/           - User uploads (scorm/, certificates/, thumbnails/, logos/)
└── vendor/            - Composer dependencies
```

## User Roles

1. **superadmin** — Full platform control
2. **admin** — Training management
3. **firm** — Company representative
4. **student** — Learner/trainee
5. **egitmen** — Instructor

## Running the Application

The `start.sh` script handles everything:
1. Initializes MySQL data directory (if needed)
2. Starts MySQL server on port 3306 via Unix socket at `/home/runner/mysql_run/mysql.sock`
3. Creates and seeds the `isg_lms` database (if needed)
4. Creates required upload directories
5. Starts PHP built-in server at `0.0.0.0:5000`

```bash
bash start.sh
```

## Database Configuration

- **DB Name:** `isg_lms`
- **DB User:** `root` (no password in dev)
- **Connection:** Via Unix socket `/home/runner/mysql_run/mysql.sock` (Replit environment)
- **MySQL Data:** `/home/runner/mysql_data/`
- **Logs:** `/home/runner/mysql_logs/`

## Key Features

- Multi-role authentication system
- SCORM 1.2/2004 content delivery
- Automated PDF certificate generation
- QR code certificate verification
- Compliance training packages (3 risk levels × temel/tekrar)
- Company (firm) white-labeling
- Audit logging
- Excel/PDF reporting

## Deployment

Configured as a `vm` deployment type (always-running) since it needs a local MySQL instance.
Run command: `bash start.sh`
