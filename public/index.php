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
        $dashboardController->showDasboard();

        break;

    case 'role':
        require_once __DIR__ . '/../app/Controllers/AdminController.php';
        $adminController= new AdminController();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
        if ($_SERVER['REQUEST_METHOD']=="POST"){
            $adminController->EditRole();
        }
        break;

    case 'status':
        require_once __DIR__ . '/../app/Controllers/AdminController.php';
        $adminController= new AdminController();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
        if ($_SERVER['REQUEST_METHOD']=="GET"){
            $adminController->ShowStatus();
        }
        if (isset($parts[1]) && $parts[1] === 'add') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                $adminController->Add('status');
            }
        }
        if (isset($parts[1]) && $parts[1] === 'delete') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                $adminController->Delete('status','status_ID');
            }
        }
        break;

    case 'department':
        require_once __DIR__ . '/../app/Controllers/AdminController.php';
        $adminController= new AdminController();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
        if ($_SERVER['REQUEST_METHOD']=="GET"){
            $adminController->ShowDepartment();
        }
        if (isset($parts[1]) && $parts[1] === 'add') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                $adminController->Add('departments');
            }
        }
        if (isset($parts[1]) && $parts[1] === 'delete') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                $adminController->Delete('departments','department_id');
            }
        }
        break;

    case 'skills':
        require_once __DIR__ . '/../app/Controllers/AdminController.php';
        $adminController= new AdminController();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
        if ($_SERVER['REQUEST_METHOD']=="GET"){
            $adminController->ShowSkills();
        }
        if (isset($parts[1]) && $parts[1] === 'add') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                $adminController->Add('skills');
            }
        }
        if (isset($parts[1]) && $parts[1] === 'delete') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                $adminController->Delete('skills','skill_ID');
            }
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
    
     case 'resume':
         require_once __DIR__ . '/../app/Controllers/ResumeControllers.php';
         $resumeController = new ResumeController();
         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $resumeController->SaveResume();  // Handle form submission
         } else {
             $resumeController->ShowResume();  // Show login form
         }
         break;

    case 'apply':
        require_once __DIR__ . '/../app/Controllers/ApplicationController.php';
        $applicationController= new ApplicationController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vacancy_ID'])) {
            $applicationController->CreateApplication();  // Удаляем вакансию
        }
        break;

    case 'applications':
        require_once __DIR__ . '/../app/Controllers/ApplicationController.php';
        $applicationController = new ApplicationController();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $applicationController->ShowApplicationsForCandidate();
        }

        if (isset($parts[1]) && $parts[1] === 'refuse') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                $applicationController->ChangeApplicationStatus(5);
            }
        }

        if (isset($parts[1]) && $parts[1] === 'accept') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                $applicationController->ChangeApplicationStatus(6);
            }
        }

        if (isset($parts[1]) && $parts[1] === 'delete') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_ID'])) {
                // Обработка удаления заявки
                $applicationController->UnApply();
            } else {
                echo "Не указан ID заявки или неверный метод запроса.";
            }
        }
        if (isset($parts[1]) && $parts[1] === 'search') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $applicationController->SearchApplicationForCandidate();
            } else {
                echo "Не указан ID заявки или неверный метод запроса.";
            }
        }
        break;

    case 'profile':
        require_once __DIR__ . '/../app/Controllers/ProfileController.php';
        $profileController = new ProfileController();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $profileController->ShowProfileForCandidate();
        }
        break;


    default:
        http_response_code(404);
        echo "404 - Page Not Found";
        break;
}
