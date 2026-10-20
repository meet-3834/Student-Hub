# CHARUSAT Student Portal — Lab Practicals Implementation

## Project Overview
This is a modern, responsive, accessible student hub web portal for Charotar University of Science and Technology (CHARUSAT). The project implements coursework practicals progressively into a unified, multi-page web application using semantic HTML5, CSS3, modern JavaScript (ES6+), PHP, and MySQL.

---

## 📑 Lab Practicals & File Mapping Index

The table below maps each project file to its corresponding lab practical:

| Practical Number & Title | Key Files Belonging to this Practical | Purpose & Features Implemented |
| :--- | :--- | :--- |
| **Practical 4**<br>JavaScript DOM Manipulation, Event Handling & UI Interactivity | • [index.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/index.html)<br>• [dashboard.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/dashboard.html)<br>• [script.js](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/script.js) (Sections 1–6)<br>• [style.css](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/style.css) | • Light/Dark theme switcher with `localStorage` persistence<br>• Dismissable Notification Banner with preference stored in `localStorage`<br>• Mobile responsive Hamburger Navigation Menu<br>• Content Slider with Next/Previous navigation controls<br>• Collapsible FAQ accordion cards (`+`/`-` toggle)<br>• Interactive Modal Popup on Dashboard ("View Important Update")<br>• *Intermediate Extension:* Smooth transition effects on cards &amp; buttons<br>• *Advanced Extension:* Store user UI preferences in `localStorage` and restore on load |
| **Practical 5**<br>Student Registration Form with Frontend Validation | • [register.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/register.html)<br>• [script.js](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/script.js) (Section 7)<br>• [style.css](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/style.css) (Lines 660–750) | • Form inputs: Name, Email, Mobile, Password, Confirm Password, Course, Year, Gender, Terms acceptance<br>• Regular Expressions for field validation<br>• Inline validation error messages (`<span class="error-msg">`) displayed near fields<br>• Dynamic visual **Password Strength Meter** (Weak / Medium / Strong)<br>• *Intermediate Extension:* Real-time validation on `input`, `keyup`, and `change` events<br>• *Advanced Extension:* Custom **Canvas CAPTCHA** with random noise lines, dots, distortion, and verification |
| **Practical 6**<br>Rendering External JSON Data using Fetch API, Search & Filter | • [events.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/events.html)<br>• [students.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/students.html)<br>• [faq.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/faq.html)<br>• [dashboard.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/dashboard.html)<br>• `data/events.json` (16 records)<br>• `data/students.json` (16 records)<br>• `data/faqs.json` (16 records)<br>• `data/locations.json`<br>• [script.js](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/script.js) (Practical 6 modules) | • Asynchronous data consumption via **Fetch API**<br>• Dynamic card rendering using `map()` and template literals<br>• Real-time search (`filter()`), category filtering, and multi-field sorting (`sort()`)<br>• Client-side pagination (`slice()`) with Previous/Next and page number controls<br>• Loading spinners, empty states, and error handling with "Retry" buttons<br>• *Intermediate Extension:* Cascading Dependent Location Dropdowns (Country &rarr; State &rarr; City)<br>• *Advanced Extension:* `localStorage` caching for offline-ready views with cache/live badge &amp; refresh button |
| **Practical 7**<br>PHP Form Processing with Server-Side Validation & File Storage | • [contact.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/contact.php)<br>• [process.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/process.php)<br>• `data/records.csv`<br>• `data/records.json`<br>• [contact.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/contact.html) | • POST submission handling for inquiry/contact data<br>• Server-side input sanitization (`htmlspecialchars`) and validation (Email filter, Name &amp; Phone regex)<br>• Safe file storage in both **CSV** (`fputcsv`) and **JSON** (`LOCK_EX`)<br>• Clear success and error alert banners<br>• *Intermediate Extension:* Live display of stored CSV/JSON records in a responsive table on [contact.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/contact.php)<br>• *Advanced Extension:* Cryptographic **CSRF Token Validation** preventing cross-site request forgery |
| **Practical 8**<br>MySQL Schema Design, ER Model, PDO Connectivity & Prepared Statements | • [schema.sql](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/schema.sql)<br>• [db.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/db.php)<br>• [setup.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/setup.php)<br>• [test_db.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/test_db.php)<br>• [students_api.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/students_api.php)<br>• [auth.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/auth.php) | • 3NF Normalized relational schema with `students`, `events`, and `registrations` (junction table)<br>• Foreign key constraints with `ON DELETE CASCADE` ensuring referential integrity<br>• Secure PHP PDO connection singleton with `ERRMODE_EXCEPTION` and emulated prepares disabled<br>• Parameterized prepared statement execution preventing SQL injection<br>• *Intermediate Extension:* PHP PDO and Prepared Statements across all queries<br>• *Advanced Extension:* MySQL **Stored Procedures** (`sp_RegisterStudentForEvent` and `sp_GetStudentRegistrations`)<br>• Screenshot-ready live connection test dashboard on [test_db.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/test_db.php) |
| **Practical 9**<br>Secure User Registration | • [register.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/register.php)<br>• [auth.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/auth.php)<br>• [setup.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/setup.php) | • Frontend and backend validation, duplicate email protection, prepared database insert, `password_hash()`, and audit log |
| **Practical 10**<br>Secure Login, Sessions & Role-Based Access | • [login.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/login.php)<br>• [logout.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/logout.php)<br>• [student-dashboard.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/student-dashboard.php)<br>• [admin-dashboard.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/admin-dashboard.php)<br>• [auth.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/auth.php) | • `password_verify()`, regenerated session IDs, role-based dashboards, protected pages, 15-minute idle timeout, secure logout, remember-me tokens, and audit logs |
| **Site Navigation & Overview** | • [sitemap.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/sitemap.html)<br>• [about.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/about.html)<br>• [profile.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/profile.html)<br>• [login.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/login.php) | • Comprehensive site map linking to all practical demonstrations<br>• Institutional information, student profile, and role-based login portal |

