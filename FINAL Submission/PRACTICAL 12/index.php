<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/config.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function db(): PDO
{
    static $connection;
    if (!$connection) {
        $connection = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $connection;
}

function redirect(): void
{
    header('Location: index.php');
    exit;
}

function flash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function validateCsrf(): void
{
    if (!isset($_POST['csrf']) || !hash_equals(csrfToken(), (string) $_POST['csrf'])) {
        throw new RuntimeException('Your session expired. Refresh the page and try again.');
    }
}

function uploadPoster(?string $currentPoster = null): ?string
{
    if (!isset($_FILES['poster']) || $_FILES['poster']['error'] === UPLOAD_ERR_NO_FILE) {
        return $currentPoster;
    }

    $file = $_FILES['poster'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The poster upload failed.');
    }
    if ($file['size'] > MAX_POSTER_SIZE) {
        throw new RuntimeException('Poster must be 2 MB or smaller.');
    }

    $mimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($mimeTypes[$mime])) {
        throw new RuntimeException('Poster must be a JPG, PNG, or WEBP image.');
    }

    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true) && !is_dir(UPLOAD_DIR)) {
        throw new RuntimeException('The poster upload directory could not be created.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $mimeTypes[$mime];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('The poster could not be saved.');
    }
    return $filename;
}

function removePoster(?string $filename): void
{
    if ($filename === null || $filename === '') {
        return;
    }
    $path = UPLOAD_DIR . DIRECTORY_SEPARATOR . basename($filename);
    if (is_file($path) && !unlink($path)) {
        throw new RuntimeException('The event was saved, but its old poster could not be removed.');
    }
}

$error = '';
$notice = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$pdo = null;

try {
    $pdo = db();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        validateCsrf();
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'login') {
            $password = (string) ($_POST['password'] ?? '');
            if (ADMIN_PASSWORD === '' || !hash_equals(ADMIN_USERNAME, (string) ($_POST['username'] ?? ''))
                || !hash_equals(ADMIN_PASSWORD, $password)) {
                throw new RuntimeException('Incorrect username or password. Set ADMIN_PASSWORD in config.php first.');
            }
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            flash('Signed in.');
            redirect();
        }

        if ($action === 'logout') {
            unset($_SESSION['admin']);
            session_regenerate_id(true);
            flash('Signed out.');
            redirect();
        }

        if (empty($_SESSION['admin'])) {
            throw new RuntimeException('Please sign in to manage events.');
        }

        if ($action === 'save') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
            $title = trim((string) ($_POST['title'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            $category = trim((string) ($_POST['category'] ?? ''));
            $eventDate = (string) ($_POST['event_date'] ?? '');
            $status = (string) ($_POST['status'] ?? '');
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $eventDate);

            if ($title === '' || mb_strlen($title) > 150 || $description === ''
                || $category === '' || mb_strlen($category) > 80
                || !$date || $date->format('Y-m-d') !== $eventDate
                || !in_array($status, ['open', 'closed'], true)) {
                throw new RuntimeException('Enter a title, description, valid date, category, and event status.');
            }

            $current = null;
            if ($id !== null) {
                $find = $pdo->prepare('SELECT poster FROM events WHERE id = ?');
                $find->execute([$id]);
                $current = $find->fetchColumn();
                if ($current === false) {
                    throw new RuntimeException('Event not found.');
                }
            }

            $newPoster = uploadPoster($current ?: null);
            try {
                if ($id === null) {
                    $query = $pdo->prepare(
                        'INSERT INTO events (title, description, event_date, category, status, poster)
                         VALUES (?, ?, ?, ?, ?, ?)'
                    );
                    $query->execute([$title, $description, $eventDate, $category, $status, $newPoster]);
                } else {
                    $query = $pdo->prepare(
                        'UPDATE events SET title = ?, description = ?, event_date = ?, category = ?, status = ?, poster = ?
                         WHERE id = ?'
                    );
                    $query->execute([$title, $description, $eventDate, $category, $status, $newPoster, $id]);
                }
            } catch (Throwable $exception) {
                if ($newPoster !== $current && $newPoster !== null) {
                    removePoster($newPoster);
                }
                throw $exception;
            }
            if ($id !== null && $newPoster !== $current) {
                removePoster($current ?: null);
            }
            flash($id === null ? 'Event added.' : 'Event updated.');
            redirect();
        }

        if ($action === 'delete') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new RuntimeException('Invalid event.');
            }
            $find = $pdo->prepare('SELECT poster FROM events WHERE id = ?');
            $find->execute([$id]);
            $poster = $find->fetchColumn();
            if ($poster === false) {
                throw new RuntimeException('Event not found.');
            }
            $delete = $pdo->prepare('DELETE FROM events WHERE id = ?');
            $delete->execute([$id]);
            removePoster($poster ?: null);
            flash('Event deleted.');
            redirect();
        }
    }
} catch (Throwable $exception) {
    $error = $exception->getMessage();
}

