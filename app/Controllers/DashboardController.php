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
            $search = trim($_POST['search']);
            if (empty($search)){
                $this->showDasboard();
                return;
            }
            try{
            $data = $this->model->getVacanciesWithParam($search);
            $columns = $this->model->getTableColumns();
            }
            catch(Exception $e){
                http_response_code(500);
                $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
                header("Location: /error?message=" . $errorMessage);
                exit();
            }
            require_once __DIR__ . '/../Views/dashboard.html';

        }
    }
    public function showDasboard() {
        try{
        $data = $this->model->getVacancies();
        $columns = $this->model->getTableColumns();
        }
        catch(Exception $e){
                http_response_code(500);
                $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
                header("Location: /error?message=" . $errorMessage);
                exit();
            }
        require_once __DIR__ . '/../Views/dashboard.html';
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

    public function addVacancy(){
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $department_ID = trim($_POST['department_ID']);
            $description = trim($_POST['description']);
            $experience_required = trim($_POST['experience_required']);
            $salary= trim($_POST['salary']);
            $posting_date =trim($_POST['posting_date']);
            $status =trim($_POST['status']);

            
            // Validate inputs
            if (empty($name) ||  empty($department_ID) || empty($description) || empty($experience_required) || empty($salary) || empty($posting_date) || empty($status)) {
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

            if ($experience_required<0) {
                echo json_encode(['error' => 'Невалидное значение experience_required']);
                return;      
            }

            if ($salary<0) {
                echo json_encode(['error' => 'Невалидное значение salary']);
                return;      
            }

            if ($status<1) {
                echo json_encode(['error' => 'Невалидное значение status']);
                return;      
            }
            try{
            $result=$this->model->Insert($name,$department_ID,$description,$experience_required,$salary,$posting_date,$status);
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
