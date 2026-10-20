# Practical 14: Admin Dashboard

A basic PHP and MySQL dashboard with role-based access, database statistics, latest registrations, and an audit log.

## Run it with XAMPP

1. Install and open XAMPP. Start **Apache** and **MySQL**.
2. Copy this whole project folder into `C:\xampp\htdocs\` and name the copied folder `practical14`.
3. If your MySQL username or password is different from the XAMPP defaults, update `DB_USER` and `DB_PASSWORD` in `config.php`.
4. In your browser, open `http://localhost/practical14/setup.php`. Wait for **Setup complete**.
5. Delete `setup.php` from the copied project folder after setup.
6. Open `http://localhost/practical14/login.php` and sign in with one of these sample accounts:

   | Role | Email | Password |
   | --- | --- | --- |
   | Admin | admin@example.com | Admin123! |
   | Staff | staff@example.com | Admin123! |
   | Student | student@example.com | Admin123! |

The staff and student accounts can sign in, but they cannot see the admin statistics. Their menu is loaded for their role from the `menu_items` table.

## What to try

- Sign in as the admin to see the user, student, event, and registration counts.
- Check the latest registrations and audit log.
- Sign out, then sign in as staff or student to see the access restriction and their role-specific menu.
- Sign in as admin again; the login, dashboard view, and logout are recorded in `audit_logs`.

This sample is for local coursework practice. Change the sample passwords and database settings before using it anywhere public.
