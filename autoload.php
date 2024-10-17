<?php

spl_autoload_register(function ($class) {
    // Преобразуем имя класса в путь
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';

    // Проверяем, существует ли файл
    if (file_exists($file)) {
        require $file;
    }
});
