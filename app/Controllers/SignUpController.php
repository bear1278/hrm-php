<?php
// app/Controllers/LoginController.php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php'; 

class SignUpController{

    public function ShowRegistration(){
        require_once __DIR__ . '/../Views/signup_form.html';
    }

    public function SignUp() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstname = trim($_POST['firstname']);
            $lastname = trim($_POST['lastname']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirmPassword']);

            
            // Validate inputs
            if (empty($firstname) ||  empty($lastname) || empty($password) || empty($email) || empty($confirmPassword)) {
                echo json_encode(['error' => 'Пожалуйста, заполните все поля']);
                return;
            }

            if ($password!=$confirmPassword) {
                echo json_encode(['error' => 'Пароли не совпадают']);
                return;      
            }
            
            try{

            $userModel = new UserModel();
            $user = $userModel->findUserByUsername($email);

            if ($user){
                echo json_encode(['error' => 'Аккаунт уже существует']);
                exit();
            }

            $hashedPassword = password_hash($password,PASSWORD_DEFAULT);

            $result=$userModel->createUser($firstname,$lastname,$email,$hashedPassword);
            }
            catch (Exception $e){
                http_response_code(500);
                echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
                exit();
            }
                
            

            if ($result) {
                // Set session and log in user
                AuthHelper::login($result,$firstname,$lastname, $email,1);
                echo json_encode(['success' => true]);
                exit();
            } else {
                echo json_encode(['error' => 'Database error']);
                exit();
            }
        }
    }
}