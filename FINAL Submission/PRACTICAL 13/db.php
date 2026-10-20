<?php
$host = 'localhost';
$database = 'practical13';
$username = 'root';
$password = '';

$dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
