<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$user = requireRole(['student']);
$csrf = csrfToken();

// Query last login timestamp for Practical 10 Intermediate Extension
$lastLoginQuery = db()->prepare('SELECT last_login_at FROM users WHERE id = ?');
$lastLoginQuery->execute([$user['id']]);
$lastLogin = $lastLoginQuery->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Student Dashboard — CHARUSAT</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .dash-info-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px;
      margin-bottom: 20px;
    }
    .security-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 0.8rem;
      font-weight: 700;
      background: #e0f2fe;
      color: #0369a1;
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container">
      <h1 class="brand">CHARUSAT Student</h1>
      <nav>
        <a href="index.html">Home</a>
        <a href="events.html">Events</a>
        <a href="students.html">Students Directory</a>
        <a href="student-dashboard.php" class="active">Dashboard</a>
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
          <h2>Welcome, <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>!</h2>
          <p class="subtitle">Practical 10: Role-Based Dashboard for Role: <span class="tag-pill" style="background:#e0e7ff; color:#3730a3; font-weight:bold;">STUDENT</span></p>
        </div>
        <span class="security-badge">🔒 Session Active (15m Idle Limit)</span>
      </div>
    </div>

    <!-- Practical 10 Authentication & Session Metadata -->
    <section class="card mb-20" style="border-left: 5px solid var(--accent);">
      <h3>🔐 Active Session &amp; Security Profile</h3>
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 12px;">
        <div class="dash-info-card">
          <p style="margin:0; font-size:0.85rem; color:var(--muted);">Signed In Account</p>
          <h4 style="margin:4px 0 0;"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></h4>
        </div>
        <div class="dash-info-card">
          <p style="margin:0; font-size:0.85rem; color:var(--muted);">Assigned Role</p>
          <h4 style="margin:4px 0 0;">Student (Read-Only)</h4>
        </div>
        <div class="dash-info-card">
          <p style="margin:0; font-size:0.85rem; color:var(--muted);">Last Login Timestamp (Intermediate Ext)</p>
          <h4 style="margin:4px 0 0;"><?= htmlspecialchars($lastLogin ?: 'Just now', ENT_QUOTES, 'UTF-8') ?></h4>
        </div>
        <div class="dash-info-card">
          <p style="margin:0; font-size:0.85rem; color:var(--muted);">Session Inactivity Timeout</p>
          <h4 style="margin:4px 0 0;">15 Minutes (900 sec)</h4>
        </div>
      </div>
    </section>

    <!-- Practical 10 Submission Screenshot Helpers -->
    <section class="card mb-20" style="background: rgba(37, 99, 235, 0.04); border-color: rgba(37, 99, 235, 0.2);">
      <h3>📸 Practical 10 Lab Submission Screenshots Helper</h3>
      <p style="color:var(--muted); font-size:0.9rem; margin-top:4px;">Use the actions below to easily produce the required evaluation states for Practical 10:</p>
      
      <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 14px;">
        <!-- 1. Screenshot of Login Success (Current Page) -->
        <button class="btn btn-sm" onclick="window.scrollTo({top:0, behavior:'smooth'})">1. Login Success (Screenshot This Page)</button>
        
        <!-- 2. Test Unauthorized Access Attempt -->
        <a href="admin-dashboard.php" class="btn btn-outline btn-sm" style="color:#b91c1c; border-color:#fca5a5;">2. Test Unauthorized Access (Attempt Admin Dashboard) &rarr;</a>
        
        <!-- 3. Test Session Timeout -->
        <a href="student-dashboard.php?test_timeout=1" class="btn btn-outline btn-sm" style="color:#b45309; border-color:#fde68a;">3. Simulate Session Timeout &rarr;</a>
        
        <!-- 4. Logout -->
        <form action="logout.php" method="post" style="display:inline;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <button class="btn btn-outline btn-sm" type="submit">4. Secure Logout &rarr;</button>
        </form>
      </div>
    </section>

    <!-- Quick Navigation Links -->
    <section class="card">
      <h3>Academic Student Resources</h3>
      <p>As a registered student, you have access to campus events, dynamic knowledgebase, and the student directory:</p>
      <div style="display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap;">
        <a href="students.html" class="btn">Browse Student Directory &rarr;</a>
        <a href="events.html" class="btn btn-outline">Explore Campus Events &rarr;</a>
        <a href="faq.html" class="btn btn-outline">University FAQs &rarr;</a>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>Practical 10: Secure Role-Based Authentication &amp; Session Management • CHARUSAT</p>
    </div>
  </footer>
</body>
</html>
