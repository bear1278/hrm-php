<?php

namespace app\Controllers;

use app\Helpers\ApplicationHelper;
use app\Models\ApplicationModel;
use app\Models\ProfileModel;
use app\Models\TaskModel;
use app\Models\VacancyModel;
use Exception;
use PDOException;

class TaskController{

    private $model;
    private $applicationModel;
    private $vacancyModel;
    private $profileModel;
    private $tasks;

    public function __construct()
    {
        $this->model = new TaskModel();
        $this->applicationModel = new ApplicationModel();
        $this->vacancyModel = new VacancyModel();
        $this->profileModel = new ProfileModel();
        $this->tasks = [
            'Ждет ревью'=>[],
            'Архив'=>[],
            'Нет ответа'=>[],
            'Нет условия'=>[],
        ];
    }

    public function getTasksPage()
    {
        try{
            $tasks = $this->model->selectTasksByUser($_SESSION['user_id']);
            foreach ($tasks as $key => $task){
                $application = $this->applicationModel->selectApplicationById($task['application_ID']);
                $tasks[$key]['application']=$application;
            }
            foreach ($tasks as $key => $task){
                if($task['result']!='нет'){
                    array_push($this->tasks['Архив'],$task);
                }elseif (!$task['clause']){
                    array_push($this->tasks['Нет условия'],$task);
                }elseif (!$task['response']){
                    array_push($this->tasks['Нет ответа'],$task);
                }else{
                    array_push($this->tasks['Ждет ревью'],$task);
                }
            }
            $tabs = $this->tasks;
            require_once __DIR__.'/../Views/tasks.html';
            exit();
        }catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function getTaskPage($id)
    {
        try{
            $task = $this->model->selectTaskById($id);
            $vacancy = $this->applicationModel->selectApplicationById($task['application_ID']);
            $skills = $this->vacancyModel->selectSkillForVacancy($vacancy->getId());
            $vacancy->setSkills($skills);
            $candidate = $this->profileModel->SelectCandidate($vacancy->getCandidateId());
            $candidate->setSkills($this->profileModel->selectSkillForCandidate($vacancy->getCandidateId()));
            $percent = ApplicationHelper::getComparison($candidate,$vacancy);
            $isDeadLineExpired=false;
            date_default_timezone_set('Europe/Moscow');
            if(strtotime(date('Y-m-d\TH:i:sP'))>=strtotime($task['deadline'])){
                $isDeadLineExpired=true;
            }
            require_once __DIR__.'/../Views/task.html';
            exit();
        }catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function setTaskClause($id)
    {
        try {
            $clause = trim($_POST['clause']);
            $deadline = $_POST['deadline'];
            $result = $this->model->SetClauseForTask($id, $clause, $deadline);
            if ($result) {
                header("Location: http://localhost/task/$id");
            } else {
                throw new Exception('Ошибка изменения текущего этапа');
            }
            exit();
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            $errorMessage = urlencode('Ошибка: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function setTaskResponse($app_id)
    {
        try {
            $response = trim($_POST['response']);
            $id = $_POST['task'];
            $result = $this->model->SetResponseForTask($id, $response);
            if ($result) {
                header("Location: http://localhost/application/$app_id");
            } else {
                throw new Exception('Ошибка изменения текущего этапа');
            }
            exit();
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            $errorMessage = urlencode('Ошибка: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function setTaskFeedback($id)
    {
        try{
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $skills = $data['skills'];
            $result = trim($data['result']);
            $isOk = $this->model->feedback($id,$skills,$result);
            if($isOk){
                echo json_encode(['success'=>true]);
            }else{
                throw new Exception('Ошибка изменения текущего этапа');
            }
        }catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Ошибка: ' . $e->getMessage()]);
            exit();
        }
    }

    public function setTaskReject($id)
    {
        try {
            $result = $this->model->ChangeTaskResult($id,'не пройдено');
            if ($result) {
                header("Location: http://localhost/task/$id");
            } else {
                throw new Exception('Ошибка изменения текущего этапа');
            }
            exit();
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            $errorMessage = urlencode('Ошибка: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }
}
