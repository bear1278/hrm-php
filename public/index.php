<?php

use app\Controllers\AdminController;
use app\Controllers\ApplicationController;
use app\Controllers\DashboardController;
use app\Controllers\LoginController;
use app\Controllers\NotificationController;
use app\Controllers\ProfileController;
use app\Controllers\ResumeController;
use app\Controllers\SignUpController;
use app\Helpers\AuthHelper;
use app\Controllers\VacancyController;

require_once '../autoload.php';


session_start();

$request_uri = $_SERVER['REQUEST_URI'];
$request_uri = explode('?', $request_uri)[0];

$parts = explode('/', trim($request_uri, '/'));

if($parts[0]==='error'){
    $errorMessage = isset($_GET['message']) ? $_GET['message'] : 'Произошла неизвестная ошибка';
    require_once __DIR__ . '/../app/Views/error.html';
    exit();
}
require_once __DIR__ . '/../config/database.php';
$loginController = new LoginController();
$signUpController = new SignUpController();
$dashboardController = new DashboardController();
$applicationController = new ApplicationController();
$resumeController = new ResumeController();
$profileController = new ProfileController();
$adminController = new AdminController();
$notificationController = new NotificationController();
$vacancyController = new VacancyController();

switch ($parts[0]) {


    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loginController->login();
        } else {
            $loginController->showLoginForm();
        }
        break;

    case 'logout':
        AuthHelper::ensureLoggedIn();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loginController->logout();
        }
        break;

    case 'signup':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $signUpController->signUp();
        } else {
            $signUpController->ShowRegistration();
        }
        break;

    case '':
        AuthHelper::ensureLoggedIn();
        $dashboardController->showDashboard();
        break;

    case 'search':
        AuthHelper::ensureLoggedIn();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dashboardController->displaySearchResult();
        }
        break;

    case 'delete':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isManager()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vacancy_ID'])) {
                $dashboardController->deleteVacancy($_POST['vacancy_ID']);
            }
        } else {
            header('Location: /');
            exit();
        }
        break;

    case 'add':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isManager()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $dashboardController->addVacancy();
            }
        } else {
            header('Location: /');
            exit();
        }
        break;

    case 'edit':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isManager()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $dashboardController->editVacancy();
            }
        } else {
            header('Location: /');
            exit();
        }
        break;

    case 'applications':
        AuthHelper::ensureLoggedIn();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $applicationController->ShowApplicationsForCandidate();
        }
        if (isset($parts[1]) && $parts[1] === 'refuse') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $applicationController->ChangeApplicationStatus(5);
            }
        }
        if (isset($parts[1]) && $parts[1] === 'accept') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $applicationController->ChangeApplicationStatus(6);
            }
        }
        if (isset($parts[1]) && $parts[1] === 'delete') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_ID'])) {
                $applicationController->UnApply();
            } else {
                echo "Не указан ID заявки или неверный метод запроса.";
            }
        }
        if (isset($parts[1]) && $parts[1] === 'search') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $applicationController->SearchApplication();
            } else {
                echo "Не указан ID заявки или неверный метод запроса.";
            }
        }
        break;

    case 'apply':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isCandidate()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vacancy_ID'])) {
                $applicationController->CreateApplication();
            }
        }
        break;

    case 'resume':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isCandidate()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $resumeController->SaveResume();
            } else {
                $resumeController->ShowResume();
            }
        } else {
            header('Location: /');
            exit();
        }
        break;

    case 'profile':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isCandidate()) {
            if(isset($parts[1]) && $parts[1]==='edit' && $_SERVER['REQUEST_METHOD'] === 'POST'){
                $profileController->SetNewProfileImage();
            }elseif($_SERVER['REQUEST_METHOD'] === 'GET') {
                $profileController->ShowProfileForCandidate();
            }
        } else {
            header('Location: /');
            exit();
        }
        break;

    case 'role':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isAdmin()) {
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $adminController->EditRole();
            }
        } else {
            header('Location: /');
            exit();
        }
        break;

    case 'status':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isAdmin()) {
            if ($_SERVER['REQUEST_METHOD'] == "GET") {
                $adminController->ShowStatus();
            }
            if (isset($parts[1]) && $parts[1] === 'add') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $adminController->Add('status');
                }
            }
            if (isset($parts[1]) && $parts[1] === 'delete') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $adminController->Delete('status', 'status_ID');
                }
            }
        } else {
            header('Location: /');
            exit();
        }
        break;

    case 'department':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isAdmin()) {
            if ($_SERVER['REQUEST_METHOD'] == "GET") {
                $adminController->ShowDepartment();
            }
            if (isset($parts[1]) && $parts[1] === 'add') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $adminController->Add('departments');
                }
            }
            if (isset($parts[1]) && $parts[1] === 'delete') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $adminController->Delete('departments', 'department_id');
                }
            }
        } else {
            header('Location: /');
            exit();
        }
        break;

    case 'skills':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isAdmin()) {
            if ($_SERVER['REQUEST_METHOD'] == "GET") {
                $adminController->ShowSkills();
            }
            if (isset($parts[1]) && $parts[1] === 'add') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $adminController->Add('skills');
                }
            }
            if (isset($parts[1]) && $parts[1] === 'delete') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $adminController->Delete('skills', 'skill_ID');
                }
            }
        } else {
            header('Location: /');
            exit();
        }
        break;

    case 'get-notifications':
        AuthHelper::ensureLoggedIn();
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $notificationController->getNotifications();
        }
        break;

    case 'update-notification-status':
        AuthHelper::ensureLoggedIn();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $notificationController->updateNotificationStatus();
        }
        break;

    case 'history':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isAdmin()) {
            if ($_SERVER['REQUEST_METHOD'] == "GET") {
                $adminController->ShowHistory();
            }
            if (isset($parts[1]) && $parts[1] === 'delete') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $adminController->Delete('user_history', 'id');
                }
            }
            if (isset($parts[1]) && $parts[1]==='change')
            {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $adminController->changeRecommendationParameters();
                }
            }
        } else {
            header('Location: /');
            exit();
        }
        break;

    case 'vacancy':
        AuthHelper::ensureLoggedIn();
        if (AuthHelper::isCandidate()) {
            if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($parts[1])) {
                $vacancyController->ShowVacancyDetails((int)$parts[1]);
            }
            if($_SERVER['REQUEST_METHOD']==='POST' && isset($parts[1]) && isset($parts[2]) && $parts[2]=='edit'){
                $vacancyController->SetNewImage((int)$parts[1]);
            }
        } else {
            header('Location: /');
            exit();
        }
        break;


    default:
        http_response_code(404);
        echo "404 - Page Not Found";
        break;
}
