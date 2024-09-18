<?php
// app/Controllers/LoginController.php

require_once __DIR__ . '/../Models/CandidateModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php'; 

class ResumeController{

    public function ShowResume(){
        if (!isset($_SESSION['user_id'])) {
            
            header('Location: /login');
            exit();
        }
        if ($_SESSION['role']!=4){
            header('Location: /login');
            exit();
        }
        require_once __DIR__ . '/../Views/resume_form.php';
    }

    public function SaveResume(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $number = trim($_POST['phone_number']);
            $resume = trim($_POST['resume']);
            $exp = trim($_POST['experience_years']);
            $location = trim($_POST['location']);

            
            // Validate inputs
            if (empty($number) ||  empty($resume) || empty($exp) || empty($location)) {
                $error = 'Please fill in all fields.';
                require_once __DIR__ . '/../Views/resume_form.php';
                return;
            }
            $id = $_SESSION['user_id'];
            $candidateModel = new CandidateModel();
            $candidate = $candidateModel->findCandidateById($id);
            if ($candidate){
                header('Location: /');
                exit();
            }

            $result = $candidateModel->createCandidate($id,$number,$resume,$exp,$location);
            if ($result){
                header('Location: /');
                exit();
            }else{
                $error = 'Database error.';
                
                require_once __DIR__ . '/../Views/signup_form.php';
            }
    }
    }
}