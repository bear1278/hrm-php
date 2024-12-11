<?php

namespace app\Controllers;

use app\Models\InterviewModel;
use PDOException;

class InterviewerController
{
    private $interviewModel;

    public function __construct()
    {
        $this->interviewModel= new InterviewModel();
    }

    public function showDashboard()
    {
        try{
            $interviews = $this->interviewModel->selectInterviewByUser($_SESSION['user_id']);
            require_once __DIR__.'/../Views/interviews.html';
            exit();
        }catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }
}