<?php
header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body);
    exit;
}

try {
    require __DIR__ . '/db.php';

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $students = $pdo->query('SELECT id, name, email FROM students ORDER BY id DESC')
            ->fetchAll();

        respond(200, ['success' => true, 'data' => $students]);
    }

    if ($method !== 'POST') {
        respond(405, ['success' => false, 'message' => 'Use GET or POST.']);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        respond(400, ['success' => false, 'message' => 'Please send valid JSON.']);
    }

    $action = $input['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        $nameInput = $input['name'] ?? '';
        $emailInput = $input['email'] ?? '';

        if (!is_string($nameInput) || !is_string($emailInput)) {
            respond(400, ['success' => false, 'message' => 'Name and email must be text.']);
        }

        $name = trim($nameInput);
        $email = trim($emailInput);

        if ($name === '' || $email === '') {
            respond(400, ['success' => false, 'message' => 'Name and email are required.']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            respond(400, ['success' => false, 'message' => 'Enter a valid email address.']);
        }

        if ($action === 'add') {
            $statement = $pdo->prepare('INSERT INTO students (name, email) VALUES (?, ?)');
            $statement->execute([$name, $email]);
            respond(201, ['success' => true, 'message' => 'Student added.']);
        }

        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            respond(400, ['success' => false, 'message' => 'A valid student ID is required.']);
        }

        $statement = $pdo->prepare('UPDATE students SET name = ?, email = ? WHERE id = ?');
        $statement->execute([$name, $email, $id]);
        $check = $pdo->prepare('SELECT id FROM students WHERE id = ?');
        $check->execute([$id]);

        if (!$check->fetch()) {
            respond(404, ['success' => false, 'message' => 'Student not found.']);
        }

        respond(200, ['success' => true, 'message' => 'Student updated.']);
    }

    if ($action === 'delete') {
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            respond(400, ['success' => false, 'message' => 'A valid student ID is required.']);
        }

        $statement = $pdo->prepare('DELETE FROM students WHERE id = ?');
        $statement->execute([$id]);

        if ($statement->rowCount() === 0) {
            respond(404, ['success' => false, 'message' => 'Student not found.']);
        }

        respond(200, ['success' => true, 'message' => 'Student deleted.']);
    }

    respond(400, ['success' => false, 'message' => 'Unknown action.']);
} catch (PDOException $error) {
    error_log($error->getMessage());
    if ($error->getCode() === '23000') {
        respond(409, ['success' => false, 'message' => 'That email address is already in use.']);
    }

    respond(500, ['success' => false, 'message' => 'A database error occurred. Check your database setup.']);
}
