<?php
// app/Controllers/LoginController.php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';



class LoginController {
    
    public function showLoginForm() {
        // Load the login form view
        require_once __DIR__ . '/../Views/login_form.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['username']);
            $password = trim($_POST['password']);
            
            // Validate inputs
            if (empty($email) || empty($password)) {
                $error = 'Please fill in all fields.';
                require_once __DIR__ . '/../Views/login_form.php';
                return;
            }

            // Check user credentials
            $userModel = new UserModel();
            $user = $userModel->findUserByUsername($email);


            if ($user && $password==$user['password']) {
                // Set session and log in user
                
                AuthHelper::login($user['user_ID'], $user['first_name'], $user['last_name'], $user['email'], $user['role_ID']);
                header('Location: /dashboard');
                exit();
            } else {
                $error = 'Invalid username or password.';
                
                require_once __DIR__ . '/../Views/login_form.php';
            }
        }
    }

    public function logout() {
        AuthHelper::logout();
        header('Location: /login');
        exit();
    }
}
