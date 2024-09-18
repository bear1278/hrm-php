<?php
// public/index.php
session_start();

$request_uri = $_SERVER['REQUEST_URI'];

// Include the routes logic
switch ($request_uri) {
    case '/login':
        require_once __DIR__ . '/../app/Controllers/LoginController.php';
        $loginController = new LoginController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loginController->login();  // Handle form submission
        } else {
            $loginController->showLoginForm();  // Show login form
        }
        break;

    case '/':
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $dashboardController= new DashboardController();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
        
        // if ($_SESSION['role']==4){
        //     header('Location: /profile');
        //     exit();
        else /*if ($_SESSION['role']=2)*/ {
            $dashboardController->showDasboard();
        }
        
        break;

    case '/signup':
         require_once __DIR__ . '/../app/Controllers/SignUpController.php';
        $signUpController = new SignUpController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $signUpController->signUp();  // Handle form submission
        } else {
            $signUpController->ShowRegistration();  // Show login form
        }
        break;

    // case '/resume':
    //     require_once __DIR__ . '/../app/Controllers/ResumeControllers.php';
    //     $resumeController = new ResumeController();
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $resumeController->SaveResume();  // Handle form submission
    //     } else {
    //         $resumeController->ShowResume();  // Show login form
    //     }
    //     break;

    default:
        http_response_code(404);
        echo "404 - Page Not Found";
        break;
}
