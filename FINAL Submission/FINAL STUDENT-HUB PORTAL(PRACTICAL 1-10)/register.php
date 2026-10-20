<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$duplicateError = '';
$success = false;
$successEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Practical 9 Advanced Extension: CSRF Token Verification
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $error = 'Your registration session has expired. Please refresh and try again.';
        logAudit('unknown', 'USER_REGISTRATION', 'FAILED', 'CSRF token mismatch or expired');
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirmPassword'] ?? '');

        // 1. Backend Input Validation
        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            $error = 'Full name must be between 2 and 120 characters.';
            logAudit($email, 'USER_REGISTRATION', 'FAILED', 'Invalid name length');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address (e.g. scholar@charusat.edu.in).';
            logAudit($email, 'USER_REGISTRATION', 'FAILED', 'Invalid email syntax');
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
            logAudit($email, 'USER_REGISTRATION', 'FAILED', 'Password too short');
        } elseif ($password !== $confirm) {
            $error = 'Password and Confirm Password do not match.';
            logAudit($email, 'USER_REGISTRATION', 'FAILED', 'Password mismatch');
        } else {
            // 2. Duplicate Email Check (Prepared Statement)
            $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $existing = $stmt->fetch();

            if ($existing) {
                $duplicateError = "Duplicate Email Detected: An account with '{$email}' already exists in the database.";
                logAudit($email, 'USER_REGISTRATION', 'FAILED', 'Duplicate email check failed');
            } else {
                // 3. Password Hashing & Secure Database Insert (Prepared Statement)
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insert = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, \'student\')');
                try {
                    $insert->execute([$name, $email, $hashedPassword]);

                    // 4. Practical 9 Advanced Extension: Audit Log Entry
                    logAudit($email, 'USER_REGISTRATION', 'SUCCESS', "New student account created for {$name}");

                    $success = true;
                    $successEmail = $email;
                } catch (PDOException $exception) {
                    if (($exception->errorInfo[1] ?? null) !== 1062) {
                        throw $exception;
                    }
                    $duplicateError = "Duplicate Email Detected: An account with '{$email}' already exists in the database.";
                    logAudit($email, 'USER_REGISTRATION', 'FAILED', 'Duplicate email insert rejected by unique constraint');
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Practical 9: Secure Registration — CHARUSAT</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .test-box {
      background: rgba(37, 99, 235, 0.06);
      border: 1px dashed var(--accent);
      border-radius: var(--radius);
      padding: 14px 18px;
      margin-bottom: 20px;
      font-size: 0.88rem;
    }
    .test-btn-group {
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
        <a href="register.php" class="active">Register</a>
        <a href="login.php">Login</a>
        <a href="contact.php">Contact</a>
        <a href="sitemap.html">Site Map</a>
      </nav>
    </div>
  </header>

  <main class="container auth">
    <div class="page-title-section text-center">
      <h2>Secure User Registration</h2>
      <p class="subtitle">
        <strong>Practical 9:</strong> Database Insert with Prepared Statements, Duplicate Email Check, Password Hashing &amp; Audit Logging.
      </p>
    </div>

    <!-- Practical 9 Test Report Quick Helper -->
    <div class="test-box">
      <strong>🧪 Practical 9 Test Cases Helper (for Lab Submission):</strong>
      <p style="margin: 4px 0 8px; color: var(--muted);">Click a button to auto-fill inputs and observe outputs for valid, duplicate, and invalid test cases:</p>
      <div class="test-btn-group">
        <button type="button" class="btn btn-outline btn-sm" onclick="fillTestCase('valid')">Case 1: Valid New User</button>
        <button type="button" class="btn btn-outline btn-sm" onclick="fillTestCase('duplicate')">Case 2: Duplicate Email</button>
        <button type="button" class="btn btn-outline btn-sm" onclick="fillTestCase('invalid')">Case 3: Mismatched Passwords</button>
      </div>
    </div>

    <section class="card center-card" style="max-width: 600px; margin: 0 auto 30px;">
      <!-- Alerts -->
      <?php if ($error !== ''): ?>
        <div class="alert alert-error" style="background:#fdecec; color:#b42318; padding:12px 16px; border-radius:8px; border:1px solid #efb4b4; margin-bottom:16px;" role="alert">
          <strong>Validation Error:</strong> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php elseif ($duplicateError !== ''): ?>
        <div class="alert alert-error" style="background:#fffbeb; color:#b45309; padding:12px 16px; border-radius:8px; border:1px solid #fde68a; margin-bottom:16px;" role="alert">
          <strong>⚠️ Duplicate Check:</strong> <?= htmlspecialchars($duplicateError, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success" style="background:#eafaf1; color:#167a43; padding:14px 18px; border-radius:8px; border:1px solid #bde8c7; margin-bottom:16px;">
          <h3 style="margin: 0 0 4px; color:#167a43;">✔ Registration Successful!</h3>
          <p style="margin: 0;">Student account created for <strong><?= htmlspecialchars($successEmail, ENT_QUOTES, 'UTF-8') ?></strong> with hashed password.</p>
          <p style="margin: 6px 0 0; font-size: 0.88rem;">Audit log entry recorded in <code>audit_logs</code> database table and log file.</p>
        </div>
        <div style="display:flex; gap:10px; margin-top:16px;">
          <a href="login.php" class="btn full" style="text-align:center;">Proceed to Login (Practical 10) &rarr;</a>
          <a href="register.php" class="btn btn-outline" style="text-align:center;">Register Another</a>
        </div>
      <?php else: ?>

        <form method="post" action="register.php" class="auth-form" id="dbRegForm">
          <!-- Practical 9 Advanced Extension: CSRF Token -->
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

          <label>Full Name <span style="color:#dc2626;">*</span>
            <input type="text" id="regName" name="name" maxlength="120" required placeholder="e.g. Meet Patel">
          </label>

          <label>Email Address <span style="color:#dc2626;">*</span>
            <input type="email" id="regEmail" name="email" maxlength="190" required placeholder="e.g. meet@charusat.edu.in">
          </label>

          <label>Password <span style="color:#dc2626;">*</span>
            <input type="password" id="regPassword" name="password" minlength="6" required placeholder="At least 6 characters">
          </label>

          <label>Confirm Password <span style="color:#dc2626;">*</span>
            <input type="password" id="regConfirm" name="confirmPassword" minlength="6" required placeholder="Re-type password">
          </label>

          <button class="btn full" type="submit" style="margin-top: 10px;">Register Student Account</button>
        </form>

      <?php endif; ?>

      <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--border); text-align: center; font-size: 0.9rem; color: var(--muted);">
        Already registered? <a href="login.php">Sign In to Dashboard</a> | <a href="register.html">Static Form (Practical 5)</a>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>Practical 9: Secure Registration with Password Hashing &amp; Duplicate Check • CHARUSAT</p>
    </div>
  </footer>

  <script>
    function fillTestCase(type) {
      const name = document.getElementById('regName');
      const email = document.getElementById('regEmail');
      const pass = document.getElementById('regPassword');
      const conf = document.getElementById('regConfirm');
      if (!name || !email) return;

      if (type === 'valid') {
        const uniqueId = Date.now();
        name.value = 'Demo Scholar ' + uniqueId;
        email.value = 'scholar' + uniqueId + '@charusat.edu.in';
        pass.value = 'StudentPass123!';
        conf.value = 'StudentPass123!';
      } else if (type === 'duplicate') {
        name.value = 'Portal Student';
        email.value = 'student@charusat.edu.in'; // Already exists in seeded database
        pass.value = 'StudentPass123!';
        conf.value = 'StudentPass123!';
      } else if (type === 'invalid') {
        name.value = 'Demo Scholar';
        email.value = 'invalid.case@charusat.edu.in';
        pass.value = 'StudentPass123!';
        conf.value = 'DifferentPass123!';
      }
    }
  </script>
</body>
</html>
