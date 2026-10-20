<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$info = '';
$successMsg = '';

if (isset($_GET['timeout'])) {
    $info = '⚠️ Session Inactivity Timeout: Your session expired and was terminated securely according to Practical 10 requirements.';
} elseif (isset($_GET['logged_out'])) {
    $successMsg = '✔ Secure Logout: Your session has been destroyed and credentials cleared from memory.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $error = 'Your login session has expired. Please try again.';
        logAudit('unknown', 'LOGIN_ATTEMPT', 'FAILED', 'CSRF token mismatch');
    } else {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        // 1. Prepared Statement Query for User Credentials
        $statement = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ?');
        $statement->execute([$email]);
        $user = $statement->fetch();

        // 2. Secure Password Verification using password_verify()
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = '❌ Authentication Failed: Incorrect email or password.';
            logAudit($email, 'LOGIN_ATTEMPT', 'FAILED', 'Invalid password or unknown email');
        } else {
            // 3. Prevent Session Fixation: Regenerate Session ID
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            // 4. Practical 10 Intermediate Extension: Update Last-Login Timestamp
            $update = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
            $update->execute([$user['id']]);

            // 5. Practical 10 Advanced Extension: Remember-Me using Random Selector/Validator Token Hash
            if (isset($_POST['remember_me'])) {
                issueRememberToken((int) $user['id']);
            } else {
                clearRememberToken();
            }

            // 6. Audit Log Entry
            logAudit($email, 'LOGIN_ATTEMPT', 'SUCCESS', "User authenticated with role '{$user['role']}'");

            // 7. Role-Based Redirection
            if ($user['role'] === 'admin') {
                header('Location: admin-dashboard.php');
            } else {
                header('Location: student-dashboard.php');
            }
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Practical 10: Secure Login — CHARUSAT</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .credentials-box {
      background: rgba(37, 99, 235, 0.06);
      border: 1px dashed var(--accent);
      border-radius: var(--radius);
      padding: 14px 18px;
      margin-bottom: 20px;
      font-size: 0.88rem;
    }
    .cred-buttons {
      display: flex;
      gap: 8px;
      margin-top: 8px;
      flex-wrap: wrap;
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container">
      <h1 class="brand">CHARUSAT</h1>
      <nav>
        <a href="index.html">Home</a>
        <a href="events.html">Events</a>
        <a href="students.html">Students</a>
        <a href="dashboard.html">Dashboard</a>
        <a href="register.php">Register</a>
        <a href="login.php" class="active">Login</a>
        <a href="contact.php">Contact</a>
        <a href="sitemap.html">Site Map</a>
      </nav>
    </div>
  </header>

  <main class="container auth">
    <div class="page-title-section text-center">
      <h2>Portal Authentication</h2>
      <p class="subtitle">
        <strong>Practical 10:</strong> Secure Sessions, <code>password_verify()</code>, Role-Based Access Control, Session Timeout &amp; Remember-Me.
      </p>
    </div>

    <!-- Practical 10 Test Cases Quick Filler -->
    <div class="credentials-box">
      <strong>🔑 Practical 10 Test Accounts (Click to Auto-fill credentials):</strong>
      <div class="cred-buttons">
        <button type="button" class="btn btn-outline btn-sm" onclick="fillLogin('student')">Login as Student (StudentPass123!)</button>
        <button type="button" class="btn btn-outline btn-sm" onclick="fillLogin('admin')">Login as Admin (AdminPass123!)</button>
        <button type="button" class="btn btn-outline btn-sm" onclick="fillLogin('wrong')">Test Failed Login (Wrong Password)</button>
      </div>
    </div>

    <section class="card center-card" style="max-width: 540px; margin: 0 auto 30px;">
      <!-- Alerts for Screenshots (Failed, Timeout, Logout) -->
      <?php if ($error !== ''): ?>
        <div class="alert alert-error" style="background:#fdecec; color:#b42318; padding:12px 16px; border-radius:8px; border:1px solid #efb4b4; margin-bottom:16px;" role="alert">
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php elseif ($info !== ''): ?>
        <div class="alert alert-error" style="background:#fffbeb; color:#b45309; padding:12px 16px; border-radius:8px; border:1px solid #fde68a; margin-bottom:16px;" role="alert">
          <?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php elseif ($successMsg !== ''): ?>
        <div class="alert alert-success" style="background:#eafaf1; color:#167a43; padding:12px 16px; border-radius:8px; border:1px solid #bde8c7; margin-bottom:16px;" role="alert">
          <?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form action="login.php" method="post" class="auth-form" id="loginForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        
        <label>Email Address
          <input type="email" id="loginEmail" name="email" autocomplete="username" required placeholder="e.g. student@charusat.edu.in">
        </label>
        
        <label>Password
          <input type="password" id="loginPassword" name="password" autocomplete="current-password" required placeholder="Enter your password">
        </label>

        <!-- Practical 10 Advanced Extension: Remember-Me Option -->
        <label class="inline-label" style="margin: 6px 0 16px; cursor: pointer;">
          <input type="checkbox" name="remember_me" value="1" checked>
          <span>Remember me for 30 days (Secure Selector/Validator Hash Token)</span>
        </label>

        <button class="btn full" type="submit">Sign In to Dashboard</button>
      </form>

      <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--border); text-align: center; font-size: 0.9rem; color: var(--muted);">
        Don't have an account? <a href="register.php">Create Student Account (Practical 9)</a>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>Practical 10: Secure Authentication, Session Management &amp; RBAC • CHARUSAT</p>
    </div>
  </footer>

  <script>
    function fillLogin(role) {
      const email = document.getElementById('loginEmail');
      const pass = document.getElementById('loginPassword');
      if (role === 'student') {
        email.value = 'student@charusat.edu.in';
        pass.value = 'StudentPass123!';
      } else if (role === 'admin') {
        email.value = 'admin@charusat.edu.in';
        pass.value = 'AdminPass123!';
      } else if (role === 'wrong') {
        email.value = 'student@charusat.edu.in';
        pass.value = 'IncorrectPassword999';
      }
    }
  </script>
</body>
</html>
