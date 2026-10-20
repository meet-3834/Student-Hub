<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$adminEmail = getenv('PORTAL_ADMIN_EMAIL') ?: 'admin@charusat.edu.in';
$adminPassword = getenv('PORTAL_ADMIN_PASSWORD') ?: 'AdminPass123!';
$studentEmail = getenv('PORTAL_STUDENT_EMAIL') ?: 'student@charusat.edu.in';
$studentPassword = getenv('PORTAL_STUDENT_PASSWORD') ?: 'StudentPass123!';

if (strtolower($adminEmail) === strtolower($studentEmail)) {
    fwrite(STDERR, "Administrator and student accounts must use different email addresses.\n");
    exit(1);
}

$host = getenv('PORTAL_DB_HOST') ?: '127.0.0.1';
$name = getenv('PORTAL_DB_NAME') ?: 'charusat_portal';
$user = getenv('PORTAL_DB_USER') ?: 'root';
$password = getenv('PORTAL_DB_PASSWORD') !== false ? (string)getenv('PORTAL_DB_PASSWORD') : '';

try {
    $initPdo = new PDO(sprintf('mysql:host=%s;charset=utf8mb4', $host), $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $initPdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (Throwable $e) {
    // Fall back to direct db() call
}

$pdo = db();

// 1. Create users table
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS users (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('student', 'admin') NOT NULL,
        last_login_at DATETIME NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

// 2. Create remember_tokens table
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS remember_tokens (
        selector CHAR(24) NOT NULL PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        validator_hash CHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

// 3. Create students table
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS students (
        id VARCHAR(24) NOT NULL PRIMARY KEY,
        roll_no VARCHAR(32) NOT NULL UNIQUE,
        name VARCHAR(120) NOT NULL,
        department VARCHAR(120) NOT NULL,
        semester TINYINT UNSIGNED NOT NULL,
        cgpa DECIMAL(4,2) NOT NULL,
        email VARCHAR(190) NOT NULL,
        country VARCHAR(80) NOT NULL,
        state VARCHAR(80) NOT NULL,
        city VARCHAR(80) NOT NULL,
        skills JSON NOT NULL,
        avatar_initials VARCHAR(8) NOT NULL,
        avatar_color CHAR(7) NOT NULL,
        status VARCHAR(24) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

// 4. Create events table (Practical 8)
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS events (
        id VARCHAR(24) NOT NULL PRIMARY KEY,
        title VARCHAR(190) NOT NULL,
        category VARCHAR(60) NOT NULL,
        event_date DATE NOT NULL,
        event_time VARCHAR(60) NOT NULL,
        venue VARCHAR(190) NOT NULL,
        organizer VARCHAR(120) NOT NULL,
        capacity INT UNSIGNED NOT NULL DEFAULT 100,
        registered_count INT UNSIGNED NOT NULL DEFAULT 0,
        description TEXT NOT NULL,
        tags JSON NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

// 5. Create registrations table (Practical 8 Normalized junction with FKs)
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS registrations (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(24) NOT NULL,
        event_id VARCHAR(24) NOT NULL,
        registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        status ENUM('confirmed', 'waitlisted', 'cancelled') NOT NULL DEFAULT 'confirmed',
        remarks VARCHAR(255) NULL DEFAULT 'Self-registered via Portal',
        CONSTRAINT fk_reg_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_reg_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE ON UPDATE CASCADE,
        UNIQUE KEY unique_student_event (student_id, event_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

// 6. Create audit_logs table (Practical 9 Advanced Extension)
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS audit_logs (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(190) NOT NULL,
        action VARCHAR(60) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent VARCHAR(255) NULL,
        status ENUM('SUCCESS', 'FAILED') NOT NULL,
        details VARCHAR(255) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

// Seed user accounts
$userInsert = $pdo->prepare(
    'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), role = VALUES(role)'
);
$userInsert->execute(['Portal Administrator', strtolower($adminEmail), password_hash($adminPassword, PASSWORD_DEFAULT), 'admin']);
$userInsert->execute(['Portal Student', strtolower($studentEmail), password_hash($studentPassword, PASSWORD_DEFAULT), 'student']);

// Seed students
$studentCount = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
if ($studentCount === 0) {
    $records = json_decode((string) file_get_contents(__DIR__ . '/data/students.json'), true, 512, JSON_THROW_ON_ERROR);
    $insert = $pdo->prepare(
        'INSERT INTO students (id, roll_no, name, department, semester, cgpa, email, country, state, city, skills, avatar_initials, avatar_color, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $pdo->beginTransaction();
    try {
        foreach ($records as $record) {
            $insert->execute([
                $record['id'], $record['rollNo'], $record['name'], $record['department'], $record['semester'],
                $record['cgpa'], $record['email'], $record['country'], $record['state'], $record['city'],
                json_encode($record['skills'], JSON_THROW_ON_ERROR), $record['avatarInitials'],
                $record['avatarColor'], $record['status'],
            ]);
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

// Seed events
$eventCount = (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
if ($eventCount === 0) {
    $events = json_decode((string) file_get_contents(__DIR__ . '/data/events.json'), true, 512, JSON_THROW_ON_ERROR);
    $evtInsert = $pdo->prepare(
        'INSERT INTO events (id, title, category, event_date, event_time, venue, organizer, capacity, registered_count, description, tags)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $pdo->beginTransaction();
    try {
        foreach ($events as $e) {
            $evtInsert->execute([
                $e['id'], $e['title'], $e['category'], $e['date'], $e['time'],
                $e['venue'], $e['organizer'], $e['capacity'], $e['registered'] ?? 0,
                $e['description'], json_encode($e['tags'] ?? [], JSON_THROW_ON_ERROR),
            ]);
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

// Seed sample registrations
$regCount = (int) $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
if ($regCount === 0) {
    $sampleRegs = [
        ['STU-001', 'EVT-101', 'confirmed', 'Technical Symposium Delegate'],
        ['STU-001', 'EVT-102', 'confirmed', 'Hackathon Team Lead'],
        ['STU-002', 'EVT-102', 'confirmed', 'Hackathon Frontend Dev'],
        ['STU-002', 'EVT-103', 'confirmed', 'Web Architecture Workshop Attendee'],
        ['STU-003', 'EVT-101', 'confirmed', 'Coding Track Participant'],
        ['STU-004', 'EVT-104', 'confirmed', 'Cloud Computing Seminar'],
        ['STU-005', 'EVT-105', 'waitlisted', 'Cybersecurity Workshop Waiting List'],
        ['STU-006', 'EVT-106', 'confirmed', 'AI Summit Attendee'],
    ];
    $regInsert = $pdo->prepare(
        'INSERT INTO registrations (student_id, event_id, status, remarks) VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE status = VALUES(status)'
    );
    foreach ($sampleRegs as $sr) {
        $regInsert->execute($sr);
    }
}

// Practical 8 Advanced Extension: Create Stored Procedures
$pdo->exec("DROP PROCEDURE IF EXISTS sp_RegisterStudentForEvent");
$pdo->exec(
    "CREATE PROCEDURE sp_RegisterStudentForEvent(
        IN p_student_id VARCHAR(24),
        IN p_event_id VARCHAR(24),
        OUT p_message VARCHAR(100)
    )
    BEGIN
        DECLARE v_student_exists INT DEFAULT 0;
        DECLARE v_event_exists INT DEFAULT 0;
        DECLARE v_already_registered INT DEFAULT 0;
        DECLARE v_capacity INT DEFAULT 0;
        DECLARE v_current_count INT DEFAULT 0;

        SELECT COUNT(*) INTO v_student_exists FROM students WHERE id = p_student_id;
        IF v_student_exists = 0 THEN
            SET p_message = 'ERROR: Student ID not found.';
        ELSE
            SELECT COUNT(*), IFNULL(MAX(capacity), 0), IFNULL(MAX(registered_count), 0)
            INTO v_event_exists, v_capacity, v_current_count
            FROM events WHERE id = p_event_id;

            IF v_event_exists = 0 THEN
                SET p_message = 'ERROR: Event ID not found.';
            ELSE
                SELECT COUNT(*) INTO v_already_registered
                FROM registrations WHERE student_id = p_student_id AND event_id = p_event_id;

                IF v_already_registered > 0 THEN
                    SET p_message = 'WARNING: Student already registered for this event.';
                ELSE
                    IF v_current_count < v_capacity THEN
                        INSERT INTO registrations (student_id, event_id, status, remarks)
                        VALUES (p_student_id, p_event_id, 'confirmed', 'Registered via sp_RegisterStudentForEvent');
                        UPDATE events SET registered_count = registered_count + 1 WHERE id = p_event_id;
                        SET p_message = 'SUCCESS: Registration confirmed.';
                    ELSE
                        INSERT INTO registrations (student_id, event_id, status, remarks)
                        VALUES (p_student_id, p_event_id, 'waitlisted', 'Event full - placed on waiting list');
                        SET p_message = 'SUCCESS: Event full, student waitlisted.';
                    END IF;
                END IF;
            END IF;
        END IF;
    END"
);

$pdo->exec("DROP PROCEDURE IF EXISTS sp_GetStudentRegistrations");
$pdo->exec(
    "CREATE PROCEDURE sp_GetStudentRegistrations(
        IN p_student_id VARCHAR(24)
    )
    BEGIN
        SELECT 
            r.id AS registration_id,
            s.id AS student_id,
            s.name AS student_name,
            s.roll_no,
            s.department,
            e.id AS event_id,
            e.title AS event_title,
            e.category AS event_category,
            e.event_date,
            e.venue,
            r.status AS registration_status,
            r.registration_date,
            r.remarks
        FROM registrations r
        INNER JOIN students s ON r.student_id = s.id
        INNER JOIN events e ON r.event_id = e.id
        WHERE r.student_id = p_student_id
        ORDER BY r.registration_date DESC;
    END"
);

fwrite(STDOUT, "Practical 8 database schema, students, events, registrations, and stored procedures are ready!\n");
