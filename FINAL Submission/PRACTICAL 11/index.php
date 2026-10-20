<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$dbError = '';

try {
    $conn = new mysqli('localhost', 'root', '', 'student_management');
    $conn->set_charset('utf8mb4');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $course = trim($_POST['course'] ?? '');

            if ($name === '' || $course === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Enter a name, a valid email address, and a course.';
            } elseif ($id > 0) {
                $stmt = $conn->prepare('UPDATE students SET name = ?, email = ?, course = ? WHERE id = ?');
                $stmt->bind_param('sssi', $name, $email, $course, $id);
                $stmt->execute();
                $_SESSION['message'] = $stmt->affected_rows > 0 ? 'Student updated successfully.' : 'No changes were made.';
                header('Location: index.php');
                exit;
            } else {
                $stmt = $conn->prepare('INSERT INTO students (name, email, course) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $name, $email, $course);
                $stmt->execute();
                $_SESSION['message'] = 'Student added successfully.';
                header('Location: index.php');
                exit;
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $conn->prepare('DELETE FROM students WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $_SESSION['message'] = $stmt->affected_rows > 0 ? 'Student deleted successfully.' : 'Student not found.';
            header('Location: index.php');
            exit;
        }
    }

    $search = trim($_GET['search'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 10;

    if (isset($_GET['export'])) {
        $stmt = $conn->prepare('SELECT name, email, course FROM students WHERE name LIKE ? OR email LIKE ? OR course LIKE ? ORDER BY name');
        $like = '%' . $search . '%';
        $stmt->bind_param('sss', $like, $like, $like);
        $stmt->execute();
        $students = $stmt->get_result();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="students.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Email', 'Course']);
        while ($student = $students->fetch_assoc()) {
            fputcsv($output, $student);
        }
        fclose($output);
        exit;
    }

    $countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM students WHERE name LIKE ? OR email LIKE ? OR course LIKE ?');
    $like = '%' . $search . '%';
    $countStmt->bind_param('sss', $like, $like, $like);
    $countStmt->execute();
    $total = (int)$countStmt->get_result()->fetch_assoc()['total'];
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $stmt = $conn->prepare('SELECT id, name, email, course FROM students WHERE name LIKE ? OR email LIKE ? OR course LIKE ? ORDER BY name LIMIT ? OFFSET ?');
    $stmt->bind_param('sssii', $like, $like, $like, $perPage, $offset);
    $stmt->execute();
    $students = $stmt->get_result();

    $editStudent = null;
    if (isset($_GET['edit'])) {
        $editId = (int)$_GET['edit'];
        $editStmt = $conn->prepare('SELECT id, name, email, course FROM students WHERE id = ?');
        $editStmt->bind_param('i', $editId);
        $editStmt->execute();
        $editStudent = $editStmt->get_result()->fetch_assoc();
        if (!$editStudent) {
            $message = 'Student not found.';
        }
    }
} catch (mysqli_sql_exception $e) {
    $dbError = 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <h1>Student Management</h1>

        <?php if ($dbError !== ''): ?>
            <p class="message"><?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?></p>
            <p>Import <strong>database.sql</strong> in phpMyAdmin, then refresh this page.</p>
        <?php else: ?>
            <?php if ($message !== ''): ?>
                <p class="message"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <h2><?= $editStudent ? 'Edit Student' : 'Add Student' ?></h2>
            <form method="post">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int)($editStudent['id'] ?? 0) ?>">
                <input name="name" placeholder="Name" required value="<?= htmlspecialchars($editStudent['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input name="email" type="email" placeholder="Email" required value="<?= htmlspecialchars($editStudent['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input name="course" placeholder="Course" required value="<?= htmlspecialchars($editStudent['course'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit"><?= $editStudent ? 'Update Student' : 'Add Student' ?></button>
                <?php if ($editStudent): ?>
                    <a href="index.php">Cancel</a>
                <?php endif; ?>
            </form>

            <h2>Students</h2>
            <form method="get">
                <input name="search" placeholder="Search name, email, or course" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">Search</button>
                <a href="index.php">Clear</a>
                <a href="?search=<?= urlencode($search) ?>&amp;export=1">Export CSV</a>
            </form>

            <table>
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Course</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['course'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="actions">
                                <a href="?edit=<?= (int)$student['id'] ?>">Edit</a>
                                <form method="post" onsubmit="return confirm('Delete this student?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$student['id'] ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($total === 0): ?>
                        <tr><td colspan="4">No students found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <p>Page <?= $page ?> of <?= $totalPages ?> (<?= $total ?> students)</p>
            <?php if ($page > 1): ?>
                <a href="?search=<?= urlencode($search) ?>&amp;page=<?= $page - 1 ?>">Previous</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?search=<?= urlencode($search) ?>&amp;page=<?= $page + 1 ?>">Next</a>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>
