<?php

namespace app\Controllers;

use app\Helpers\AuthHelper;
use app\Models\ApplicationModel;
use Exception;
use PDOException;

class ApplicationController
{

    private $model;

    public function __construct()
    {
        $this->model = new ApplicationModel();
    }

    public function ShowApplicationsForCandidate()
    {
        try {
            $columns = $this->model->getTableColumns();
            if (AuthHelper::isCandidate()) {
                $columns = array_diff($columns, ['candidate']);
                $data = $this->model->selectApplications($_SESSION['user_id']);
                require_once __DIR__ . '/../Views/applications.html';
            } elseif (AuthHelper::isManager()) {
                $data = $this->model->SelectAllApplicationManager($_SESSION['user_id']);
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
        }
        catch (Exception $e){
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
            $columns = $this->model->getTableColumns();
            if (AuthHelper::isCandidate()) {
                $columns = array_diff($columns, ['candidate']);
                $data = $this->model->SelectApplicationsWithParam($id, $search);
                require_once __DIR__ . '/../Views/applications.html';
            } elseif (AuthHelper::isManager()) {
                $data = $this->model->SelectApplicationsManagerWithParam($id, $search);
                require_once __DIR__ . '/../Views/applicationsManager.html';
            }
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        }
    }

    public function ChangeApplicationStatus($status)
    {
        $id = $_POST['application_ID'];
        try {
            $result = $this->model->UpdateApplication($id, $status);
            if ($result) {
                http_response_code(200);
                header("Location: /applications");
            } else {
                throw new Exception('Ошибка обработки данных');
            }
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            $errorMessage = urlencode($e->getMessage());
            header("Location: /error?message=" . $errorMessage);
        }
    }
}