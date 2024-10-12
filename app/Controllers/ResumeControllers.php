<?php

require_once __DIR__ . '/../Models/CandidateModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php'; 

class ResumeController{

    private $model;

    public function __construct() {
        $this->model = new CandidateModel();
    }

    public function ShowResume(){
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
        if ($_SESSION['role']!=4){
            header('Location: /login');
            exit();
        }
        require_once __DIR__ . '/../Views/resume_form.html';
    }

    public function SaveResume(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $number = trim($_POST['phone_number']);
            $resume = trim($_POST['resume']);
            $exp = trim($_POST['experience_years']);
            $location = trim($_POST['location']);

            if (empty($number) ||  empty($resume) || empty($location)) {
                echo json_encode(['error' => 'Пожалуйста, заполните поля']);
                return;
            }
            $id = $_SESSION['user_id'];
            try {
                $candidate = $this->model->findCandidateById($id);
            }catch(Exception $e){
                http_response_code(500);
                echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
                exit();
            }
            if ($candidate){
                try{
                    $result=$this->model->updateCandidate($id, $number, $resume, $exp, $location);
                }
                catch(Exception $e){
                    http_response_code(500);
                    echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
                    exit();
                }
            }else{
                try {
                    $result = $this->model->createCandidate($id, $number, $resume, $exp, $location);
                }catch(Exception $e){
                    http_response_code(500);
                    echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
                    exit();
                }
            }
            if ($result) {
                echo json_encode(['success' => true]);
                exit();
            } else {
                echo json_encode(['error' => 'Database error']);
                exit();
            }
        }
    }
}