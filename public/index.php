<?php
session_start();



$request_uri = $_SERVER['REQUEST_URI'];
$request_uri = explode('?', $request_uri)[0];

$parts = explode('/', trim($request_uri, '/')); 

switch ($parts[0]) {
    case 'login':
        require_once __DIR__ . '/../app/Controllers/LoginController.php';
        $loginController = new LoginController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loginController->login();  // Handle form submission
        } else {
            $loginController->showLoginForm();  // Show login form
        }
        break;

    case '':
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $dashboardController= new DashboardController();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
         if ($_SESSION['role']==4){
             $dashboardController->showDasboard();
         }
        elseif ($_SESSION['role']=2){
            $dashboardController->showDasboard();
        }
        
        break;

    case 'signup':
         require_once __DIR__ . '/../app/Controllers/SignUpController.php';
        $signUpController = new SignUpController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $signUpController->signUp();  // Handle form submission
        } else {
            $signUpController->ShowRegistration();  // Show login form
        }
        break;

    case 'search':
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $dashboardController= new DashboardController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dashboardController->displaySearchResult();
        }
        break;

    case 'delete': 
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $dashboardController= new DashboardController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vacancy_ID'])) {
            $dashboardController->deleteVacancy($_POST['vacancy_ID']);  // Удаляем вакансию
        }
        break;

    case 'add': 
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $dashboardController= new DashboardController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $dashboardController->addVacancy(); 
        }
        break;

    case 'edit': 
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $dashboardController= new DashboardController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $dashboardController->editVacancy(); 
        }
        break;

    case 'logout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../app/Controllers/LoginController.php';
            $loginController = new LoginController();
            $loginController->logout();
        }
        break;
    case 'error':
        $errorMessage = isset($_GET['message']) ? $_GET['message'] : 'Произошла неизвестная ошибка';
        require_once __DIR__ . '/../app/Views/error.html';  
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
