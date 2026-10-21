# RegistrarConnect — PHP + MySQL starter project

## Requirements
- Windows with XAMPP (Apache + MySQL/MariaDB)
- VS Code
- A modern browser

## Installation
1. Extract the `registrarconnect` folder into `C:\xampp\htdocs\registrarconnect`.
2. Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin`.
4. Select **Import**, choose `setup.sql`, then click **Import/Go**. The script creates the `registrarconnect` database, tables, and five document types.
5. Check `config/config.php`. The default XAMPP MySQL user is `root` with a blank password. Change these values if your installation differs.
6. Visit `http://localhost/registrarconnect/setup_admin.php` once to create the initial registrar account.
7. **Immediately delete `setup_admin.php`** after it succeeds. Do not leave the setup script on a deployed server.
8. Visit `http://localhost/registrarconnect/auth/login.php`.
9. Registrar login: `registrar.admin@celtech.edu.ph` / `ChangeMe123!`. Log in and change this temporary password immediately using Profile → Change Password.
10. Create a student account using **Create an account**.

## Main routes
- `/auth/register.php` — student registration
- `/auth/login.php` — login
- `/student/dashboard.php` — student dashboard
- `/student/request.php` — submit document request
- `/student/requests.php` — request history
- `/student/details.php?id=1` — request details and status history
- `/registrar/dashboard.php` — registrar dashboard
- `/registrar/requests.php` — filter and review all requests
- `/registrar/request_details.php?id=1` — update status and remarks

## Project structure
- `config/` — database connection and settings
- `includes/` — authentication helpers, shared layout, and request table
- `auth/` — register, login, logout
- `student/` — student pages and request workflow
- `registrar/` — staff dashboard and request processing
- `assets/style.css` — responsive UI styling
- `setup.sql` — schema and document types

## Status workflow
Pending → Under Review → Processing → Ready for Release → Completed. Rejected is an available terminal status. Registrar staff select the status explicitly; this starter does not enforce a strict transition graph.

## Security and production notes
- Passwords are hashed with PHP `password_hash()` and checked with `password_verify()`.
- Database calls use PDO prepared statements.
- Forms use session CSRF tokens and HTML output escaping.
- Role checks protect student and registrar pages; students can only open their own request details.
- For production, use HTTPS, set secure session-cookie settings, create a least-privilege database account, implement rate limiting and password reset/change, add audit logging and backups, and review privacy requirements before entering real student data.
- This is an educational starter, not a fully audited production system. Automated email/SMS, payment gateway, SIS integration, file uploads, and digital document issuance are not included.
