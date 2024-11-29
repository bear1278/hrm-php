<?php

namespace app\Helpers;

use app\Entities\Candidate;
use Exception;

class ErrorHelper
{
    private function redirectToErrorPage($message)
    {
        $encodedMessage = urlencode($message);
        header("Location: /error?message=" . $encodedMessage);
        exit();
    }

    public static function ImageFileErrorHandler($file)
    {
        if($file['size']>2*1024*1024){
            throw new Exception('Размер файла не должен превышать 2мб');
        }
        $imageInfo = @getimagesize($_FILES['image']['tmp_name']);
        if ($imageInfo === false) {
            throw new Exception("Файл повреждён или не является изображением.");
        }
    }

    public static function ImagePathErrorHandler($filePath)
    {
        if (!is_dir(__DIR__ . Candidate::DIR_IMAGES)){
            throw new Exception("Директория для хранения не найдена");
        }

        if (!is_writable(__DIR__ . Candidate::DIR_IMAGES)){
            throw new Exception("Ограничен доступ к директории для хранения.");
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
            throw new Exception("Failed to move uploaded file to " . $filePath);
        }
    }

    public static function ImageFileErrorHandlersToView($filepath)
    {
        $errorImage="";
        if (!is_dir(__DIR__ . Candidate::DIR_IMAGES)) {
            $errorImage = "Директория с файлами не найдена.";
        } elseif (!is_readable(__DIR__ . Candidate::DIR_IMAGES)) {
            $errorImage = "Ограничен доступ к директории с файлами.";
        }elseif (!file_exists($filepath)) {
            $errorImage = "Файл не найден.";
        } elseif (!is_readable($filepath)) {
            $errorImage = "Ограничен доступ к файлу.";
        } elseif (!is_file($filepath)) {
            $errorImage = "Указанный путь не является файлом.";
        } else {
            $imageInfo = @getimagesize($filepath);
            if ($imageInfo === false) {
                $errorImage = "Файл повреждён или не является изображением.";
            }
        }
        return $errorImage;
    }

    public static function ImageBlobErrorHandler($mime_type,$image)
    {
        $errorImage="";
        $image_data = base64_encode($image);
        if (base64_decode($image_data, true) === false) {
            $errorImage="Ошибка кодировки изображения.";
        }
        if (!preg_match('/^image\//', $mime_type)) {
            $errorImage="Данные не являются изображением.";
        }
        return $errorImage;
    }
}