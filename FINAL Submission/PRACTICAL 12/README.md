# Event Manager

A small PHP/MySQL admin app for event create, view, update, and delete, with poster validation and status/category filters.

## Run with XAMPP

1. Copy this folder into `xampp/htdocs`.
2. Import `database.sql` in phpMyAdmin.
3. Open `config.php`. Set a private `ADMIN_PASSWORD` (and change `ADMIN_USERNAME` if desired). Set the database values if your MySQL setup differs.
4. Start Apache and MySQL, then visit `http://localhost/practical%2012/`.

Posters are optional and limited to JPG, PNG, or WEBP images up to 2 MB. Uploads are saved with random filenames in `uploads/`.
