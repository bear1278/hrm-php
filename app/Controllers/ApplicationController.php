<?php


require_once __DIR__ . '/../Models/ApplicationModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';



class ApplicationController
{

    private $model;

    public function __construct()
    {
        $this->model = new ApplicationModel();
    }

    public function ShowApplicationsForCandidate()
    {
        try{
            $columns = $this->model->getTableColumns();
        }
        catch(Exception $e){
            $this->ErrorHandler($e->getMessage());
            exit();
        }
        if($_SESSION['role']==4){
            try{
                $columns = array_diff($columns,['candidate']);
                $data = $this->model->selectApplications($_SESSION['user_id']);
            }
            catch(Exception $e){
                $this->ErrorHandler($e->getMessage());
                exit();
            }
            require_once __DIR__ . '/../Views/applications.html';
        }elseif($_SESSION['role']==2){
            try{
                $data = $this->model->SelectAllApplicationManager($_SESSION['user_id']);
            }
            catch(Exception $e){
                $this->ErrorHandler($e->getMessage());
                exit();
            }
            require_once __DIR__ . '/../Views/applicationsManager.html';
        }
    }

    public function ErrorHandler($message)
    {
        http_response_code(500);
        $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $message);
        header("Location: /error?message=" . $errorMessage);
    }

    public function CreateApplication(){
        $vacancyID=$_POST['vacancy_ID'];
        $candidateID=$_SESSION['user_id'];
        try{
            $result= $this->model->createApplication($vacancyID,$candidateID);
        }catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        }
        if ($result){
            echo json_encode(['success' => true]);
        }else{
            echo json_encode(['success' => false, 'message' => 'Ошибка']);
        }
    }

    public function UnApply()
    {
        $application_ID=$_POST['application_ID'];
        try{
            $result = $this->model->DeleteApplication($application_ID);

        }catch(Exception $e){
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        }
        if ($result){
            echo json_encode(['success' => true]);
        }else{
            echo json_encode(['success' => false, 'message' => 'Ошибка']);
        }
    }

    public function SearchApplicationForCandidate()
    {
        $search = $_POST['search'];
        $id=$_SESSION['user_id'];
        if (empty($search)){
            $this->ShowApplicationsForCandidate();
        }
        try{

            $columns = $this->model->getTableColumns();
        }catch(Exception $e){
            $this->ErrorHandler($e->getMessage());
            exit();
        }
        if($_SESSION['role']==4){
            try{
                $columns = array_diff($columns,['candidate']);
                $data = $this->model->SelectApplicationsWithParam($id,$search);
            }catch(Exception $e){
                $this->ErrorHandler($e->getMessage());
                exit();
            }
            require_once __DIR__ . '/../Views/applications.html';
        }elseif($_SESSION['role']==2){
            try{
                $data = $this->model->SelectApplicationsManagerWithParam($id,$search);
            }catch(Exception $e){
                $this->ErrorHandler($e->getMessage());
                exit();
            }
            require_once __DIR__ . '/../Views/applicationsManager.html';
        }
    }

    public function ChangeApplicationStatus($status)
    {
        $id=$_POST['application_ID'];
        try{
            $result = $this->model->UpdateApplication($id,$status);
        }catch(Exception $e){
            $this->ErrorHandler($e->getMessage());
            exit();
        }
        if($result){
            http_response_code(200);
            header("Location: /applications");
        }else{
            http_response_code(500);
            $errorMessage = urlencode('Ошибка');
            header("Location: /error?message=" . $errorMessage);
        }
    }
}