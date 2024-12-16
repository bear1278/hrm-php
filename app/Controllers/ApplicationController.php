<?php

namespace app\Controllers;

use app\Entities\Application;
use app\Entities\Candidate;
use app\Entities\Vacancy;
use app\Helpers\ApplicationHelper;
use app\Helpers\AuthHelper;
use app\Models\ApplicationModel;
use app\Models\InterviewModel;
use app\Models\ProfileModel;
use app\Models\TaskModel;
use app\Models\UserModel;
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
    private $userModel;
    private $taskModel;

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
        $this->userModel = new UserModel();
        $this->taskModel = new TaskModel();
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
                $allEmpty = array_reduce($applications, function ($carry, $item) {
                    return $carry && empty($item);
                }, true);
                require_once __DIR__ . '/../Views/applications.html';
            } elseif (AuthHelper::isManager()) {
                $data = $this->model->SelectAllApplicationManager($_SESSION['user_id']);
                foreach ($data as $application) {
                    array_push($this->applications[$application->getApplicationStatus()], $application);
                }
                $applications = $this->applications;
                $allEmpty = array_reduce($applications, function ($carry, $item) {
                    return $carry && empty($item);
                }, true);
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
                $isInterviewEnded = false;
                $current_order = $vacancy->getCurrentProcess();
                $current_process_id = 0;
                foreach ($processes as $index => $process) {
                    if ($process['orderable'] == $current_order) {
                        $current_process_id = $process['process_ID'];
                    }
                    if ($process['type'] != 'Тестовое задание') {
                        $interview = $this->interviewModel->selectInterviewByAppIDProcessID($vacancy->getApplicationId(), $process['process_ID']);
                        if ($interview) {
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
                        }
                        if ($interview) {
                            $processes[$index]['interview'] = $interview;
                            if ($current_process_id == $process['process_ID']) {
                                date_default_timezone_set('Europe/Moscow');
                                if (strtotime(date('Y-m-d\TH:i:sP')) >= strtotime($interview['date'])) {
                                    $isInterviewEnded = true;
                                }
                            }
                        }
                    } else {
                        $task = $this->taskModel->selectTaskByApplicationId($param);
                        if ($task) {
                            $feedback = $this->taskModel->selectFeedback($task['task_ID']);
                            if ($feedback) {
                                $task['feedback'] = $feedback;
                                $result = ApplicationHelper::getFeedbackResult($feedback);
                                $task['feedbackResult'] = $result;
                                $maxResult = ApplicationHelper::getMaxFeedbackResult($feedback);
                                $task['maxResult'] = $maxResult;
                                if ($maxResult != 0) {
                                    $task['percent'] = round(($result / $maxResult) * 100, 2);
                                } else {
                                    $task['percent'] = 0;
                                }
                            }
                            $processes[$index]['task'] = $task;
                        }
                    }
                }
                $vacancy->setSkills($skills);
                $vacancy->setProcesses($processes);
                $chat = [];
                if ($vacancy->getApplicationStatus() == 'приглашение' || $vacancy->getApplicationStatus() == 'вас приняли' || $vacancy->getApplicationStatus() == 'отказ') {
                    $chat = $this->model->getChat($vacancy->getApplicationId());
                }
                require_once __DIR__ . '/../Views/application-page.html';
            } elseif (AuthHelper::isManager()) {
                $vacancy = $this->model->selectApplicationById($param);
                if ($vacancy->getApplicationStatus() == 'не просмотрен') {
                    $this->model->UpdateApplication($param, 'просмотрен');
                    $vacancy->setApplicationStatus('просмотрен');
                }
                $skills = $this->vacancyModel->selectSkillForVacancy($vacancy->getId());
                $processes = $this->vacancyModel->selectProcessesForVacancy($vacancy->getId());
                $isInterviewEnded = false;
                $current_order = $vacancy->getCurrentProcess();
                $current_process_id = 0;
                $isCurrentProcessTypeTechInterview = false;
                foreach ($processes as $index => $process) {
                    if ($process['orderable'] == $current_order) {
                        $current_process_id = $process['process_ID'];
                        if ($process['type'] == 'Техническое интервью') {
                            $isCurrentProcessTypeTechInterview = true;
                        }
                    }
                    if ($process['type'] != 'Тестовое задание') {
                        $interview = $this->interviewModel->selectInterviewByAppIDProcessID($vacancy->getApplicationId(), $process['process_ID']);

                        if ($interview) {
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
                        }
                        if ($interview) {
                            $processes[$index]['interview'] = $interview;
                            if ($current_process_id == $process['process_ID']) {
                                date_default_timezone_set('Europe/Moscow');
                                if (strtotime(date('Y-m-d\TH:i:sP')) >= strtotime($interview['date'])) {
                                    $isInterviewEnded = true;
                                }
                            }
                        }
                    } else {
                        $task = $this->taskModel->selectTaskByApplicationId($param);
                        if ($task) {
                            $feedback = $this->taskModel->selectFeedback($task['task_ID']);
                            if ($feedback) {
                                $task['feedback'] = $feedback;
                                $result = ApplicationHelper::getFeedbackResult($feedback);
                                $task['feedbackResult'] = $result;
                                $maxResult = ApplicationHelper::getMaxFeedbackResult($feedback);
                                $task['maxResult'] = $maxResult;
                                if ($maxResult != 0) {
                                    $task['percent'] = round(($result / $maxResult) * 100, 2);
                                } else {
                                    $task['percent'] = 0;
                                }
                            }
                            $processes[$index]['task'] = $task;
                        }
                    }
                }
                $vacancy->setSkills($skills);
                $vacancy->setProcesses($processes);
                $candidate = $this->profileModel->SelectCandidate($vacancy->getCandidateId());
                $candidate->setSkills($this->profileModel->selectSkillForCandidate($vacancy->getCandidateId()));
                $percent = ApplicationHelper::getComparison($candidate, $vacancy);
                $chat = [];
                if ($vacancy->getApplicationStatus() == 'приглашение' || $vacancy->getApplicationStatus() == 'вас приняли' || $vacancy->getApplicationStatus() == 'отказ') {
                    $chat = $this->model->getChat($vacancy->getApplicationId());
                }
                $interviewers = $this->userModel->selectInterviewersByDepartment($vacancy->getDepartment());

                require_once __DIR__ . '/../Views/application-page-manager.html';
            }
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function nextProcess($id)
    {
        try {
            $process = $this->model->getCurrentProcess($id);
            $result = $this->model->NextProcess($id, $process[0], $_POST['process']);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Ошибка изменения текущего этапа');
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

    public function nextSetInterviewerForTask($id)
    {
        try {
            $interviewer_id = $_POST['user'];
            $process = $_POST['process'];
            $result = $this->taskModel->SetInterviewerForTask($id, $interviewer_id, $process);
            if ($result) {
                header("Location: http://localhost/application/$id");
            } else {
                throw new Exception('Ошибка изменения текущего этапа');
            }
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