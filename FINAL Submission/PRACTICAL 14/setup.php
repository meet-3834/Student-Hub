<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

try {
    $server = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $server->exec(
        'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );

    $pdo = getDatabaseConnection();
    $statements = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'staff', 'student') NOT NULL DEFAULT 'student',
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS students (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            enrolled_at DATE NOT NULL
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            event_date DATE NOT NULL
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS registrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_id INT UNSIGNED NOT NULL,
            event_id INT UNSIGNED NOT NULL,
            registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_registration_student FOREIGN KEY (student_id) REFERENCES students(id),
            CONSTRAINT fk_registration_event FOREIGN KEY (event_id) REFERENCES events(id)
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NULL,
            action VARCHAR(100) NOT NULL,
            details VARCHAR(255) NOT NULL DEFAULT '',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS menu_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            role ENUM('admin', 'staff', 'student') NOT NULL,
            label VARCHAR(100) NOT NULL,
            url VARCHAR(255) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0
        ) ENGINE=InnoDB",
    ];

    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }

    $insertUser = $pdo->prepare(
        'INSERT IGNORE INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)'
    );
    $passwordHash = password_hash('Admin123!', PASSWORD_DEFAULT);
    $sampleUsers = [
        ['Administrator', 'admin@example.com', $passwordHash, 'admin'],
        ['Staff Member', 'staff@example.com', $passwordHash, 'staff'],
        ['Student User', 'student@example.com', $passwordHash, 'student'],
    ];
    foreach ($sampleUsers as $sampleUser) {
        $insertUser->execute($sampleUser);
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn() === 0) {
        $pdo->exec(
            "INSERT INTO students (name, email, enrolled_at) VALUES
                ('Asha Kumar', 'asha@example.com', '2026-01-10'),
                ('Ravi Shah', 'ravi@example.com', '2026-02-15'),
                ('Maya Singh', 'maya@example.com', '2026-03-20')"
        );
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn() === 0) {
        $pdo->exec(
            "INSERT INTO events (name, event_date) VALUES
                ('Web Design Workshop', '2026-11-05'),
                ('Science Fair', '2026-11-18'),
                ('Sports Day', '2026-12-02')"
        );
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn() === 0) {
        $students = $pdo->query('SELECT id FROM students ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
        $events = $pdo->query('SELECT id FROM events ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
        $insertRegistration = $pdo->prepare(
            'INSERT INTO registrations (student_id, event_id) VALUES (?, ?)'
        );
        foreach ($students as $index => $studentId) {
            $insertRegistration->execute([$studentId, $events[$index % count($events)]]);
        }
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM menu_items')->fetchColumn() === 0) {
        $insertMenu = $pdo->prepare(
            'INSERT INTO menu_items (role, label, url, sort_order) VALUES (?, ?, ?, ?)'
        );
        $menuItems = [
            ['admin', 'Dashboard', '#overview', 1],
            ['admin', 'Users', '#users', 2],
            ['admin', 'Audit Log', '#audit', 3],
            ['staff', 'Staff Home', '#overview', 1],
            ['staff', 'My Profile', '#profile', 2],
            ['student', 'Student Home', '#overview', 1],
            ['student', 'My Events', '#profile', 2],
        ];
        foreach ($menuItems as $menuItem) {
            $insertMenu->execute($menuItem);
        }
    }

    $success = true;
} catch (Throwable $error) {
    $success = false;
    $errorMessage = $error->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set up Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width: 680px;">
    <section class="card shadow-sm">
        <div class="card-body p-4">
            <?php if ($success): ?>
                <h1 class="h3">Setup complete</h1>
                <p>The database and sample data are ready.</p>
                <p><strong>Admin:</strong> admin@example.com / Admin123!</p>
                <p><strong>Staff:</strong> staff@example.com / Admin123!</p>
                <p><strong>Student:</strong> student@example.com / Admin123!</p>
                <p class="text-danger">For safety, remove or rename setup.php after setup.</p>
                <a class="btn btn-primary" href="login.php">Go to login</a>
            <?php else: ?>
                <h1 class="h3 text-danger">Setup failed</h1>
                <p>Check the database settings in config.php and confirm MySQL is running.</p>
                <pre class="text-danger"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></pre>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
