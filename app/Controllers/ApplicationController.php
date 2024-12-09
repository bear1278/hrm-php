<?php

namespace app\Controllers;

use app\Entities\Candidate;
use app\Entities\Vacancy;
use app\Helpers\AuthHelper;
use app\Models\ApplicationModel;
use app\Models\InterviewModel;
use app\Models\ProfileModel;
use app\Models\VacancyModel;
use Exception;
use PDOException;

class ApplicationController
{

    private $model;
    private $vacancyModel;
    private $applications;
    private $profileModel;
    private $interviewModel;
    public function __construct()
    {
        $this->model = new ApplicationModel();
        $this->vacancyModel = new VacancyModel();
        $this->profileModel = new ProfileModel();
        $this->applications = [
            'просмотрен' => [],
            'приглашение' => [],
            'не просмотрен' => [],
            'вас приняли' => [],
            'отказ' => []
        ];
        $this->interviewModel = new InterviewModel();
    }

    public function ShowApplicationsForCandidate()
    {
        try {
            if (AuthHelper::isCandidate()) {
                $data = $this->model->selectApplications($_SESSION['user_id']);
                foreach ($data as $application) {
                    array_push($this->applications[$application->getApplicationStatus()], $application);
                }
                $applications = $this->applications;
                require_once __DIR__ . '/../Views/applications.html';
            } elseif (AuthHelper::isManager()) {
                $data = $this->model->SelectAllApplicationManager($_SESSION['user_id']);
                foreach ($data as $application) {
                    array_push($this->applications[$application->getApplicationStatus()], $application);
                }
                $applications = $this->applications;
                require_once __DIR__ . '/../Views/applicationsManager.html';
            }
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function CreateApplication()
    {
        $vacancyID = $_POST['vacancy_ID'];
        $candidateID = $_SESSION['user_id'];
        try {
            $result = $this->model->createApplication($vacancyID, $candidateID);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Ошибка обработки данных');
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }

    public function UnApply()
    {
        $application_ID = $_POST['application_ID'];
        try {
            $result = $this->model->DeleteApplication($application_ID);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Ошибка при обработке данных');
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Ошибка: ' . $e->getMessage()]);
            exit();
        }
    }

    public function SearchApplication()
    {
        $search = $_POST['search'];
        $id = $_SESSION['user_id'];
        if (empty($search)) {
            $this->ShowApplicationsForCandidate();
        }
        try {
            if (AuthHelper::isCandidate()) {
                $data = $this->model->SelectApplicationsWithParam($id, $search);
                foreach ($data as $application) {
                    array_push($this->applications[$application->getApplicationStatus()], $application);
                }
                $applications = $this->applications;
                require_once __DIR__ . '/../Views/applications.html';
            } elseif (AuthHelper::isManager()) {
                $data = $this->model->SelectApplicationsManagerWithParam($id, $search);
                foreach ($data as $application) {
                    array_push($this->applications[$application->getApplicationStatus()], $application);
                }
                $applications = $this->applications;
                require_once __DIR__ . '/../Views/applicationsManager.html';
            }
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function AcceptApplication($status)
    {
        $application_ID = $_POST['application_ID'];
        try {
            if ($status == 'accept') {
                $status = 'приглашение';
            } else {
                $status = 'отказ';
            }
            $result = $this->model->UpdateApplication($application_ID, $status);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Ошибка при обработке данных');
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Ошибка: ' . $e->getMessage()]);
            exit();
        }
    }

    public function ShowApplicationDetails(int $param)
    {
        try {
            if (AuthHelper::isCandidate()) {
                $vacancy = $this->model->selectApplicationById($param);
                $skills = $this->vacancyModel->selectSkillForVacancy($vacancy->getId());
                $processes = $this->vacancyModel->selectProcessesForVacancy($vacancy->getId());
                $vacancy->setSkills($skills);
                $vacancy->setProcesses($processes);
                $chat = [];
                if($vacancy->getApplicationStatus()=='приглашение' || $vacancy->getApplicationStatus()=='вас приняли' || $vacancy->getApplicationStatus()=='отказ') {
                    $chat = $this->model->getChat($vacancy->getApplicationId());
                }
                require_once __DIR__ . '/../Views/application-page.html';
            } elseif (AuthHelper::isManager()) {
                $vacancy = $this->model->selectApplicationById($param);
                if($vacancy->getApplicationStatus()=='не просмотрен'){
                    $this->model->UpdateApplication($param,'просмотрен');
                    $vacancy->setApplicationStatus('просмотрен');
                }
                $skills = $this->vacancyModel->selectSkillForVacancy($vacancy->getId());
                $processes = $this->vacancyModel->selectProcessesForVacancy($vacancy->getId());
                $vacancy->setSkills($skills);
                $vacancy->setProcesses($processes);
                $candidate = $this->profileModel->SelectCandidate($vacancy->getCandidateId());
                $candidate->setSkills($this->profileModel->selectSkillForCandidate($vacancy->getCandidateId()));
                $percent = $this->getComparison($candidate,$vacancy);
                $current_process_id=0;
                foreach ($processes as $process){
                    if ($process['orderable']==$vacancy->getCurrentProcess()){
                        $current_process_id=$process['process_ID'];
                        break;
                    }
                }
                $interview = $this->interviewModel->selectInterviewByAppIDProcessID($vacancy->getApplicationId(),$current_process_id);
                $chat=[];
                if($vacancy->getApplicationStatus()=='приглашение' || $vacancy->getApplicationStatus()=='вас приняли' || $vacancy->getApplicationStatus()=='отказ') {
                    $chat = $this->model->getChat($vacancy->getApplicationId());
                }
                $isInterviewEnded = false;
                if($interview){
                    date_default_timezone_set('Europe/Moscow');
                    if(strtotime(date('Y-m-d\TH:i:sP'))>=strtotime($interview['date'])){
                        $isInterviewEnded=true;
                    }
                }
                require_once __DIR__ . '/../Views/application-page-manager.html';
            }
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function getComparison(Candidate $candidate, Vacancy $vacancy)
    {
        $sum = 0;
        $all = 2;
        if ($candidate->getExperience() == $vacancy->getExperience()) {
            $sum += 1;
        } elseif ($candidate->getExperience() > $vacancy->getExperience()) {
            $sum += 2;
        }
        foreach ($vacancy->getSkills() as $skill) {
            foreach ($candidate->getSkills() as $candidateSkill) {
                if ($candidateSkill == $skill) {
                    $sum += 1;
                }
            }
            $all += 1;
        }
        return round(($sum/$all)*100);
    }

    public function nextProcess($id)
    {
        try{
            $result = $this->model->NextProcess($id);
            if($result){
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


}