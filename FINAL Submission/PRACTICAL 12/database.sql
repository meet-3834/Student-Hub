CREATE DATABASE IF NOT EXISTS practical12
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE practical12;

CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    category VARCHAR(80) NOT NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    poster VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
