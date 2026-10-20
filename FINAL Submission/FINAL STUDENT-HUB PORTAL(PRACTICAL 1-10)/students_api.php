<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

$user = currentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Please log in to view student records.', 'login' => 'login.php']);
    exit;
}

function jsonResponse(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function readStudentInput(): array
{
    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) {
        jsonResponse(400, ['error' => 'Invalid JSON request body.']);
    }
    $fields = ['rollNo', 'name', 'department', 'semester', 'cgpa', 'email', 'country', 'state', 'city', 'skills', 'status'];
    foreach ($fields as $field) {
        if (!array_key_exists($field, $input)) {
            jsonResponse(422, ['error' => 'Missing required field: ' . $field]);
        }
    }
    $maxLengths = ['rollNo' => 32, 'name' => 120, 'department' => 120, 'email' => 190, 'country' => 80, 'state' => 80, 'city' => 80, 'status' => 24];
    foreach ($maxLengths as $field => $maxLength) {
        if (!is_string($input[$field]) || trim($input[$field]) === '' || mb_strlen(trim($input[$field])) > $maxLength) {
            jsonResponse(422, ['error' => 'Invalid value for ' . $field . '.']);
        }
    }
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)
        || filter_var($input['semester'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]) === false
        || !is_numeric($input['cgpa']) || (float) $input['cgpa'] < 0 || (float) $input['cgpa'] > 10
        || !is_array($input['skills']) || count($input['skills']) > 30) {
        jsonResponse(422, ['error' => 'Invalid email, semester, CGPA, or skills.']);
    }
    foreach ($input['skills'] as $skill) {
        if (!is_string($skill) || mb_strlen($skill) > 60) {
            jsonResponse(422, ['error' => 'Each skill must be text of at most 60 characters.']);
        }
    }
    $input['skills'] = array_values(array_map('trim', $input['skills']));
    $input['semester'] = (int) $input['semester'];
    $input['cgpa'] = (float) $input['cgpa'];
    return $input;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = db()->query('SELECT id, roll_no, name, department, semester, cgpa, email, country, state, city, skills, avatar_initials, avatar_color, status FROM students ORDER BY name')->fetchAll();
    $students = array_map(static function (array $row): array {
        return [
            'id' => $row['id'],
            'rollNo' => $row['roll_no'],
            'name' => $row['name'],
            'department' => $row['department'],
            'semester' => (int) $row['semester'],
            'cgpa' => (float) $row['cgpa'],
            'email' => $row['email'],
            'country' => $row['country'],
            'state' => $row['state'],
            'city' => $row['city'],
            'skills' => json_decode($row['skills'], true, 512, JSON_THROW_ON_ERROR),
            'avatarInitials' => $row['avatar_initials'],
            'avatarColor' => $row['avatar_color'],
            'status' => $row['status'],
        ];
    }, $rows);
    echo json_encode(['students' => $students, 'role' => $user['role'], 'csrfToken' => csrfToken()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    jsonResponse(405, ['error' => 'Method not allowed.']);
}
if ($user['role'] !== 'admin') {
    jsonResponse(403, ['error' => 'Only administrators can add or edit student records.']);
}
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!verifyCsrfToken($csrf)) {
    jsonResponse(400, ['error' => 'Invalid or expired security token. Refresh and try again.']);
}

$input = readStudentInput();
$id = trim((string) ($_GET['id'] ?? ''));
$initials = strtoupper(implode('', array_map(static fn(string $part): string => mb_substr($part, 0, 1), preg_split('/\s+/', trim($input['name'])))));
$initials = mb_substr($initials, 0, 8);
$color = '#2563eb';
$skills = json_encode($input['skills'], JSON_THROW_ON_ERROR);
try {
    if ($id === '') {
        $id = 'STU-' . strtoupper(bin2hex(random_bytes(5)));
        $statement = db()->prepare(
            'INSERT INTO students (id, roll_no, name, department, semester, cgpa, email, country, state, city, skills, avatar_initials, avatar_color, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $statement->execute([$id, $input['rollNo'], $input['name'], $input['department'], $input['semester'], $input['cgpa'], $input['email'], $input['country'], $input['state'], $input['city'], $skills, $initials, $color, $input['status']]);
        jsonResponse(201, ['message' => 'Student record added.']);
    }
    $statement = db()->prepare(
        'UPDATE students SET roll_no = ?, name = ?, department = ?, semester = ?, cgpa = ?, email = ?, country = ?, state = ?, city = ?, skills = ?, avatar_initials = ?, status = ? WHERE id = ?'
    );
    $statement->execute([$input['rollNo'], $input['name'], $input['department'], $input['semester'], $input['cgpa'], $input['email'], $input['country'], $input['state'], $input['city'], $skills, $initials, $input['status'], $id]);
    if ($statement->rowCount() === 0) {
        $check = db()->prepare('SELECT 1 FROM students WHERE id = ?');
        $check->execute([$id]);
        if (!$check->fetchColumn()) {
            jsonResponse(404, ['error' => 'Student record not found.']);
        }
    }
    jsonResponse(200, ['message' => 'Student record updated.']);
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        jsonResponse(409, ['error' => 'That roll number is already in use.']);
    }
    throw $exception;
}
