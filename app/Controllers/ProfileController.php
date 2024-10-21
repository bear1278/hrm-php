<?php

namespace app\Controllers;


use app\Models\ProfileModel;
use Exception;
use PDOException;

class ProfileController
{
    private $model;

    public function __construct()
    {
        $this->model = new ProfileModel();
    }

    public function ShowProfileForCandidate()
    {
        $id = $_SESSION['user_id'];
        try {
            $candidate = $this->model->SelectCandidate($id);
            $columns = $this->model->SelectColumns();
            if ($candidate) {
                require_once __DIR__ . '/../Views/profile.html';
            } else {
                throw new Exception('Ошибка обработки данных');
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Ошибка подключения к базе данных: ' . $e->getMessage()
            ]);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }
}