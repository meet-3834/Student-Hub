<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

const SESSION_IDLE_LIMIT = 900;
const REMEMBER_COOKIE = 'charusat_remember';

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
    if (isset($_GET['test_timeout']) && isset($_SESSION['user_id'])) {
        clearRememberToken();
        destroySession();
        header('Location: login.php?timeout=1');
        exit;
    }
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['last_activity']) && time() - (int) $_SESSION['last_activity'] > SESSION_IDLE_LIMIT) {
            clearRememberToken();
            destroySession();
            header('Location: login.php?timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}

function destroySession(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Lax',
        ]);
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function setRememberCookie(string $value, int $expires): void
{
    setcookie(REMEMBER_COOKIE, $value, [
        'expires' => $expires,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function issueRememberToken(int $userId): void
{
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $expires = time() + 60 * 60 * 24 * 30;
    $statement = db()->prepare('INSERT INTO remember_tokens (selector, user_id, validator_hash, expires_at) VALUES (?, ?, ?, ?)');
    $statement->execute([$selector, $userId, hash('sha256', $validator), date('Y-m-d H:i:s', $expires)]);
    setRememberCookie($selector . ':' . $validator, $expires);
}

function clearRememberToken(): void
{
    $cookie = $_COOKIE[REMEMBER_COOKIE] ?? '';
    if (preg_match('/^([a-f0-9]{24}):([a-f0-9]{64})$/', $cookie, $matches)) {
        $statement = db()->prepare('DELETE FROM remember_tokens WHERE selector = ?');
        $statement->execute([$matches[1]]);
    }
    setRememberCookie('', time() - 3600);
}

function restoreRememberedUser(): void
{
    $cookie = $_COOKIE[REMEMBER_COOKIE] ?? '';
    if (!preg_match('/^([a-f0-9]{24}):([a-f0-9]{64})$/', $cookie, $matches)) {
        return;
    }

    $statement = db()->prepare(
        'SELECT rt.user_id, rt.validator_hash, rt.expires_at, u.email, u.name, u.role
         FROM remember_tokens rt JOIN users u ON u.id = rt.user_id WHERE rt.selector = ?'
    );
    $statement->execute([$matches[1]]);
    $token = $statement->fetch();
    if (!$token || strtotime($token['expires_at']) <= time()
        || !hash_equals($token['validator_hash'], hash('sha256', $matches[2]))) {
        clearRememberToken();
        return;
    }

    $delete = db()->prepare('DELETE FROM remember_tokens WHERE selector = ?');
    $delete->execute([$matches[1]]);
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $token['user_id'];
    $_SESSION['email'] = $token['email'];
    $_SESSION['name'] = $token['name'];
    $_SESSION['role'] = $token['role'];
    $_SESSION['last_activity'] = time();
    issueRememberToken((int) $token['user_id']);
}

function currentUser(): ?array
{
    startSecureSession();
    if (!isset($_SESSION['user_id'])) {
        restoreRememberedUser();
    }
    if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
        return null;
    }
    return [
        'id' => (int) $_SESSION['user_id'],
        'email' => (string) $_SESSION['email'],
        'name' => (string) $_SESSION['name'],
        'role' => (string) $_SESSION['role'],
    ];
}

function logAudit(string $email, string $action, string $status, string $details = ''): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);
    $timestamp = date('Y-m-d H:i:s');

    // 1. Log to database if table exists
    try {
        $stmt = db()->prepare(
            'INSERT INTO audit_logs (user_email, action, ip_address, user_agent, status, details) 
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$email, $action, $ip, $userAgent, $status, $details]);
    } catch (Throwable $e) {
        // Table may be created during setup
    }

    // 2. Log to file data/registration_audit.log
    $logDir = __DIR__ . '/data';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logLine = sprintf("[%s] IP: %s | Email: %s | Action: %s | Status: %s | %s\n", 
        $timestamp, $ip, $email, $action, $status, $details);
    @file_put_contents($logDir . '/registration_audit.log', $logLine, FILE_APPEND | LOCK_EX);
}

function requireRole(array $roles): array
{
    $user = currentUser();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        logAudit($user['email'], 'UNAUTHORIZED_ACCESS', 'FAILED', 'Attempted access to ' . basename($_SERVER['PHP_SELF']) . ' without ' . implode('/', $roles) . ' role');
        $csrf = csrfToken();
        ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>403 Forbidden — Unauthorized Access Attempt</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="site-header">
    <div class="container">
      <h1 class="brand">CHARUSAT Security</h1>
      <nav>
        <a href="index.html">Home</a>
        <a href="dashboard.php">Dashboard</a>
      </nav>
      <form action="logout.php" method="post" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <button class="btn btn-sm" type="submit">Logout</button>
      </form>
    </div>
  </header>
  <main class="container">
    <section class="card center-card" style="border-left: 5px solid #dc2626; margin-top: 40px;">
      <span class="source-badge" style="background:#fee2e2; color:#b91c1c;">Practical 10: Role-Based Access Control (RBAC)</span>
      <h2 style="color: #b91c1c; margin-top: 12px;">🚫 403 Forbidden: Unauthorized Access Attempt</h2>
      <p>Access to this administrative page is restricted by role-based access control.</p>
      <div style="background: var(--bg); padding: 14px 18px; border-radius: var(--radius); margin: 16px 0; font-size: 0.95rem;">
        <p style="margin: 4px 0;"><strong>Signed-in User:</strong> <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>)</p>
        <p style="margin: 4px 0;"><strong>Current Account Role:</strong> <span class="tag-pill" style="background:#fef3c7; color:#92400e; font-weight:bold;"><?= strtoupper($user['role']) ?></span></p>
        <p style="margin: 4px 0;"><strong>Required Role(s):</strong> <span class="tag-pill" style="background:#dbeafe; color:#1e40af; font-weight:bold;"><?= strtoupper(implode(', ', $roles)) ?></span></p>
        <p style="margin: 4px 0;"><strong>Attempted Target:</strong> <code><?= htmlspecialchars(basename($_SERVER['PHP_SELF']), ENT_QUOTES, 'UTF-8') ?></code></p>
      </div>
      <p style="color: var(--muted); font-size: 0.9rem;">
        📸 <em>Take a screenshot of this page for Practical 10 "unauthorized access attempt" submission.</em>
      </p>
      <div style="display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap;">
        <a href="student-dashboard.php" class="btn">Return to Student Dashboard</a>
        <a href="login.php" class="btn btn-outline">Switch Account (Login as Admin)</a>
      </div>
    </section>
  </main>
</body>
</html>
        <?php
        exit;
    }
    return $user;
}

function csrfToken(): string
{
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    startSecureSession();
    return is_string($token) && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

startSecureSession();
