<?php

require_once __DIR__ . '/../Models/ProfileModel.php';

class ProfileController
{
    private $model;

    public function __construct() {
        $this->model = new ProfileModel();
    }

    public function ShowProfileForCandidate()
    {
        $id = $_SESSION['user_id'];
        try{
            $user = $this->model->SelectCandidate($id);
            $data = $user[0];
            $columns = array_keys($data);
        }catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Ошибка подключения к базе данных: ' . $e->getMessage()
            ]);
            return;
        }
        if ($user){
            require_once __DIR__ . '/../Views/profile.html';
        }else{
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
            exit();
        }
    }
}