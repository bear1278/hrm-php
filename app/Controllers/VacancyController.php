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
        $this->model=new VacancyModel();
    }

    public function ShowVacancyDetails(int $id)
    {
        try
        {
            $vacancy = $this->model->getVacancyByID($id);
            $columns = $this->model->getTableColumns();
            $errorImage="";
            if ($vacancy->getImage()==null){
                $vacancy->setImage('/img/vacancy.png');
                $filepath= __DIR__ . Candidate::ADDITION_TO_PATH.$vacancy->getImage();
                $errorImage=ErrorHelper::ImageFileErrorHandlersToView($filepath);
            }else{
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $finfo->buffer($vacancy->getImage());
                $errorImage=ErrorHelper::ImageBlobErrorHandler($mime_type,$vacancy->getImage());
                $data_uri = "data:" . $mime_type . ";base64," . base64_encode($vacancy->getImage());
                $vacancy->setImage($data_uri);
            }
            require_once __DIR__ . '/../Views/vacancy-page.html';
        }catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
        }catch (Exception $e){
            http_response_code(400);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
        }
    }

    public function GetVacancy(int $id)
    {
        try
        {
            $vacancy = $this->model->getVacancyByID($id);
            $skills = $this->model->getVacancySkills($id);
            $vacancyArray=[
                'name'=>$vacancy->getName(),
                'department'=>$vacancy->getDepartment(),
                'description'=>$vacancy->getDescription(),
                'experience'=>$vacancy->getExperience(),
                'salary'=>$vacancy->getSalary(),
                'skills'=>$skills
            ];
            echo json_encode($vacancyArray);
            exit();
        }catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
        }catch (Exception $e){
            http_response_code(400);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
        }
    }

    public function SetNewImage(int $id)
    {
        try
        {
            ErrorHelper::ImageFileErrorHandler($_FILES['image']);
            $fileData = file_get_contents($_FILES['image']['tmp_name']);
            if($this->model->UpdateVacancyImage($id,$fileData)){
                echo json_encode(['success' => true]);
                exit();
            }else{
                throw new Exception('Ошибка записи в базу данных');
            }
        }catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = urlencode('Ошибка подключения к базе данных: ' . $e->getMessage());
            header("Location: /error?message=" . $errorMessage);
        }catch (Exception $e){
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }


}