$events = [];
$editEvent = null;
$categories = [];
$filters = ['status' => (string) ($_GET['status'] ?? ''), 'category' => (string) ($_GET['category'] ?? '')];
if ($pdo && !empty($_SESSION['admin'])) {
    try {
        $categories = $pdo->query('SELECT DISTINCT category FROM events ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
        $where = [];
        $values = [];
        if (in_array($filters['status'], ['open', 'closed'], true)) {
            $where[] = 'status = ?';
            $values[] = $filters['status'];
        }
        if ($filters['category'] !== '') {
            $where[] = 'category = ?';
            $values[] = $filters['category'];
        }
        $sql = 'SELECT * FROM events' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY event_date';
        $query = $pdo->prepare($sql);
        $query->execute($values);
        $events = $query->fetchAll(PDO::FETCH_ASSOC);

        $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
        if ($editId) {
            $query = $pdo->prepare('SELECT * FROM events WHERE id = ?');
            $query->execute([$editId]);
            $editEvent = $query->fetch(PDO::FETCH_ASSOC) ?: null;
            if ($editEvent === null) {
                $error = 'Event not found.';
            }
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$admin = !empty($_SESSION['admin']);
$form = $editEvent ?: ['id' => '', 'title' => '', 'description' => '', 'event_date' => '', 'category' => '', 'status' => 'open', 'poster' => null];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Event Manager</h1>
    <?php if ($admin): ?>
        <form method="post" class="inline">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="logout">
            <button type="submit">Sign out</button>
        </form>
    <?php endif; ?>
</header>

<?php if ($notice !== ''): ?><p class="message"><?= e($notice) ?></p><?php endif; ?>
<?php if ($error !== ''): ?><p class="message error"><?= e($error) ?></p><?php endif; ?>

<?php if (!$admin): ?>
    <h2>Admin sign in</h2>
    <p>Set a private ADMIN_PASSWORD in <code>config.php</code> before signing in.</p>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="login">
        <label>Username <input name="username" required autocomplete="username"></label>
        <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
        <button type="submit">Sign in</button>
    </form>
<?php else: ?>
    <h2><?= $editEvent ? 'Update event' : 'Add event' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e((string) $form['id']) ?>">
        <label>Title <input name="title" maxlength="150" required value="<?= e((string) $form['title']) ?>"></label>
        <label>Description <textarea name="description" required rows="3"><?= e((string) $form['description']) ?></textarea></label>
        <label>Date <input type="date" name="event_date" required value="<?= e((string) $form['event_date']) ?>"></label>
        <label>Category <input name="category" maxlength="80" required value="<?= e((string) $form['category']) ?>"></label>
        <label>Status
            <select name="status">
                <option value="open" <?= $form['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                <option value="closed" <?= $form['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
        </label>
        <label>Poster (JPG, PNG, or WEBP; max 2 MB)
            <input type="file" name="poster" accept="image/jpeg,image/png,image/webp">
        </label>
        <?php if (!empty($form['poster'])): ?>
            <p>Current poster: <img class="poster" src="<?= e(UPLOAD_URL . '/' . rawurlencode((string) $form['poster'])) ?>" alt="Current event poster"></p>
        <?php endif; ?>
        <button type="submit"><?= $editEvent ? 'Save changes' : 'Add event' ?></button>
        <?php if ($editEvent): ?> <a href="index.php">Cancel</a><?php endif; ?>
    </form>

    <h2>Events</h2>
    <form method="get">
        <label>Status
            <select name="status">
                <option value="">All</option>
                <option value="open" <?= $filters['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                <option value="closed" <?= $filters['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
        </label>
        <label>Category
            <select name="category">
                <option value="">All</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e((string) $category) ?>" <?= $filters['category'] === $category ? 'selected' : '' ?>><?= e((string) $category) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Filter</button> <a href="index.php">Clear</a>
    </form>

    <table>
        <thead><tr><th>Event</th><th>Date</th><th>Category</th><th>Status</th><th>Poster</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($events as $event): ?>
            <tr>
                <td><strong><?= e($event['title']) ?></strong><br><?= nl2br(e($event['description'])) ?></td>
                <td><?= e($event['event_date']) ?></td>
                <td><?= e($event['category']) ?></td>
                <td><?= e(ucfirst($event['status'])) ?></td>
                <td><?php if ($event['poster']): ?><img class="poster" src="<?= e(UPLOAD_URL . '/' . rawurlencode($event['poster'])) ?>" alt="Event poster"><?php else: ?>—<?php endif; ?></td>
                <td>
                    <a href="?edit=<?= (int) $event['id'] ?>">Edit</a>
                    <form method="post" class="inline" onsubmit="return confirm('Delete this event?')">
                        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $event['id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$events): ?><tr><td colspan="6">No events found.</td></tr><?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>
</body>
</html>
