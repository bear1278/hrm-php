<?php
$host = 'localhost:3308';
$dbname = 'hrm';
$user = 'Greg';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
    if ($_SERVER['REQUEST_URI']=='/' || $_SERVER['REQUEST_URI']=='/search' || $_SERVER['REQUEST_URI']=='/logout'){
        header("Location: /error?message=" . $errorMessage);
    }
    echo json_encode(['error' => $errorMessage]);
    exit();
}
