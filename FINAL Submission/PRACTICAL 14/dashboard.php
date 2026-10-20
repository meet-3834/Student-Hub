<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireLogin();

$pdo = getDatabaseConnection();
$menuStatement = $pdo->prepare(
    'SELECT label, url FROM menu_items WHERE role = :role ORDER BY sort_order, id'
);
$menuStatement->execute(['role' => $_SESSION['role']]);
$menuItems = $menuStatement->fetchAll();
$isAdmin = $_SESSION['role'] === 'admin';

$stats = [];
$latestRegistrations = [];
$latestAuditLogs = [];
$users = [];

if ($isAdmin) {
    recordAudit($pdo, (int) $_SESSION['user_id'], 'view_dashboard', 'Viewed admin dashboard');

    $stats = [
        'Users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'Students' => (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn(),
        'Events' => (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn(),
        'Registrations' => (int) $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn(),
    ];

    $latestRegistrations = $pdo->query(
        'SELECT s.name AS student_name, e.name AS event_name, r.registered_at
         FROM registrations r
         JOIN students s ON s.id = r.student_id
         JOIN events e ON e.id = r.event_id
         ORDER BY r.registered_at DESC
         LIMIT 5'
    )->fetchAll();

    $users = $pdo->query(
        'SELECT name, email, role, status FROM users ORDER BY created_at DESC LIMIT 10'
    )->fetchAll();

    $latestAuditLogs = $pdo->query(
        'SELECT a.action, a.details, a.created_at, u.name AS user_name
         FROM audit_logs a
         LEFT JOIN users u ON u.id = a.user_id
         ORDER BY a.created_at DESC
         LIMIT 10'
    )->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Practical 14</a>
        <div class="navbar-nav me-auto">
            <?php foreach ($menuItems as $menuItem): ?>
                <a class="nav-link" href="<?= escape($menuItem['url']) ?>"><?= escape($menuItem['label']) ?></a>
            <?php endforeach; ?>
        </div>
        <span class="navbar-text me-3"><?= escape($_SESSION['name']) ?> (<?= escape($_SESSION['role']) ?>)</span>
        <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
    </div>
</nav>

<main class="container py-4">
    <?php if (!$isAdmin): ?>
        <section class="alert alert-warning" id="overview">
            <h1 class="h4">Admin access only</h1>
            <p class="mb-0">Your role is <?= escape($_SESSION['role']) ?>. The admin dashboard is only available to administrators.</p>
        </section>
        <section class="card" id="profile">
            <div class="card-body">
                <h2 class="h5">Your account</h2>
                <p class="mb-1"><?= escape($_SESSION['name']) ?></p>
                <p class="mb-0"><?= escape($_SESSION['email']) ?></p>
            </div>
        </section>
    <?php else: ?>
        <h1 class="h3 mb-4" id="overview">Admin Dashboard</h1>

        <div class="row g-3 mb-4">
            <?php foreach ($stats as $label => $value): ?>
                <div class="col-6 col-lg-3">
                    <section class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-secondary"><?= escape($label) ?></div>
                            <div class="display-6"><?= $value ?></div>
                        </div>
                    </section>
                </div>
            <?php endforeach; ?>
        </div>

        <section class="card shadow-sm mb-4" id="users">
            <div class="card-body">
                <h2 class="h5">Users</h2>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= escape($user['name']) ?></td>
                                <td><?= escape($user['email']) ?></td>
                                <td><?= escape($user['role']) ?></td>
                                <td><?= escape($user['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Latest Registrations</h2>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>Student</th><th>Event</th><th>Registered</th></tr></thead>
                        <tbody>
                        <?php foreach ($latestRegistrations as $registration): ?>
                            <tr>
                                <td><?= escape($registration['student_name']) ?></td>
                                <td><?= escape($registration['event_name']) ?></td>
                                <td><?= escape($registration['registered_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($latestRegistrations === []): ?>
                            <tr><td colspan="3">No registrations yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="card shadow-sm" id="audit">
            <div class="card-body">
                <h2 class="h5">Latest Audit Log Entries</h2>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>User</th><th>Action</th><th>Details</th><th>Time</th></tr></thead>
                        <tbody>
                        <?php foreach ($latestAuditLogs as $log): ?>
                            <tr>
                                <td><?= escape($log['user_name'] ?? 'Deleted user') ?></td>
                                <td><?= escape($log['action']) ?></td>
                                <td><?= escape($log['details']) ?></td>
                                <td><?= escape($log['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
