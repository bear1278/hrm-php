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
    const SECRET_KEY = 'kjnvkernvejnvenocms';
    const METHOD = 'aes-256-cbc';
    private $model;

    public function __construct()
    {
        $this->model = new VacancyModel();
    }

    public function showDashboard()
    {
        try {
            $columns = $this->model->getTableColumns();
            $columns = array_diff($columns, ["image"]);
            $columns_type = $this->model->getColumnsType();
            $departments = $this->model->SelectAllDepartments();
            $skills = $this->model->SelectAllSkills();
            $number_of_app = $this->model->SelectNumberOfApps($_SESSION['user_id']);
            if (AuthHelper::isCandidate()) {
                if (isset($_COOKIE['filtersData'.$_SESSION['user_id']]) && $encryptedFilters = $_COOKIE['filtersData'.$_SESSION['user_id']]) {
                    $encryptedFilters = $_COOKIE['filtersData'.$_SESSION['user_id']];
                    $decodedFilters = $this->decryptData($encryptedFilters,$_SESSION['user_id']%10);
                    $filters = json_decode($decodedFilters, true);
                    $data = $this->model->getVacanciesWithParamForCandidate($filters, $_SESSION['user_id']);
                } else {
                    $data = $this->model->SelectVacanciesForCandidate($_SESSION['user_id']);
                }
                require_once __DIR__ . '/../Views/dashboardCandidate.html';
                exit();
            } elseif (AuthHelper::isManager()) {
                $vacancy = null;
                if(isset($_SESSION['vacancy'])){
                    $vacancy = $_SESSION['vacancy'];
                }
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

        private function encryptData(string $text, int $shift): string
        {
            $text = base64_encode($text);
            $result = "";
            for ($i = 0; $i < strlen($text); $i++) {
                $char = $text[$i];
                if (ctype_alpha($char)) {
                    $ascii = ord($char);
                    $base = ctype_lower($char) ? 97 : 65;
                    $result .= chr(($ascii - $base + $shift) % 26 + $base);
                } else {
                    $result .= $char;
                }
            }
            return $result;
        }

        private function decryptData(string $text,int $shift)
        {
            $shift =26 - $shift;
            $result = "";
            for ($i = 0; $i < strlen($text); $i++) {
                $char = $text[$i];
                if (ctype_alpha($char)) {
                    $ascii = ord($char);
                    $base = ctype_lower($char) ? 97 : 65;
                    $result .= chr(($ascii - $base + $shift) % 26 + $base);
                } else {
                    $result .= $char;
                }
            }
            return base64_decode($result);
        }

        public function displaySearchResult()
        {
            try {
                $columns = $this->model->getTableColumns();
                $columns = array_diff($columns, ["image"]);
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
                    $column = trim($_POST['column']);
                    $value = trim($_POST['value']);
                    $newFilter = [
                        'column' => $column,
                        'value' => $value
                    ];
                    if ($value == "") {
                        header('Location: http://localhost');
                        exit();
                    }
                    $filters = [];
                    $id = $_SESSION['user_id'];
                    if (isset($_COOKIE["filtersData$id"]) && ($encryptedFilters = $_COOKIE["filtersData$id"])) {
                        $decodedFilters = $this->decryptData($encryptedFilters,$_SESSION['user_id']%10);
                        $filters = json_decode($decodedFilters, true);
                        $isNotThereSuchColumn = true;
                        foreach ($filters as $index => $filter) {
                            if ($filter['column'] == $column) {
                                $filters[$index]['value'] = $value;
                                $isNotThereSuchColumn = false;
                            }
                        }
                        if ($isNotThereSuchColumn) {
                            array_push($filters, $newFilter);
                        }
                        $updatedFilters = $this->encryptData(json_encode($filters),$_SESSION['user_id']%10);
                        setcookie('filtersData'.$id, $updatedFilters, time() + 3600, '/', '', true);
                        header('Location: http://localhost');
                        exit();
                    } else {
                        array_push($filters, $newFilter);
                        $updatedFilters = $this->encryptData(json_encode($filters),$_SESSION['user_id']%10);
                        setcookie('filtersData'.$id, $updatedFilters, time() + 3600, '/', '', true);
                    }
                    header('Location: http://localhost');
                    exit();
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
                $_POST['skills'],
                null);
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
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
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
                $_POST['skills'],
                null);
            $result = $this->model->InsertNewVacancy($vacancy);
            if ($result) {
                $_SESSION['vacancy']=$vacancy;
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

    public function deleteColumnFromFilter()
    {
        $column = trim($_POST['delete-column']);
        if (isset($_COOKIE['filtersData'.$_SESSION['user_id']]) && ($encryptedFilters = $_COOKIE['filtersData'.$_SESSION['user_id']])) {
            $decodedFilters = $this->decryptData($encryptedFilters,$_SESSION['user_id']%10);
            $filters = json_decode($decodedFilters, true);
            $indexDelete = null;
            foreach ($filters as $index => $filter) {
                if ($filter['column'] == $column) {
                    $indexDelete = $index;
                    break;
                }
            }
            unset($filters[$indexDelete]);
            if (!empty($filters)){
                $updatedFilters = $this->encryptData(json_encode($filters),$_SESSION['user_id']%10);
                setcookie('filtersData'.$_SESSION['user_id'], $updatedFilters, time() + 3600 * 24, '/', '', true);
            }else{
                setcookie('filtersData'.$_SESSION['user_id'], "", time() + 3600 * 24, '/', '', true);
            }
            header("Location: http://localhost");
            exit();
        }
    }

    public function getSecretKey()
    {
        echo json_encode(['key'=>$_SESSION['user_id']]);
    }
}
