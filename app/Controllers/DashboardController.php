<?php

namespace app\Controllers;

use app\Entities\Vacancy;
use app\Helpers\AuthHelper;
use app\Models\VacancyModel;
use Exception;
use PDOException;


require_once __DIR__ . '/../Models/VacancyModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';


class DashboardController
{
    private $model;

    public function __construct()
    {
        $this->model = new VacancyModel();
    }

    public function showDashboard()
    {
        try {
            $columns = $this->model->getTableColumns();
            $columns_type = $this->model->getColumnsType();
            $departments = $this->model->SelectAllDepartments();
            $skills = $this->model->SelectAllSkills();
            if (AuthHelper::isCandidate()) {
                $data = $this->model->SelectVacanciesForCandidate($_SESSION['user_id']);
                require_once __DIR__ . '/../Views/dashboardCandidate.html';
                exit();
            } elseif (AuthHelper::isManager()) {
                $data = $this->model->getVacancies($_SESSION['user_id']);
                require_once __DIR__ . '/../Views/dashboard.html';
                exit();
            } elseif (AuthHelper::isAdmin()) { // ToDo: think about store many models and implement it
                $data = $this->model->SelectAllUsers($_SESSION['user_id']);
                $columns = array_keys($data[0]);
                $columns = array_diff($columns, ['user_ID']);
                $roles = $this->model->SelectRoles();
                require_once __DIR__ . '/../Views/admin.html';
                exit();
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
            exit();
        }
    }

    public function displaySearchResult()
    {
        try {
            $columns = $this->model->getTableColumns();
            $columns_type = $this->model->getColumnsType();
            if (AuthHelper::isManager()) {
                $search = $_POST['search'];
                if (empty($search)) {
                    $this->showDashboard();
                    return;
                }
                $data = $this->model->getVacanciesSearchManager($_SESSION['user_id'], $search);
                require_once __DIR__ . '/../Views/dashboard.html';
                return;
            }
            if (AuthHelper::isCandidate()) {
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
                $search = $data['search'];
                if (empty($search)) {
                    http_response_code(301);
                    return;
                }
                $data = $this->model->getVacanciesWithParamForCandidate($search, $_SESSION['user_id']);
                $this->sendJsonResponse([
                    'data' => $this->serializeData($data),
                    'columns' => $columns,
                    'columns_type' => $columns_type
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            $this->sendJsonResponse([
                'error' => 'Ошибка подключения к базе данных: ' . $e->getMessage()
            ]);
            return;
        } catch (Exception $e) {
            http_response_code(400);
            $this->sendJsonResponse([
                'error' => $e->getMessage()
            ]);
            exit();
        }
    }

    public function serializeData($data): array
    {
        return array_map(function ($vacancy) {
            $dataArray = [];
            foreach (Vacancy::fieldMapping as $key => $method) {
                if (method_exists($vacancy, $method)) {
                    $value = $vacancy->$method();
                    if ($method === 'getPostingDate') {
                        $value = $value->format('Y-m-d');
                    }
                    $dataArray[$key] = $value;
                }
            }
            return $dataArray;
        }, $data);
    }

    private function sendJsonResponse($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function deleteVacancy($id)
    {
        header('Content-Type: application/json');
        try {
            $result = $this->model->deleteVacancyFromDB($id);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка при удалении']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }

    public function editVacancy()
    {
        header('Content-Type: application/json');
        try {
            $vacancy = new Vacancy((int)trim($_POST['vacancy_ID']),
                trim($_POST['name']),
                trim($_POST['department_ID']),
                trim($_POST['description']),
                (int)trim($_POST['experience_required']),
                trim($_POST['salary']),
                null,
                2,
                $_SESSION['user_id'],
                $_POST['skills']);
            $oldVacancy = $this->model->getVacancyByID($vacancy->getId());
            $vacancy->copy($oldVacancy[0]);
            $result = $this->model->UpdateVacancyAndSkills($vacancy);
            if ($result) {
                echo json_encode(['success' => true]);
                exit();
            } else {
                throw new Exception('Ошибка записи в базу данных');
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        }
        catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        }
    }

    public
    function addVacancy()
    {
        header('Content-Type: application/json');
        try {
            $vacancy = new Vacancy(null, trim($_POST['name']),
                trim($_POST['department_ID']),
                trim($_POST['description']),
                (int)trim($_POST['experience_required']),
                trim($_POST['salary']),
                date("Y-m-d H:i:s"),
                1,
                $_SESSION['user_id'],
                $_POST['skills']);
            $result = $this->model->InsertNewVacancy($vacancy);
            if ($result) {
                echo json_encode(['success' => true]);
                exit();
            } else {
                throw new Exception('Ошибка при записи в базу данных');
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }

}
