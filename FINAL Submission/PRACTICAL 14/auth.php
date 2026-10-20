<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function recordAudit(PDO $pdo, ?int $userId, string $action, string $details = ''): void
{
    $statement = $pdo->prepare(
        'INSERT INTO audit_logs (user_id, action, details) VALUES (:user_id, :action, :details)'
    );
    $statement->execute([
        'user_id' => $userId,
        'action' => $action,
        'details' => $details,
    ]);
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}