---

## 🚀 How to Run the Project

### Option A: Testing Public Frontend Pages (Practicals 4, 5, 6)
You can open the static pages directly in any browser (or via VS Code Live Server):
- [index.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/index.html) &mdash; Practical 4 (Theme Switcher, Notification Banner, Content Slider, Hamburger Menu)
- [register.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/register.html) &mdash; Practical 5 (Full Registration Form, Real-Time Regex Validation, Password Strength Meter, Canvas CAPTCHA)
- [events.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/events.html) &mdash; Practical 6 (Fetch API, Search, Filter, Sort, Pagination)
- [students.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/students.html) &mdash; Practical 6 (Student Profiles, Dependent Dropdowns Country &rarr; State &rarr; City)
- [faq.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/faq.html) &mdash; Practical 6 (Dynamic FAQs, Collapsible Accordions)
- [dashboard.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/dashboard.html) &mdash; Practical 4 &amp; 6 (Interactive Dashboard &amp; Modal Popup)
- [sitemap.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/sitemap.html) &mdash; Full practical mapping index

### Option B: Testing PHP & MySQL Backend Modules (Practicals 7–10)
1. **Start Apache & MySQL** in the XAMPP Control Panel.
2. In phpMyAdmin, create a database named `charusat_portal` and import the included [schema.sql](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/schema.sql).
3. Alternatively, initialize the database via CLI:
   ```powershell
   C:\xampp\php\php.exe setup.php
   ```
4. Start the local PHP server in the project directory:
   ```powershell
   C:\xampp\php\php.exe -S 127.0.0.1:8000
   ```
5. Open your browser:
   - **Practical 7 (CSV/JSON Storage &amp; CSRF):** `http://127.0.0.1:8000/contact.php`
   - **Practical 8 (Connection &amp; Schema Test):** `http://127.0.0.1:8000/test_db.php` (take screenshot here for lab submission)
   - **Practical 9 (Secure Registration):** `http://127.0.0.1:8000/register.php`
   - **Practical 10 (Secure Login &amp; Sessions):** `http://127.0.0.1:8000/login.php`
     - Default Admin: `admin@charusat.edu.in` / `AdminPass123!`
     - Default Student: `student@charusat.edu.in` / `StudentPass123!`

### How to See Practical 9 Output
1. Start Apache and MySQL, initialize the database with `C:\xampp\php\php.exe setup.php`, then start the PHP server as above.
2. Open `http://127.0.0.1:8000/register.php`.
3. Click **Case 1: Valid New User**, then submit. The success message confirms that the account was saved with a hashed password.
4. Click **Case 2: Duplicate Email**, then submit. The page shows the duplicate-email warning for the seeded student account.
5. Click **Case 3: Mismatched Passwords**, then submit. The page displays the server-side password mismatch validation.
6. For audit-log evidence, sign in as admin and open `http://127.0.0.1:8000/admin-dashboard.php`.

