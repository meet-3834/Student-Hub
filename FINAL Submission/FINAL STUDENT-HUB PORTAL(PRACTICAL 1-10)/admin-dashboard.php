<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$user = requireRole(['admin']);
$csrf = csrfToken();

$lastLoginQuery = db()->prepare('SELECT last_login_at FROM users WHERE id = ?');
$lastLoginQuery->execute([$user['id']]);
$lastLogin = $lastLoginQuery->fetchColumn();

// Fetch Recent Audit Logs for Practical 9 Advanced Extension
$auditLogs = [];
try {
    $auditQuery = db()->query('SELECT id, user_email, action, ip_address, status, details, created_at FROM audit_logs ORDER BY id DESC LIMIT 10');
    $auditLogs = $auditQuery->fetchAll();
} catch (Throwable $e) {
    // If table not yet created
    $auditLogs = [];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard — CHARUSAT</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
      font-size: 0.88rem;
    }
    .data-table th, .data-table td {
      padding: 10px 12px;
      border: 1px solid var(--border);
      text-align: left;
    }
    .data-table th {
      background: rgba(37, 99, 235, 0.08);
      color: var(--primary);
      font-weight: 700;
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container">
      <h1 class="brand">CHARUSAT Admin</h1>
      <nav>
        <a href="index.html">Home</a>
        <a href="students.html">Manage Students</a>
        <a href="admin-dashboard.php" class="active">Admin Dashboard</a>
      </nav>
      <form action="logout.php" method="post" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <button class="btn btn-sm" type="submit">Logout</button>
      </form>
    </div>
  </header>

  <main class="container">
    <div class="page-title-section">
      <div class="section-header-flex">
        <div>
          <h2>Administrator Control Center</h2>
          <p class="subtitle">Practical 10: Role-Based Access Control &mdash; Role: <span class="tag-pill" style="background:#fee2e2; color:#991b1b; font-weight:bold;">ADMINISTRATOR</span></p>
        </div>
        <span class="source-badge">Privileged Level: SuperAdmin</span>
      </div>
    </div>

    <!-- Admin Details Card -->
    <section class="card mb-20" style="border-left: 5px solid #dc2626;">
      <h3>👤 Administrator Profile &amp; Session Inactivity Timeout</h3>
      <p>Welcome back, <strong><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></strong> (<code><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></code>). You have full privileges to create, edit, and manage student profiles.</p>
      
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-top: 14px;">
        <div style="background:var(--bg); padding:14px; border-radius:var(--radius); border:1px solid var(--border);">
          <small style="color:var(--muted); font-weight:600;">Last Login Timestamp (Intermediate Ext)</small>
          <div style="font-size:1.1rem; font-weight:700; color:var(--primary); margin-top:4px;">
            <?= htmlspecialchars($lastLogin ?: 'Initial Login', ENT_QUOTES, 'UTF-8') ?>
          </div>
        </div>
        <div style="background:var(--bg); padding:14px; border-radius:var(--radius); border:1px solid var(--border);">
          <small style="color:var(--muted); font-weight:600;">Session Timeout</small>
          <div style="font-size:1.1rem; font-weight:700; color:var(--primary); margin-top:4px;">
            15 Minutes (900 seconds)
          </div>
        </div>
        <div style="background:var(--bg); padding:14px; border-radius:var(--radius); border:1px solid var(--border);">
          <small style="color:var(--muted); font-weight:600;">Quick Actions</small>
          <div style="margin-top:6px;">
            <a href="students.html" class="btn btn-sm">Add/Edit Students &rarr;</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Practical 9 Advanced Extension: Audit Log Entries -->
    <section class="card mb-20">
      <div class="section-header-flex">
        <div>
          <h3 style="margin:0;">📝 Practical 9 Advanced Extension: Registration &amp; Auth Audit Log</h3>
          <p style="color:var(--muted); font-size:0.88rem; margin:4px 0 0;">
            Security audit logs tracking registrations, duplicate checks, login attempts, and access control.
          </p>
        </div>
        <span class="source-badge">audit_logs table</span>
      </div>

      <?php if (!empty($auditLogs)): ?>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>Log ID</th>
                <th>User / Email</th>
                <th>Action</th>
                <th>IP Address</th>
                <th>Status</th>
                <th>Details</th>
                <th>Timestamp</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($auditLogs as $log): ?>
                <tr>
                  <td>#<?= htmlspecialchars((string)$log['id']) ?></td>
                  <td><strong><?= htmlspecialchars($log['user_email']) ?></strong></td>
                  <td><span class="tag-pill"><?= htmlspecialchars($log['action']) ?></span></td>
                  <td><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
                  <td>
                    <?php if ($log['status'] === 'SUCCESS'): ?>
                      <span style="color:#15803d; font-weight:bold;">✔ SUCCESS</span>
                    <?php else: ?>
                      <span style="color:#b91c1c; font-weight:bold;">✖ FAILED</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($log['details'] ?? '') ?></td>
                  <td style="color:var(--muted); font-size:0.82rem;"><?= htmlspecialchars($log['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p style="color:var(--muted); margin-top:10px;">No audit log records stored yet. Test a registration or login attempt to populate logs.</p>
      <?php endif; ?>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>Practical 10: Role-Based Access Control • CHARUSAT Admin Portal</p>
    </div>
  </footer>
</body>
</html>
