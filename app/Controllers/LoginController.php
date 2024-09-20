<?php
// app/Controllers/LoginController.php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Helpers/ErrorHelper.php';


class LoginController {
    
    public function showLoginForm($error=null) {
        // Load the login form view
        require_once __DIR__ . '/../Views/login_form.html';
    }

    public function login() {
       
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            
            // Validate inputs
            if (empty($email) || empty($password)) {
               
                echo json_encode(['error' => 'Пожалуйста, заполните все поля']);
            }
            try{
                
                $userModel = new UserModel();
                $user = $userModel->findUserByUsername($email);
            }catch(Exception $e){
                if($user){
                http_response_code(500);
                echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
                exit();
                }
            }


            if ($user && password_verify($password,$user['password'])) {
                // Set session and log in user
                
                AuthHelper::login($user['user_ID'], $user['first_name'], $user['last_name'], $user['email'], $user['role_ID']);
                echo json_encode(['success' => true]);
                exit();
            } else {
                
                echo json_encode(['error' => 'Неправильные почта или пароль.']);
            }
        }
    }

    public function logout() {
        AuthHelper::logout();
        header('Location: /login');
        exit();
    }
}
