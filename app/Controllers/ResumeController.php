<?php

namespace app\Controllers;

use app\Entities\Candidate;
use app\Models\CandidateModel;
use app\Models\VacancyModel;
use Exception;
use PDOException;

class ResumeController
{

    private $model;
    private $vacancyModel;

    public function __construct()
    {
        $this->model = new CandidateModel();
        $this->vacancyModel = new VacancyModel();
    }

    public function ShowResume()
    {
        $skills = $this->vacancyModel->SelectAllSkills();
        require_once __DIR__ . '/../Views/resume_form.html';
    }

    public function SaveResume()
    {
        try {
            $candidate = new Candidate($_SESSION['user_id'],
                null,
                null,
                null,
                trim($_POST['phone_number']),
                trim($_POST['resume']),
                (int)trim($_POST['experience_years']),
                trim($_POST['location']), 1,null,trim($_POST['position']),$_POST['skills']);
            $oldCandidate = $this->model->findCandidateById($candidate->getId());
            if ($oldCandidate) {
                $result = $this->model->updateCandidate($candidate->getId(),
                    $candidate->getPhone(),
                    $candidate->getResume(),
                    $candidate->getExperience(),
                    $candidate->getLocation(),
                    $candidate->getStatus(),
                    $candidate->getPosition(),
                    $candidate->getSkills());
            } else {
                $result = $this->model->createCandidate($candidate->getId(),
                    $candidate->getPhone(),
                    $candidate->getResume(),
                    $candidate->getExperience(),
                    $candidate->getLocation(),
                    $candidate->getStatus(),
                    $candidate->getPosition(),
                    $candidate->getSkills());
            }
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
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }
}