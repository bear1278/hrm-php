<?php
// app/Controllers/LoginController.php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php'; 

class SignUpController{

    public function ShowRegistration(){
        require_once __DIR__ . '/../Views/signup_form.php';
    }

    public function SignUp() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstname = trim($_POST['firstname']);
            $lastname = trim($_POST['lastname']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirmPassword']);

            
            // Validate inputs
            if (empty($firstname) ||  empty($lastname) || empty($password) || empty($email) || empty($confirmPassword)) {
                $error = 'Please fill in all fields.';
                require_once __DIR__ . '/../Views/signup_form.php';
                return;
            }

            if ($password!=$confirmPassword) {
                $error = 'Passwords dont match.';
                require_once __DIR__ . '/../Views/signup_form.php';
                return;      
            }
            

            $userModel = new UserModel();
            $user = $userModel->findUserByUsername($email);

            if ($user){
                $error='You already have account';
                header('Location: /login');
                exit();
            }

            $result=$userModel->createUser($firstname,$lastname,$email,$password);

            if ($result) {
                // Set session and log in user
                AuthHelper::login($result,$firstname,$lastname, $email,4);
                header('Location: /dashboard');
                exit();
            } else {
                $error = 'Database error.';
                
                require_once __DIR__ . '/../Views/signup_form.php';
            }
        }
    }
}