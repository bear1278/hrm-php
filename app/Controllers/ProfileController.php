<?php

namespace app\Controllers;


use app\Entities\Candidate;
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
            $errorImage = "";
            if ($candidate->getImage()==null){
                $candidate->setImage(Candidate::DEFAULT_IMAGE);
            }
            $filepath= __DIR__ . Candidate::ADDITION_TO_PATH.$candidate->getImage();
            $imageInfo = @getimagesize($filepath);
            if (!file_exists($filepath)) {
                $errorImage = "Файл не найден.";
            } elseif (!is_readable($filepath)) {
                $errorImage = "Ограничен доступ к файлу.";
            } elseif (!is_file($filepath)) {
                $errorImage = "Указанный путь не является файлом.";
            } else {
                $imageInfo = @getimagesize($filepath);
                if ($imageInfo === false) {
                    $errorImage = "Файл повреждён или не является изображением.";
                }
            }

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
            if($_FILES['image']['size']>2*1024*1024){
                throw new Exception('Размер файла не должен превышать 2мб');
            }
            $imageInfo = @getimagesize($_FILES['image']['tmp_name']);
            if ($imageInfo === false) {
                throw new Exception("Файл повреждён или не является изображением.");
            }
            $imageName = basename($_FILES['image']['name']);
            $filePathForDB = Candidate::DIR_IMG_FOR_VIEW . $id . '_' . $imageName;
            $filePath = __DIR__ . Candidate::DIR_IMAGES . $id . '_' . $imageName;

            if (!is_dir(__DIR__ . Candidate::DIR_IMAGES)){
                throw new Exception("Ошибка загрузки на сервер");
            }

            if (!is_writable(__DIR__ . Candidate::DIR_IMAGES)){
                throw new Exception("Ограничен доступ к директории для хранения.");
            }

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
                throw new Exception("Failed to move uploaded file to " . $filePath);
            }
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