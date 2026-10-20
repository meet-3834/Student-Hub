# Practical 13: PHP JSON API and Fetch CRUD

This small project adds, lists, updates, and deletes students without reloading the page.

## Files

- `database.sql` creates the database and students table.
- `db.php` connects PHP to MySQL.
- `api.php` handles the JSON API requests.
- `index.php` shows the form and uses Fetch API.

## Step-by-step setup

1. Install and open XAMPP. Start **Apache** and **MySQL**.
2. Copy this project folder into `C:\xampp\htdocs\practical13`.
3. Open `http://localhost/phpmyadmin` in your browser.
4. Choose the **SQL** tab, paste the contents of `database.sql`, and run it.
5. If your MySQL username or password is different, update the values at the top of `db.php`.
6. Open `http://localhost/practical13/` in your browser.
7. Add a student using the form. Use **Edit** or **Delete** beside a student to change or remove it.
8. To see the asynchronous requests, open browser developer tools and select the **Network** tab.

## How it works

1. The browser calls `api.php` with `GET` to retrieve the student list.
2. When the form is submitted, JavaScript prevents the normal page submission and sends JSON using `fetch()`.
3. `api.php` checks the input and uses prepared SQL statements to add, update, or delete a student.
4. PHP returns a JSON result. JavaScript displays any message and reloads just the table data.

The API expects JSON such as `{"action":"add","name":"Alex","email":"alex@example.com"}` for a create request.
