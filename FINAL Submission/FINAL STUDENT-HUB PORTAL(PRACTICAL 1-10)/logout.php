<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Invalid logout request.');
}
clearRememberToken();
destroySession();
header('Location: login.php?logged_out=1');
exit;
