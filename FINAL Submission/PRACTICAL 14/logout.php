<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (isset($_SESSION['user_id'])) {
    $pdo = getDatabaseConnection();
    recordAudit($pdo, (int) $_SESSION['user_id'], 'logout', 'User signed out');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookie['path'],
        $cookie['domain'],
        $cookie['secure'],
        $cookie['httponly']
    );
}

session_destroy();
header('Location: login.php');
exit;
