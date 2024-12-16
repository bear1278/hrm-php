<?php

namespace app\Controllers;

use app\Helpers\ApplicationHelper;
use app\Models\ApplicationModel;
use app\Models\InterviewModel;
use app\Models\ProfileModel;
use app\Models\VacancyModel;
use Exception;
use PDOException;

class InterviewerController
{
    private $interviewModel;
    private $applicationModel;
    private $interviews;
    private $vacancyModel;
    private $profileModel;

    public function __construct()
    {
        $this->interviewModel= new InterviewModel();
        $this->applicationModel = new ApplicationModel();
        $this->vacancyModel = new VacancyModel();
        $this->profileModel = new ProfileModel();
        $this->interviews = [
            'Ждет ревью'=>[],
            'Архив'=>[],
            'Не проведено'=>[]
        ];
    }

    public function showDashboard()
    {
        try{
            $interviews = $this->interviewModel->selectInterviewByUser($_SESSION['user_id']);
            foreach ($interviews as $key => $interview){
                $application = $this->applicationModel->selectApplicationById($interview['application_ID']);
                $interviews[$key]['application']=$application;
            }
            foreach ($interviews as $key => $interview){
                if($interview['result']!='нет'){
                    array_push($this->interviews['Архив'],$interview);
                }elseif (strtotime(date('Y-m-d\TH:i:sP'))>=strtotime($interview['date'])){
                    array_push($this->interviews['Ждет ревью'],$interview);
                }else{
                    array_push($this->interviews['Не проведено'],$interview);
                }
            }
            $tabs = $this->interviews;
            require_once __DIR__.'/../Views/interviews.html';
            exit();
        }catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function getInterviewPage($id)
    {
        try{
            $interview = $this->interviewModel->selectInterviewById($id);
            $vacancy = $this->applicationModel->selectApplicationById($interview['application_ID']);
            $skills = $this->vacancyModel->selectSkillForVacancy($vacancy->getId());
            $vacancy->setSkills($skills);
            $candidate = $this->profileModel->SelectCandidate($vacancy->getCandidateId());
            $candidate->setSkills($this->profileModel->selectSkillForCandidate($vacancy->getCandidateId()));
            $percent = ApplicationHelper::getComparison($candidate,$vacancy);
            $isInterviewEnded=false;
            date_default_timezone_set('Europe/Moscow');
            if(strtotime(date('Y-m-d\TH:i:sP'))>=strtotime($interview['date'])){
                $isInterviewEnded=true;
            }
            $feedback = $this->interviewModel->selectFeedback($interview['interview_ID']);
            if ($feedback) {
                $interview['feedback'] = $feedback;
                $result = ApplicationHelper::getFeedbackResult($feedback);
                $interview['feedbackResult'] = $result;
                $maxResult = ApplicationHelper::getMaxFeedbackResult($feedback);
                $interview['maxResult'] = $maxResult;
                if ($maxResult != 0) {
                    $interview['percent'] = round(($result / $maxResult) * 100, 2);
                } else {
                    $interview['percent'] = 0;
                }
            }
            require_once __DIR__.'/../Views/interview.html';
            exit();
        }catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function setInterviewFeedback($id)
    {
        try{
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $skills = $data['skills'];
            $result = trim($data['result']);
            $isOk = $this->interviewModel->feedback($id,$skills,$result);
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
}