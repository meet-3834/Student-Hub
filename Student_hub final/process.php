<?php
session_start();

function sanitizeText($value)
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['form_errors'] = ['Invalid request method.'];
    header('Location: contact.php');
    exit;
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

$errors = [];

if (empty($name) || !preg_match('/^[A-Za-z\s.\'-]{2,50}$/', $name)) {
    $errors[] = 'Name is required and must contain only letters, spaces, dots, apostrophes, or hyphens.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (empty($phone) || !preg_match('/^[0-9+()\-\s]{10,15}$/', $phone)) {
    $errors[] = 'Please enter a valid phone number.';
}

if (empty($subject) || strlen(trim($subject)) < 3) {
    $errors[] = 'Subject must be at least 3 characters long.';
}

if (empty($message) || strlen(trim($message)) < 10) {
    $errors[] = 'Message must be at least 10 characters long.';
}

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    header('Location: contact.php');
    exit;
}

$cleanName = sanitizeText($name);
$cleanEmail = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
$cleanPhone = sanitizeText($phone);
$cleanSubject = sanitizeText($subject);
$cleanMessage = sanitizeText($message);

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

$timestamp = date('Y-m-d H:i:s');
$record = [
    'name' => $cleanName,
    'email' => $cleanEmail,
    'phone' => $cleanPhone,
    'subject' => $cleanSubject,
    'message' => $cleanMessage,
    'submitted_at' => $timestamp,
];

$csvFile = $dataDir . '/records.csv';
if (!file_exists($csvFile)) {
    $csvHeader = ['name', 'email', 'phone', 'subject', 'message', 'submitted_at'];
    $handle = fopen($csvFile, 'w');
    fputcsv($handle, $csvHeader);
    fclose($handle);
}

$csvHandle = fopen($csvFile, 'a');
fputcsv($csvHandle, [
    $cleanName,
    $cleanEmail,
    $cleanPhone,
    $cleanSubject,
    $cleanMessage,
    $timestamp,
]);
fclose($csvHandle);

$jsonFile = $dataDir . '/records.json';
$existingRecords = [];
if (file_exists($jsonFile)) {
    $existingData = file_get_contents($jsonFile);
    $existingRecords = json_decode($existingData, true);
    if (!is_array($existingRecords)) {
        $existingRecords = [];
    }
}
$existingRecords[] = $record;
file_put_contents($jsonFile, json_encode($existingRecords, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

$_SESSION['form_success'] = true;
$_SESSION['form_message'] = 'Your message has been submitted successfully and saved in CSV/JSON format.';
header('Location: contact.php');
exit;