### How to See Practical 10 Output
1. Open `http://127.0.0.1:8000/login.php`. Use the buttons to fill the seeded student/admin accounts or an incorrect password.
2. Sign in as student or admin to see the corresponding role-based dashboard.
3. Sign in as student and use **Test Unauthorized Access** to see the 403 page for the admin dashboard.
4. On the student dashboard, use **Simulate Session Timeout** to see the timeout message on the login page.
5. Use **Secure Logout** to verify the session and remember-me cookie are cleared.
6. To test the actual 15-minute idle timeout, remain inactive for 15 minutes, then reload a protected dashboard.

---

## 🔬 Practical Details & Viva Guide

### Practical 4: JavaScript DOM Manipulation, Event Handling & UI Interactivity
- **DOM Selection & Modification:** Uses `document.querySelector()`, `document.getElementById()`, `document.createElement()`, and `element.appendChild()`.
- **Event Handling:** Attached via `addEventListener()` for `click`, `input`, `change`, and `DOMContentLoaded`.
- **localStorage:** Remembers light/dark theme preference (`localStorage.getItem('theme')`) and dismissed banner status (`localStorage.getItem('notification_dismissed')`).
- **Extensions:** Smooth card/button hover transition animations in [style.css](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/style.css); UI preferences restored on page load.

### Practical 5: Form Validation & User-Friendly Error Handling
- **HTML5 Input Types:** `type="text"`, `type="email"`, `type="tel"`, `type="password"`, `type="radio"`, `type="checkbox"`, `<select>`, and `required`.
- **Regular Expressions:**
  - Name: `/^[A-Za-z\s]{3,50}$/`
  - Email: `/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/`
  - Mobile: `/^[6-9][0-9]{9}$/` (10-digit Indian standard)
- **Password Strength:** Evaluates character count, uppercase letters, numbers, and special characters; dynamically updates `#strengthBar` width and color (red/yellow/green) and text (Weak/Medium/Strong).
- **Extensions:**
  - *Intermediate:* Real-time field validation on `input`, `keyup`, and `change` events.
  - *Advanced:* Custom **HTML5 Canvas CAPTCHA** with noisy background, angle distortion, and character validation.

### Practical 6: Fetch API, Search, Filter & Pagination
- **Fetch & Parse:** `fetch(url)` returns a promise resolving to `.json()`.
- **Array Methods:**
  - `Array.prototype.filter()`: For query string search and category dropdown filters.
  - `Array.prototype.sort()`: For ordering by CGPA, date, or name.
  - `Array.prototype.slice()`: For pagination calculations `(page - 1) * limit` to `page * limit`.
  - `Array.prototype.map()`: For generating HTML card components.
- **Extensions:**
  - *Intermediate:* Cascading Dependent Location Dropdowns (Country &rarr; State &rarr; City) in [students.html](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/students.html).
  - *Advanced:* Automatic `localStorage` caching with cache indicator badge and fresh fetch button.

### Practical 7: PHP Form Processing & CSV/JSON Storage
- **POST Submission:** Form data is submitted securely via HTTP POST.
- **Sanitization & Validation:**
  - `htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8')`
  - `filter_var($email, FILTER_VALIDATE_EMAIL)`
  - Regex checks for name, phone, subject, and message.
- **Safe Storage:** CSV writing via `fputcsv()`; JSON storage using `file_put_contents(..., LOCK_EX)`.
- **Extensions:**
  - *Intermediate:* Stored records are displayed dynamically in a table on [contact.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/contact.php).
  - *Advanced:* Cryptographic CSRF token generation (`bin2hex(random_bytes(32))`) and validation (`hash_equals()`).

### Practical 8: MySQL Schema, Normalized ER Model, PDO & Stored Procedures
- **Normalization:** 3NF normalized tables: `students` (1) &harr; (N) `registrations` (N) &harr; (1) `events`.
- **Key Constraints:** Primary keys and foreign keys with `ON DELETE CASCADE ON UPDATE CASCADE`.
- **PDO Security:**
  - `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
  - `PDO::ATTR_EMULATE_PREPARES => false`
- **Extensions:**
  - *Intermediate:* Parameterized PDO prepared statements used in [test_db.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/test_db.php) and [students_api.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/students_api.php).
  - *Advanced:* MySQL Stored Procedures:
    - `sp_RegisterStudentForEvent`: Verifies student and event existence, checks capacity, checks duplicate registration, inserts into junction table, and increments registration count.
    - `sp_GetStudentRegistrations`: Performs a multi-table normalized join across `registrations`, `students`, and `events`.