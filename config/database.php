<?php

function loadEnv($file) {
    if (!file_exists($file)) {
        throw new Exception("Файл .env не найден.");
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        putenv(trim("$key=$value"));
    }
}

try {
    loadEnv(__DIR__ . '../../.env');
    $dbHost = getenv('DB_HOST');
    $dbPort = getenv('DB_PORT');
    $dbname = getenv('DB_DATABASE');
    $user = getenv('DB_USERNAME');
    $password = getenv('DB_PASSWORD');
    $host=$dbHost . ":" . $dbPort;
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
    header("Location: /error?message=" . $errorMessage);
    require_once __DIR__ . '/../app/Views/error.html';
    exit();
}



