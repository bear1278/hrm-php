<?php

namespace app\Controllers;


use app\Entities\Candidate;
use app\Models\ProfileModel;
use app\Helpers\ErrorHelper;
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
            $errorImage = "";
            if ($candidate->getImage()==null){
                $candidate->setImage(Candidate::DEFAULT_IMAGE);
            }
            $filepath= __DIR__ . Candidate::ADDITION_TO_PATH.$candidate->getImage();
            $errorImage=ErrorHelper::ImageFileErrorHandlersToView($filepath);
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

    public function SetNewProfileImage()
    {
        try{
            $id=$_SESSION['user_id'];
            $file = $_FILES['image'];
            $imageName = basename($_FILES['image']['name']);
            $filePathForDB = Candidate::DIR_IMG_FOR_VIEW . $id . '_'.date("YmdHis"). "_"  . $imageName;
            $filePath = __DIR__ . Candidate::DIR_IMAGES . $id . '_'.date("YmdHis"). "_" . $imageName;
            ErrorHelper::ImageFileErrorHandler($file);
            ErrorHelper::ImagePathErrorHandler($filePath);
            if($this->model->UpdateImage($id,$filePathForDB)){
                echo json_encode(['success' => true]);
                exit();
            }else{
                throw new Exception('Ошибка записи в базу данных');
            }
        }catch (PDOException $e) {
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