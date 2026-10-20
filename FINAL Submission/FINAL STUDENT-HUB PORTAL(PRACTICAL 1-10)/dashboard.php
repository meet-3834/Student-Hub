<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
$user = currentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}
header('Location: ' . ($user['role'] === 'admin' ? 'admin-dashboard.php' : 'student-dashboard.php'));
exit;
