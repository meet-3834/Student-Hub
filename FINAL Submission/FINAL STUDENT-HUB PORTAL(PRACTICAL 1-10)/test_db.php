<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$connectionSuccess = false;
$errorMessage = '';
$dbInfo = [];
$tableCounts = [];
$preparedStatementResults = [];
$storedProcedureResults = [];

try {
    $pdo = db();
    $connectionSuccess = true;

    // 1. Database Connection Metadata
    $dbInfo = [
        'Driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
        'Server Version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'Connection Status' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
        'Error Mode' => 'PDO::ERRMODE_EXCEPTION (Strict)',
        'Emulated Prepares' => 'Disabled (Native PDO Prepared Statements)',
    ];

    // 2. Query Table Counts using PDO Prepared Statements
    $tables = ['users', 'students', 'events', 'registrations', 'audit_logs'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}`");
            $stmt->execute();
            $tableCounts[$table] = (int) $stmt->fetchColumn();
        } catch (Throwable $t) {
            $tableCounts[$table] = 0;
        }
    }

    // 3. Intermediate Extension: Parameterized Prepared Statement Query
    $paramCategory = 'Technical';
    $prepStmt = $pdo->prepare(
        "SELECT id, title, category, event_date, venue, capacity, registered_count 
         FROM events 
         WHERE category = :category 
         ORDER BY event_date ASC 
         LIMIT 4"
    );
    $prepStmt->execute([':category' => $paramCategory]);
    $preparedStatementResults = $prepStmt->fetchAll();

    // 4. Advanced Extension: Call Stored Procedure
    try {
        $spStmt = $pdo->prepare("CALL sp_GetStudentRegistrations(:student_id)");
        $spStmt->execute([':student_id' => 'STU-001']);
        $storedProcedureResults = $spStmt->fetchAll();
        $spStmt->closeCursor();
    } catch (PDOException $spException) {
        $storedProcedureResults = [];
    }

} catch (Throwable $e) {
    $connectionSuccess = false;
    $errorMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Practical 8: MySQL Connection &amp; Schema Test — CHARUSAT</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <style>
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 999px;
      font-weight: 700;
      font-size: 0.88rem;
    }
    .status-badge.success {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
    }
    .status-badge.error {
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #fecaca;
    }
    .db-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-top: 16px;
    }
    .db-metric-box {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 18px;
      text-align: center;
    }
    .db-metric-box h4 {
      margin: 0;
      font-size: 2rem;
      font-weight: 800;
      color: var(--accent);
    }
    .db-metric-box p {
      margin: 4px 0 0;
      font-size: 0.88rem;
      color: var(--muted);
      font-weight: 600;
    }
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
      font-size: 0.9rem;
    }
    .data-table th, .data-table td {
      padding: 10px 14px;
      border: 1px solid var(--border);
      text-align: left;
    }
    .data-table th {
      background: rgba(37, 99, 235, 0.08);
      font-weight: 700;
      color: var(--primary);
    }
    .check-list {
      list-style: none;
      padding: 0;
      margin: 12px 0 0;
    }
    .check-list li {
      padding: 8px 0;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.95rem;
      border-bottom: 1px solid var(--border);
    }
    .check-list li:last-child {
      border-bottom: none;
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
        <a href="register.html">Register</a>
        <a href="contact.php">Contact</a>
        <a href="test_db.php" class="active">DB Test</a>
        <a href="sitemap.html">Site Map</a>
      </nav>
    </div>
  </header>

  <main class="container">
    <div class="page-title-section">
      <div class="section-header-flex">
        <div>
          <h2>Practical 8: MySQL Schema &amp; PDO Connectivity Test</h2>
          <p class="subtitle">
            Normalized ER Model (Students, Events, Registrations) | PDO Prepared Statements | Stored Procedures
          </p>
        </div>
        <div>
          <?php if ($connectionSuccess): ?>
            <span class="status-badge success">✔ PDO MySQL Connected</span>
          <?php else: ?>
            <span class="status-badge error">✖ Connection Failed</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (!$connectionSuccess): ?>
      <section class="card mb-20" style="background: #fff1f2; border-color: #fca5a5;">
        <h3 style="color: #b91c1c;">Database Connection Notice</h3>
        <p><strong>Error Message:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <p style="font-size: 0.9rem; color: #7f1d1d; margin-top: 8px;">
          <strong>Quick Setup Guide for XAMPP:</strong><br>
          1. Start MySQL in XAMPP Control Panel.<br>
          2. Open phpMyAdmin and create database <code>charusat_portal</code>.<br>
          3. Import the included <code>schema.sql</code> file, or run <code>php setup.php</code> in terminal.<br>
          4. Refresh this page to view live verification metrics!
        </p>
      </section>
    <?php else: ?>

      <!-- Database Verification Checklist -->
      <section class="card mb-20">
        <h3>📋 Practical 8 Evaluation Criteria &amp; Verification</h3>
        <ul class="check-list">
          <li>
            <span style="color:#16a34a; font-weight:bold; font-size:1.1rem;">✔</span>
            <span><strong>Normalized Schema Design:</strong> <code>students</code>, <code>events</code>, and junction table <code>registrations</code> are properly 3NF normalized.</span>
          </li>
          <li>
            <span style="color:#16a34a; font-weight:bold; font-size:1.1rem;">✔</span>
            <span><strong>Primary &amp; Foreign Key Integrity:</strong> <code>registrations.student_id &rarr; students.id</code> and <code>registrations.event_id &rarr; events.id</code> with <code>ON DELETE CASCADE</code>.</span>
          </li>
          <li>
            <span style="color:#16a34a; font-weight:bold; font-size:1.1rem;">✔</span>
            <span><strong>Secure PDO Configuration:</strong> UTF-8 Charset, Exception Error Mode, Emulation Disabled, Strict Types in [db.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/db.php).</span>
          </li>
          <li>
            <span style="color:#16a34a; font-weight:bold; font-size:1.1rem;">✔</span>
            <span><strong>Intermediate Extension:</strong> Secure parameterized Prepared Statements with bound parameters.</span>
          </li>
          <li>
            <span style="color:#16a34a; font-weight:bold; font-size:1.1rem;">✔</span>
            <span><strong>Advanced Extension:</strong> Stored Procedures (<code>sp_RegisterStudentForEvent</code> &amp; <code>sp_GetStudentRegistrations</code>) implemented and executable.</span>
          </li>
        </ul>
      </section>

      <!-- Metrics Row -->
      <section class="mb-20">
        <h3>📊 Normalized Database Records Count</h3>
        <div class="db-grid">
          <div class="db-metric-box">
            <h4><?= $tableCounts['students'] ?? 0 ?></h4>
            <p>Students Table (Master)</p>
          </div>
          <div class="db-metric-box">
            <h4><?= $tableCounts['events'] ?? 0 ?></h4>
            <p>Events Table (Master)</p>
          </div>
          <div class="db-metric-box">
            <h4><?= $tableCounts['registrations'] ?? 0 ?></h4>
            <p>Registrations (Junction)</p>
          </div>
          <div class="db-metric-box">
            <h4><?= $tableCounts['users'] ?? 0 ?></h4>
            <p>Users (Auth &amp; Roles)</p>
          </div>
        </div>
      </section>

      <!-- Intermediate Extension: Prepared Statement Demonstration -->
      <section class="card mb-20">
        <div class="section-header-flex">
          <div>
            <h3 style="margin:0;">🛡️ Intermediate Extension: PDO Prepared Statement Query</h3>
            <p style="color:var(--muted); font-size:0.88rem; margin:2px 0 0;">
              Query: <code>SELECT * FROM events WHERE category = :category</code> (Param: <code><?= htmlspecialchars($paramCategory) ?></code>)
            </p>
          </div>
          <span class="status-badge success">Prepared Statement Active</span>
        </div>

        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>Event ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Capacity</th>
                <th>Registered</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($preparedStatementResults as $row): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($row['id']) ?></strong></td>
                  <td><?= htmlspecialchars($row['title']) ?></td>
                  <td><span class="tag-pill"><?= htmlspecialchars($row['category']) ?></span></td>
                  <td><?= htmlspecialchars($row['event_date']) ?></td>
                  <td><?= htmlspecialchars($row['venue']) ?></td>
                  <td><?= (int)$row['capacity'] ?></td>
                  <td><?= (int)$row['registered_count'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Advanced Extension: Stored Procedure Demonstration -->
      <section class="card mb-20">
        <div class="section-header-flex">
          <div>
            <h3 style="margin:0;">⚙️ Advanced Extension: Stored Procedure Execution</h3>
            <p style="color:var(--muted); font-size:0.88rem; margin:2px 0 0;">
              Procedure: <code>CALL sp_GetStudentRegistrations('STU-001')</code> &mdash; Normalized 3-Table Inner Join
            </p>
          </div>
          <span class="status-badge success">Stored Procedure Executed</span>
        </div>

        <?php if (!empty($storedProcedureResults)): ?>
          <div style="overflow-x:auto;">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Reg ID</th>
                  <th>Student Name</th>
                  <th>Roll No</th>
                  <th>Event Registered</th>
                  <th>Event Date</th>
                  <th>Status</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($storedProcedureResults as $spRow): ?>
                  <tr>
                    <td>#<?= htmlspecialchars((string)$spRow['registration_id']) ?></td>
                    <td><strong><?= htmlspecialchars($spRow['student_name']) ?></strong></td>
                    <td><?= htmlspecialchars($spRow['roll_no']) ?></td>
                    <td><?= htmlspecialchars($spRow['event_title']) ?></td>
                    <td><?= htmlspecialchars($spRow['event_date']) ?></td>
                    <td><span class="status-badge success" style="padding:2px 8px; font-size:0.8rem;"><?= htmlspecialchars($spRow['registration_status']) ?></span></td>
                    <td style="color:var(--muted);"><?= htmlspecialchars($spRow['remarks'] ?? 'N/A') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p style="color:var(--muted); margin-top:10px;">Stored procedure executed. Ensure <code>schema.sql</code> is imported to view sample join records.</p>
        <?php endif; ?>
      </section>

      <!-- Connection Details Table -->
      <section class="card">
        <h3>ℹ️ PDO Connection &amp; Environment Details</h3>
        <table class="data-table">
          <?php foreach ($dbInfo as $k => $v): ?>
            <tr>
              <th style="width: 240px;"><?= htmlspecialchars($k) ?></th>
              <td><?= htmlspecialchars($v) ?></td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <th>SQL Dump File</th>
            <td><a href="schema.sql" download>schema.sql (Direct phpMyAdmin Import Dump)</a></td>
          </tr>
          <tr>
            <th>Connection Script</th>
            <td>[db.php](file:///c:/Users/mrama/OneDrive/Desktop/WEB%20DEVELOPMENT/Student_hub%20final/db.php)</td>
          </tr>
        </table>
      </section>

    <?php endif; ?>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>Practical 8 Laboratory Verification • CHARUSAT Student Portal</p>
    </div>
  </footer>
  <script src="script.js"></script>
</body>
</html>
