<?php

$host = 'localhost';
$dbname = 'hrm';
$user = 'root';
$password = '';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
    header("Location: /error?message=" . $errorMessage);
    require_once __DIR__ . '/../app/Views/error.html';
    exit();
}



