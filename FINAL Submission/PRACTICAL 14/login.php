<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    try {
        $pdo = getDatabaseConnection();
        $statement = $pdo->prepare(
            'SELECT id, name, email, password_hash, role FROM users WHERE email = :email AND status = "active"'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            recordAudit($pdo, (int) $user['id'], 'login', 'User signed in');
            header('Location: dashboard.php');
            exit;
        }

        $errorMessage = 'Email or password is incorrect, or the account is inactive.';
    } catch (PDOException $error) {
        $errorMessage = 'Could not connect to the database. Run setup.php and check config.php.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width: 480px;">
    <section class="card shadow-sm">
        <div class="card-body p-4">
            <h1 class="h3 mb-3">Admin Dashboard</h1>
            <p class="text-secondary">Sign in with one of the sample accounts.</p>
            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-danger"><?= escape($errorMessage) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" type="email" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" type="password" id="password" name="password" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Sign in</button>
            </form>
        </div>
    </section>
</main>
</body>
</html>
