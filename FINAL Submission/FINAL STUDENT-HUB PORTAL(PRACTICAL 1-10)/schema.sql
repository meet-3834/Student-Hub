-- ============================================================================
-- CHARUSAT STUDENT HUB - PRACTICAL 8 DATABASE SCHEMA DUMP
-- Normalized MySQL Schema Design, ER Model, PDO Connectivity & Stored Procedures
-- Database Name: charusat_portal
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `charusat_portal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `charusat_portal`;

-- Disable Foreign Key checks for clean recreation
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `remember_tokens`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `users`;
DROP PROCEDURE IF EXISTS `sp_RegisterStudentForEvent`;
DROP PROCEDURE IF EXISTS `sp_GetStudentRegistrations`;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- Table 1: users (Portal Authentication & Role-Based Access Control)
-- ----------------------------------------------------------------------------
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
  `last_login_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table 2: remember_tokens (Persistent Session Security)
-- ----------------------------------------------------------------------------
CREATE TABLE `remember_tokens` (
  `selector` CHAR(24) NOT NULL PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `validator_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  CONSTRAINT `fk_remember_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table 3: students (Normalized Student Entity)
-- ----------------------------------------------------------------------------
CREATE TABLE `students` (
  `id` VARCHAR(24) NOT NULL PRIMARY KEY,
  `roll_no` VARCHAR(32) NOT NULL UNIQUE,
  `name` VARCHAR(120) NOT NULL,
  `department` VARCHAR(120) NOT NULL,
  `semester` TINYINT UNSIGNED NOT NULL,
  `cgpa` DECIMAL(4,2) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `country` VARCHAR(80) NOT NULL,
  `state` VARCHAR(80) NOT NULL,
  `city` VARCHAR(80) NOT NULL,
  `skills` JSON NOT NULL,
  `avatar_initials` VARCHAR(8) NOT NULL,
  `avatar_color` CHAR(7) NOT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table 4: events (Normalized Events Entity)
-- ----------------------------------------------------------------------------
CREATE TABLE `events` (
  `id` VARCHAR(24) NOT NULL PRIMARY KEY,
  `title` VARCHAR(190) NOT NULL,
  `category` VARCHAR(60) NOT NULL,
  `event_date` DATE NOT NULL,
  `event_time` VARCHAR(60) NOT NULL,
  `venue` VARCHAR(190) NOT NULL,
  `organizer` VARCHAR(120) NOT NULL,
  `capacity` INT UNSIGNED NOT NULL DEFAULT 100,
  `registered_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT NOT NULL,
  `tags` JSON NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table 5: registrations (Normalized Junction Table linking Students & Events)
-- Demonstrates Foreign Key Relationships:
-- student_id (FK -> students.id) & event_id (FK -> events.id)
-- ----------------------------------------------------------------------------
CREATE TABLE `registrations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(24) NOT NULL,
  `event_id` VARCHAR(24) NOT NULL,
  `registration_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('confirmed', 'waitlisted', 'cancelled') NOT NULL DEFAULT 'confirmed',
  `remarks` VARCHAR(255) NULL DEFAULT 'Self-registered via Portal',
  CONSTRAINT `fk_registration_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_registration_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `unique_student_event` (`student_id`, `event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table 6: audit_logs (Practical 9 Advanced Extension: Registration & Auth Audit Log)
-- ----------------------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_email` VARCHAR(190) NOT NULL,
  `action` VARCHAR(60) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NULL,
  `status` ENUM('SUCCESS', 'FAILED') NOT NULL,
  `details` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- 1. Seed Authentication Accounts:
-- Admin: admin@charusat.edu.in (Password: AdminPass123!)
-- Student: student@charusat.edu.in (Password: StudentPass123!)
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`) VALUES
(1, 'Portal Administrator', 'admin@charusat.edu.in', '$2y$10$XI.o.6sla6EMDBbRuqLjTeykHKicmrbDzUfhRuc5OZnA2PICaJRC.', 'admin'),
(2, 'Portal Student', 'student@charusat.edu.in', '$2y$10$yv0tcS9d6nU/KH7MR4FRiOoHYfnAHIzjfxpjtbzJToeCeLHM0e7sG', 'student')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 2. Seed Students (16 Records from students.json)
INSERT INTO `students` (`id`, `roll_no`, `name`, `department`, `semester`, `cgpa`, `email`, `country`, `state`, `city`, `skills`, `avatar_initials`, `avatar_color`, `status`) VALUES
('STU-001', '25dce100', 'meet a', 'Computer Engineering', 6, 9.42, 'meet.1@charusat.edu.in', 'India', 'Gujarat', 'surat', '["JavaScript", "Python", "Data Structures", "Docker"]', 'AP', '#4f46e5', 'Active'),
('STU-002', '23IT014', 'neel patel', 'Information Technology', 4, 9.15, 'neel.it23@charusat.edu.in', 'India', 'Gujarat', 'anand', '["Web Development", "UI/UX", "CSS3", "React"]', 'DS', '#059669', 'Active'),
('STU-003', '22CE088', 'Rohan Mehta', 'Computer Engineering', 6, 8.87, 'rohan.ce22@charusat.edu.in', 'India', 'Gujarat', 'ahmedabad', '["Java", "Spring Boot", "MySQL", "AWS"]', 'RM', '#2563eb', 'Active'),
('STU-004', '24EC012', 'Priya Joshi', 'Electronics & Communication', 2, 9.35, 'priya.ec24@charusat.edu.in', 'India', 'Gujarat', 'vadodara', '["Embedded Systems", "IoT", "C++", "MATLAB"]', 'PJ', '#d97706', 'Active'),
('STU-005', '23MCA031', 'Sneha Desai', 'Computer Applications', 4, 8.92, 'sneha.mca23@charusat.edu.in', 'India', 'Gujarat', 'rajkot', '["PHP", "Laravel", "MySQL", "Vue.js"]', 'SD', '#7c3aed', 'Active'),
('STU-006', '22ME045', 'Karan Verma', 'Mechanical Engineering', 6, 8.45, 'karan.me22@charusat.edu.in', 'India', 'Maharashtra', 'mumbai', '["SolidWorks", "AutoCAD", "Thermodynamics", "ANSYS"]', 'KV', '#dc2626', 'Active'),
('STU-007', '23CE064', 'Ananya Trivedi', 'Computer Engineering', 4, 9.61, 'ananya.ce23@charusat.edu.in', 'India', 'Gujarat', 'gandhinagar', '["Machine Learning", "PyTorch", "NLP", "Python"]', 'AT', '#0891b2', 'Active'),
('STU-008', '24IT076', 'Devanshi Shah', 'Information Technology', 2, 8.78, 'devanshi.it24@charusat.edu.in', 'India', 'Gujarat', 'surat', '["HTML5", "CSS3", "JavaScript", "Figma"]', 'DS', '#db2777', 'Active'),
('STU-009', '21EE029', 'Harsh Patel', 'Electrical Engineering', 8, 8.52, 'harsh.ee21@charusat.edu.in', 'India', 'Gujarat', 'ahmedabad', '["Power Systems", "PLC", "SCADA", "MATLAB"]', 'HP', '#ea580c', 'Active'),
('STU-010', '23PH019', 'Riddhi Parikh', 'Pharmacy', 4, 9.24, 'riddhi.ph23@charusat.edu.in', 'India', 'Gujarat', 'vadodara', '["Pharmacology", "Drug Formulation", "HPLC", "Clinical Trials"]', 'RP', '#059669', 'Active'),
('STU-011', '22MBA008', 'Tanmay Bhatt', 'Management', 4, 8.68, 'tanmay.mba22@charusat.edu.in', 'India', 'Maharashtra', 'pune', '["Financial Modeling", "Strategic Marketing", "Tableau", "Excel"]', 'TB', '#4338ca', 'Active'),
('STU-012', '24CE112', 'Vikram Rathore', 'Computer Engineering', 2, 8.94, 'vikram.ce24@charusat.edu.in', 'India', 'Rajasthan', 'jaipur', '["C++", "Algorithms", "Competitive Programming", "Git"]', 'VR', '#b91c1c', 'Active'),
('STU-013', '23IT095', 'Pooja Nair', 'Information Technology', 4, 9.08, 'pooja.it23@charusat.edu.in', 'India', 'Karnataka', 'bengaluru', '["Cloud Computing", "Go", "Kubernetes", "Microservices"]', 'PN', '#0d9488', 'Active'),
('STU-014', '22CE051', 'Manish Dave', 'Computer Engineering', 6, 8.35, 'manish.ce22@charusat.edu.in', 'India', 'Gujarat', 'bhavnagar', '["Network Security", "Ethical Hacking", "Linux", "Python"]', 'MD', '#65a30d', 'Active'),
('STU-015', '23EC037', 'Shreya Kulkarni', 'Electronics & Communication', 4, 9.12, 'shreya.ec23@charusat.edu.in', 'India', 'Maharashtra', 'nagpur', '["VLSI Design", "Verilog", "Digital Electronics", "FPGA"]', 'SK', '#9333ea', 'Active'),
('STU-016', '24IT003', 'Aditya Singh', 'Information Technology', 2, 8.71, 'aditya.it24@charusat.edu.in', 'India', 'Delhi', 'delhi', '["Full Stack", "Node.js", "Express", "MongoDB"]', 'AS', '#2563eb', 'Active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 3. Seed Events (16 Records from events.json)
INSERT INTO `events` (`id`, `title`, `category`, `event_date`, `event_time`, `venue`, `organizer`, `capacity`, `registered_count`, `description`, `tags`) VALUES
('EVT-101', 'Cognizance 2026 - Annual Tech Fest', 'Technical', '2026-10-15', '09:30 AM - 05:30 PM', 'CMPICA Auditorium, CHARUSAT', 'Department of Computer Science', 450, 380, 'The flagship technical symposium of CHARUSAT featuring 15+ coding events, robotics arenas, paper presentations, and tech talks from industry leaders.', '["Coding", "Robotics", "Symposium", "Tech"]'),
('EVT-102', 'CHARUSAT National Hackathon 2026', 'Hackathon', '2026-10-24', '36-Hour Non-stop', 'CSPIT Central Computing Center', 'DevSphere Student Club', 200, 195, '36-hour intense hackathon focusing on AI for Good, Healthcare Innovations, Smart Mobility, and Sustainable Campus Tech with prize pool of ₹1,50,000.', '["Hackathon", "AI", "Development", "Innovation"]'),
('EVT-103', 'Mastering Modern Web Architecture', 'Workshop', '2026-10-05', '02:00 PM - 05:00 PM', 'Seminar Hall 2, DEPSTAR', 'Web Development Cell', 120, 120, 'Hands-on deep dive into responsive design systems, CSS grid architectures, modern ES6+ paradigms, and Fetch API integrations for scalable SPAs.', '["WebDev", "Architecture", "JavaScript", "CSS"]'),
('EVT-104', 'Cloud Computing & DevOps Bootcamp', 'Workshop', '2026-10-18', '10:00 AM - 04:00 PM', 'Lab 502, CSPIT', 'AWS Cloud Student Community', 80, 72, 'Comprehensive practical training on Docker containers, Kubernetes cluster management, CI/CD pipelines, and cloud native architectures on AWS.', '["Cloud", "Docker", "DevOps", "Kubernetes"]'),
('EVT-105', 'CyberGuard: Hands-on Ethical Hacking', 'Workshop', '2026-10-28', '01:30 PM - 05:30 PM', 'Cyber Security Center of Excellence', 'Infosec Student Guild', 90, 88, 'Interactive capture-the-flag (CTF) challenges, network penetration testing methodologies, web vulnerability assessments, and secure coding practices.', '["Security", "Cyber", "EthicalHacking", "CTF"]'),
('EVT-106', 'AI & Deep Learning Symposium 2026', 'Technical', '2026-11-02', '09:00 AM - 04:30 PM', 'Main Auditorium, CHARUSAT', 'Center for Artificial Intelligence', 300, 260, 'Keynote speeches and research paper sessions exploring Large Language Models, Computer Vision breakthroughs, and autonomous systems.', '["AI", "DeepLearning", "Research", "LLM"]'),
('EVT-107', 'Inter-College Badminton Championship', 'Sports', '2026-10-12', '08:00 AM - 06:00 PM', 'University Indoor Sports Complex', 'CHARUSAT Sports Council', 64, 64, 'Annual singles and doubles badminton tournament with teams competing from 18 universities across western India for the championship trophy.', '["Sports", "Badminton", "Tournament", "Fitness"]'),
('EVT-108', 'Spandan 2026 - Annual Cultural Fest', 'Cultural', '2026-11-20', '05:00 PM - 10:00 PM', 'Open Air Theatre (OAT)', 'University Cultural Committee', 1200, 1150, 'The premier cultural extravaganza uniting students in music, classical & contemporary dance battles, dramatic performances, fashion showcases, and live concerts.', '["Cultural", "Music", "Dance", "Festival"]'),
('EVT-109', 'Data Science & Big Data Analytics Summit', 'Technical', '2026-11-08', '10:00 AM - 04:00 PM', 'Seminar Hall 1, CMPICA', 'Data Science Student Chapter', 150, 135, 'Interactive sessions with data architects on Spark, distributed data pipelines, predictive modeling, and business analytics in enterprise scale applications.', '["DataScience", "Analytics", "BigData", "Python"]'),
('EVT-110', 'Design Thinking & UI/UX Masterclass', 'Workshop', '2026-10-30', '11:00 AM - 03:00 PM', 'Design Studio, FOT', 'Creative Computing Club', 75, 75, 'Explore user-centric product discovery, wireframing, high-fidelity prototyping with Figma, usability heuristics, and design system creation.', '["UIUX", "Design", "Figma", "Product"]'),
('EVT-111', 'CHARUSAT Premier Cricket League (CPCL)', 'Sports', '2026-11-15', '07:30 AM - 06:30 PM', 'University Cricket Grounds', 'Sports Council & Athletic Board', 16, 16, 'T20 inter-departmental cricket tournament featuring live commentary, digital scoring, and professional umpiring across a 5-day tournament.', '["Cricket", "Sports", "CPCL", "Championship"]'),
('EVT-112', 'Virasat - Folk Heritage Evening', 'Cultural', '2026-10-09', '06:30 PM - 09:30 PM', 'Central Amphitheatre', 'Sanskriti Heritage Forum', 500, 480, 'A vibrant celebration of regional folklore, traditional Garba, folk ballads, instrumental music, and traditional attire displays.', '["Folk", "Heritage", "Garba", "Tradition"]'),
('EVT-113', 'Blockchain & Web3 Developer Meetup', 'Technical', '2026-11-25', '02:00 PM - 05:30 PM', 'Innovation & Incubation Center', 'Web3 Campus Chapter', 100, 85, 'Practical workshop on Solidity smart contract engineering, decentralized finance (DeFi) protocols, zero-knowledge proofs, and DApp deployment.', '["Blockchain", "Web3", "Ethereum", "Crypto"]'),
('EVT-114', 'Internet of Things (IoT) Prototyping Expo', 'Technical', '2026-12-04', '09:30 AM - 04:30 PM', 'Robotics & Automation Lab', 'Department of Electronics', 120, 105, 'Exhibition and live demonstration of 40+ hardware prototypes built using ESP32, Raspberry Pi, LoRaWAN networks, and industrial sensors.', '["IoT", "Hardware", "Arduino", "Robotics"]'),
('EVT-115', 'Campus Table Tennis Open 2026', 'Sports', '2026-10-22', '03:00 PM - 07:00 PM', 'Student Activity Center', 'Sports Council', 48, 48, 'Intense knockout table tennis championship open to all students, faculty, and research scholars with trophies for boys and girls singles.', '["TableTennis", "Sports", "Fitness", "Indoor"]'),
('EVT-116', 'Rhythm & Beats - Battle of the Bands', 'Cultural', '2026-12-10', '06:00 PM - 10:00 PM', 'OAT Grounds', 'Music Club CHARUSAT', 800, 750, 'High-octane musical showdown featuring 12 college rock and acoustic bands competing for the crown with live audience voting.', '["Music", "Rock", "Bands", "Concert"]')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- 4. Seed Registrations (Normalized junction links)
INSERT INTO `registrations` (`student_id`, `event_id`, `status`, `remarks`) VALUES
('STU-001', 'EVT-101', 'confirmed', 'Technical Symposium Delegate'),
('STU-001', 'EVT-102', 'confirmed', 'Hackathon Team Lead'),
('STU-002', 'EVT-102', 'confirmed', 'Hackathon Frontend Dev'),
('STU-002', 'EVT-103', 'confirmed', 'Web Architecture Workshop Attendee'),
('STU-003', 'EVT-101', 'confirmed', 'Coding Track Participant'),
('STU-004', 'EVT-104', 'confirmed', 'Cloud Computing Seminar'),
('STU-005', 'EVT-105', 'waitlisted', 'Cybersecurity Workshop Waiting List'),
('STU-006', 'EVT-106', 'confirmed', 'AI Summit Attendee'),
('STU-007', 'EVT-102', 'confirmed', 'AI Track Competitor'),
('STU-008', 'EVT-110', 'confirmed', 'UI/UX Workshop Attendee')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

-- ============================================================================
-- PRACTICAL 8 ADVANCED EXTENSION: STORED PROCEDURES
-- ============================================================================

DELIMITER $$

-- Procedure 1: Register a Student for an Event with Verification & Capacity Logic
CREATE PROCEDURE `sp_RegisterStudentForEvent`(
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

    -- Step 1: Verify that student exists
    SELECT COUNT(*) INTO v_student_exists FROM `students` WHERE `id` = p_student_id;
    IF v_student_exists = 0 THEN
        SET p_message = 'ERROR: Student ID not found in database.';
    ELSE
        -- Step 2: Verify that event exists
        SELECT COUNT(*), IFNULL(MAX(`capacity`), 0), IFNULL(MAX(`registered_count`), 0)
        INTO v_event_exists, v_capacity, v_current_count
        FROM `events` WHERE `id` = p_event_id;

        IF v_event_exists = 0 THEN
            SET p_message = 'ERROR: Event ID not found in database.';
        ELSE
            -- Step 3: Check for duplicate registration
            SELECT COUNT(*) INTO v_already_registered
            FROM `registrations`
            WHERE `student_id` = p_student_id AND `event_id` = p_event_id;

            IF v_already_registered > 0 THEN
                SET p_message = 'WARNING: Student is already registered for this event.';
            ELSE
                -- Step 4: Perform insertion and update count
                IF v_current_count < v_capacity THEN
                    INSERT INTO `registrations` (`student_id`, `event_id`, `status`, `remarks`)
                    VALUES (p_student_id, p_event_id, 'confirmed', 'Registered via sp_RegisterStudentForEvent');

                    UPDATE `events` SET `registered_count` = `registered_count` + 1 WHERE `id` = p_event_id;
                    SET p_message = 'SUCCESS: Registration confirmed successfully.';
                ELSE
                    INSERT INTO `registrations` (`student_id`, `event_id`, `status`, `remarks`)
                    VALUES (p_student_id, p_event_id, 'waitlisted', 'Event full - placed on waiting list');

                    SET p_message = 'SUCCESS: Event at full capacity. Student added to waitlist.';
                END IF;
            END IF;
        END IF;
    END IF;
END $$

-- Procedure 2: Retrieve Event Registrations for a Student (Normalized Multi-table JOIN)
CREATE PROCEDURE `sp_GetStudentRegistrations`(
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
    FROM `registrations` r
    INNER JOIN `students` s ON r.student_id = s.id
    INNER JOIN `events` e ON r.event_id = e.id
    WHERE r.student_id = p_student_id
    ORDER BY r.registration_date DESC;
END $$

DELIMITER ;
