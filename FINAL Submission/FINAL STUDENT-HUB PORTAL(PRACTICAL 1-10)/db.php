<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('PORTAL_DB_HOST') ?: '127.0.0.1';
    $name = getenv('PORTAL_DB_NAME') ?: 'charusat_portal';
    $user = getenv('PORTAL_DB_USER') ?: 'root';
    $password = getenv('PORTAL_DB_PASSWORD') !== false ? (string)getenv('PORTAL_DB_PASSWORD') : '';

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $name);
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
