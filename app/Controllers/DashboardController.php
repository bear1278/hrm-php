<?php
// app/Controllers/LoginController.php

require_once __DIR__ . '/../Models/VacancyModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';



class DashboardController {
    
    private $model;

    public function __construct() {
        $this->model = new VacancyModel();
    }

    public function displaySearchResult() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $search = $data['search'];
            if (empty($search)) {
                http_response_code(301);
                return;
            }

            try {
                // Получаем вакансии по параметрам поиска
                $data = $this->model->getVacanciesWithParam($search);
                $columns = $this->model->getTableColumns();
                $columns_type = $this->model->getColumnsType();
            } catch (Exception $e) {
                http_response_code(500);
                $this->sendJsonResponse([
                    'error' => 'Ошибка подключения к базе данных: ' . $e->getMessage()
                ]);
                return;
            }

            // Формируем ответ в зависимости от роли пользователя
            if ($_SESSION['role'] == 4) {
                // Для роли 4 - выполняем специфический поиск
                $data = $this->GetSearchForCandidate($search, $_SESSION['user_id']);
            }

            // Отправляем JSON-ответ с данными
            $this->sendJsonResponse([
                'data' => $data,
                'columns' => $columns,
                'columns_type' => $columns_type
            ]);
        }
    }

// Вспомогательный метод для отправки JSON-ответа
    private function sendJsonResponse($response) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function showDasboard() {
        try{
        $data = $this->model->getVacancies($_SESSION['user_id']);
        $columns = $this->model->getTableColumns();
        $columns_type = $this->model->getColumnsType();
        $departments = $this->model->SelectAllDepartments();
        $skills =$this->model->SelectAllSkills();
        }
        catch(Exception $e){
                http_response_code(500);
                $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
                header("Location: /error?message=" . $errorMessage);
                exit();
            }
        if($_SESSION['role']==4){
            $data = $this->GetVacanciesForCandidate($_SESSION['user_id']);
            require_once __DIR__ . '/../Views/dashboardCandidate.html';
        }elseif($_SESSION['role']==2){
            require_once __DIR__ . '/../Views/dashboard.html';
        }elseif ($_SESSION['role']==1){
            try{
                $data = $this->model->SelectAllUsers($_SESSION['user_id']);
            $columns=array_keys($data[0]);
            $columns=array_diff($columns,['user_ID']);
            $roles = $this->model->SelectRoles();
            }catch(Exception $e){
                http_response_code(500);
                $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
                header("Location: /error?message=" . $errorMessage);
                exit();
            }
            require_once __DIR__ . '/../Views/admin.html';
        }

    }

    public function GetVacanciesForCandidate($id)
    {
        try{
            return $this->model->SelectVacanciesForCandidate($id);
        }
        catch(Exception $e){
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function GetSearchForCandidate($search,$id)
    {
        try{
            return $this->model->getVacanciesWithParamForCandidate($search,$id);
        }
        catch(Exception $e){
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function deleteVacancy($id){
        header('Content-Type: application/json');
        try{
        $result= $this->model->deleteVacancyFromDB($id);
        }catch(Exception $e){
                http_response_code(500);
                echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
                exit();
            }
        if ($result){
            echo json_encode(['success' => true]);
        }else{
            echo json_encode(['success' => false, 'message' => 'Ошибка при удалении']);
        }
    }




    public function editVacancy(){
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ID=trim($_POST['vacancy_ID']);
            $name = trim($_POST['name']);
            $department_ID = trim($_POST['department_ID']);
            $description = trim($_POST['description']);
            $experience_required = trim($_POST['experience_required']);
            $salary= trim($_POST['salary']);
            $status =1;
            $skills = $_POST['skills'];

            if (empty($ID)) {
                echo json_encode(['error' => 'Ошибка']);
                return;
            }
            try{
                $vacancy=$this->model->getVacancyByID($ID);
                if (empty($name)){
                    $name=$vacancy['name'];
                }
                if (empty($department_ID)){
                    $department_ID=$vacancy['department_ID'];
                }
                if (empty($description)){
                    $description=$vacancy['description'];
                }
                if (empty($experience_required )){
                    $experience_required=$vacancy['experience_required'];
                }
                if (empty($salary)){
                    $salary=$vacancy['salary'];
                }
                if (empty($posting_date)){
                    $posting_date=$vacancy['posting_date'];
                }
                if (empty($status)){
                    $status=$vacancy['status'];
                }
            }catch(Exception $e){
                http_response_code(500);
                echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
                exit();
            }

            try{
            $departmentCount = $this->model->getRowCount('departments');
            }catch(Exception $e){
                http_response_code(500);
                echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
                exit();
            }


            if ($department_ID<1 || $department_ID>$departmentCount) {
                echo json_encode(['error' => 'Невалидное значение department_ID']);
                return;      
            }

            $maxIntValue = 2147483647;

            if ($experience_required<0 || $experience_required>$maxIntValue) {
                echo json_encode(['error' => 'Невалидное значение experience_required']);
                return;      
            }


            if ($salary<0 || $salary>$maxIntValue) {
                echo json_encode(['error' => 'Невалидное значение salary']);
                return;      
            }

            if ($status<1 || $status>$maxIntValue) {
                echo json_encode(['error' => 'Невалидное значение status']);
                return;      
            }
            try{
            $result=$this->model->UpdateVacancyAndSkills($ID,$name,$department_ID,$description,$experience_required,$salary,$posting_date,$status,$skills);
            }catch(Exception $e){
                http_response_code(500);
                echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
                exit();
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
    

    public function addVacancy(){
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $department_ID = trim($_POST['department_ID']);
            $description = trim($_POST['description']);
            $experience_required = trim($_POST['experience_required']);
            $skills=$_POST['skills'];
            $salary= trim($_POST['salary']);
            $posting_date =date("Y-m-d H:i:s");
            $status =1;

            if (empty($skills)){
                echo json_encode(['error' => 'Пожалуйста, выберите хотя бы один навык']);
                return;
            }
            
            // Validate inputs
            if (empty($name) ||  empty($department_ID) || empty($description) || empty($salary) || empty($posting_date) || empty($status)) {
                echo json_encode(['error' => 'Пожалуйста, заполните все поля']);
                return;
            }
            try{
            $departmentCount = $this->model->getRowCount('departments');
            }catch(Exception $e){
                http_response_code(500);
                echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
                exit();
            }

            if ($department_ID<1 || $department_ID>$departmentCount) {
                echo json_encode(['error' => 'Невалидное значение department_ID']);
                return;      
            }

            $maxIntValue = 2147483647;

            if ($experience_required<0 || $experience_required>$maxIntValue) {
                echo json_encode(['error' => 'Невалидное значение experience_required']);
                return;      
            }

            if ($salary<0 || $salary>$maxIntValue) {
                echo json_encode(['error' => 'Невалидное значение salary']);
                return;      
            }

            try{
            $result=$this->model->InsertNewVacancy($name,$department_ID,$description,$experience_required,$salary,$posting_date,$status,$_SESSION['user_id'],$skills);
            }catch(Exception $e){
                http_response_code(500);
                echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
                exit();
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
