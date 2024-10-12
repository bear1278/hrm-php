<?php


require_once __DIR__ . '/../Models/AdminModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';


class AdminController
{

    private $model;

    public function __construct()
    {
        $this->model = new AdminModel();
    }

    public function EditRole()
    {
        $user_ID = $_POST['user_ID'];
        $role_ID = $_POST['role_ID'];
        try{
            $result = $this->model->UpdateUserRole($user_ID,$role_ID);
        }catch(Exception $e){
            $this->ErrorHandler($e->getMessage());
            exit();
        }
        if($result){
            http_response_code(200);
            header("Location: /");
        }else{
            http_response_code(500);
            $errorMessage = urlencode('Ошибка');
            header("Location: /error?message=" . $errorMessage);
        }
    }

    public function ErrorHandler($message)
    {
        http_response_code(500);
        $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $message);
        header("Location: /error?message=" . $errorMessage);
    }

    public function ShowStatus()
    {
        if($_SESSION['role']==1){
            try{
                $data=$this->model->Select('status');
            }catch(Exception $e){
                $this->ErrorHandler($e->getMessage());
                exit();
            }
            $columns=array_keys($data[0]);
            require_once __DIR__ . '/../Views/status.html';
        }
    }

    public function Add($table)
    {
        $name=trim($_POST['name']);
        try{
            $result = $this->model->Insert($name,$table);
        }catch(Exception $e){
            $this->ErrorHandler($e->getMessage());
            exit();
        }if($result){
        http_response_code(200);
        header("Location: /status");
    }else{
        http_response_code(500);
        $errorMessage = urlencode('Ошибка');
        header("Location: /error?message=" . $errorMessage);
    }
    }

    public function Delete($table,$key)
    {
        $id=trim($_POST['ID']);
        try{
            $result = $this->model->Delete($id,$table,$key);
        }catch(Exception $e){
            $this->ErrorHandler($e->getMessage());
            exit();
        }if ($result){
        echo json_encode(['success' => true]);
    }else{
        echo json_encode(['success' => false, 'message' => 'Ошибка при удалении']);
    }
    }

    public function ShowDepartment()
    {
        if($_SESSION['role']==1){
            try{
                $data=$this->model->Select('departments');
            }catch(Exception $e){
                $this->ErrorHandler($e->getMessage());
                exit();
            }
            $columns=array_keys($data[0]);
            require_once __DIR__ . '/../Views/department.html';
        }
    }

    public function ShowSkills()
    {
        if($_SESSION['role']==1){
            try{
                $data=$this->model->Select('skills');
            }catch(Exception $e){
                $this->ErrorHandler($e->getMessage());
                exit();
            }
            $columns=array_keys($data[0]);
            require_once __DIR__ . '/../Views/skills.html';
        }
    }


}