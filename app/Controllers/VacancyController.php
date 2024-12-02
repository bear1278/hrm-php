<?php

namespace app\Controllers;

use app\Entities\Candidate;
use app\Models\VacancyModel;
use app\Helpers\ErrorHelper;
use Exception;
use finfo;
use PDOException;

class VacancyController
{
    private $model;

    public function __construct()
    {
        $this->model = new VacancyModel();
    }

    public function ShowVacancyDetails(int $id)
    {
        try {
            $vacancy = $this->model->getVacancyByID($id);
            $vacancy->setSkills($this->model->selectSkillForVacancy($id));
            $array = $this->model->selectProcessesForVacancy($id);
            usort($array,function($a, $b) {
                return $a['orderable'] <=> $b['orderable'];
            });
            $vacancy->setProcesses($array);
            $canBeApplied = $this->model->SelectApplicationByVacancy($id,$_SESSION['user_id']);
            $errorImage = "";
            require_once __DIR__ . '/../Views/vacancy-page.html';
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
        } catch (Exception $e) {
            http_response_code(400);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
        }
    }

    public function GetVacancy(int $id)
    {
        try {
            $vacancy = $this->model->getVacancyByID($id);
            $skills = $this->model->getVacancySkills($id);
            $vacancyArray = [
                'name' => $vacancy->getName(),
                'department' => $vacancy->getDepartment(),
                'description' => $vacancy->getDescription(),
                'experience' => $vacancy->getExperience(),
                'salary' => $vacancy->getSalary(),
                'skills' => $skills
            ];
            echo json_encode($vacancyArray);
            exit();
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
        } catch (Exception $e) {
            http_response_code(400);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
        }
    }
}