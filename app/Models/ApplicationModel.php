<?php

namespace app\Models;

use app\Entities\Application;
use Exception;
use PDO;
use PDOException;

class ApplicationModel
{

    protected $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function selectApplicationById($applicationId)
    {
        try {
            $sql = "SELECT V.vacancy_ID as id, application_ID, CONCAT(last_name,' ',first_name) as candidate,user_ID, V.name, D.name as department, description, experience_required as experience, 
                salary, posting_date as `posting date`,
                application_date as `application date`, A.status as `application status`,current_process
                FROM vacancies as V
                INNER JOIN applications as A 
                ON V.vacancy_ID = A.vacancy_ID
                INNER JOIN departments as D
                ON V.department_ID = D.department_id
                INNER JOIN users as U 
                ON A.candidate_ID = U.user_ID
                WHERE A.application_ID = :applicationId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':applicationId', $applicationId, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $app =  new Application(
                    $row['application_ID'],
                    $row['candidate'],
                    $row['application date'],
                    $row['application status'],
                    $row['name'],
                    $row['department'],
                    $row['description'],
                    $row['experience'],
                    $row['salary'],
                    $row['posting date'],
                    $row['current_process']
                );
                $app->setId($row['id']);
                $app->setCandidateId($row['user_ID']);
                return $app;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function selectApplications($id)
    {
        try{
            $sql = "SELECT application_ID, V.name,D.name as department,description,experience_required as experience,salary,posting_date as `posting date`,
            application_date as `application date`,A.status as `application status`,current_process 
            FROM vacancies as V 
            INNER JOIN applications as A 
            ON V.vacancy_ID=A.vacancy_ID 
            INNER JOIN
            departments as D
            ON V.department_ID = D.department_id
            Where candidate_ID= :id ORDER BY application_date DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $applications = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $applications[] = new Application(
                    $row['application_ID'],
                    null,
                    $row['application date'],
                    $row['application status'],
                    $row['name'],
                    $row['department'],
                    $row['description'],
                    $row['experience'],
                    $row['salary'],
                    $row['posting date'],
                    $row['current_process']
                );
            }
            return $applications;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectAllApplicationManager($id)
    {
        try{
            $sql = "SELECT CONCAT(last_name,' ',first_name) as candidate,  application_ID, V.name,D.name as department,description,experience_required as experience,salary,posting_date as `posting date`,
            application_date as `application date`,A.status as `application status`, current_process 
            FROM vacancies as V 
            INNER JOIN applications as A 
            ON V.vacancy_ID=A.vacancy_ID 
            INNER JOIN
            departments as D
            ON V.department_ID = D.department_id
            INNER JOIN users as U
            ON U.user_ID=A.candidate_ID 
            Where author= :id ORDER BY application_date DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $applications = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $applications[] = new Application(
                    $row['application_ID'],
                    $row['candidate'],
                    $row['application date'],
                    $row['application status'],
                    $row['name'],
                    $row['department'],
                    $row['description'],
                    $row['experience'],
                    $row['salary'],
                    $row['posting date'],
                    $row['current_process']
                );
            }
            return $applications;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }


    public function createApplication($vacancy_ID, $candidate_ID)
    {
        try{
            $this->pdo->beginTransaction();
            $result = $this->insertApplication($vacancy_ID, $candidate_ID);
            if (!$result){
                throw new PDOException('Insert application with error');
            }
            $result = $this->InsertHistory('apply',$vacancy_ID, $candidate_ID);
            if (!$result){
                throw new PDOException('Insert in history with error');
            }
            $this->pdo->commit();
            return true;
        }
        catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function DeleteApplication($id)
    {
        try{
            $this->pdo->beginTransaction();
            $app = $this->SelectApplicationByIdForDelete($id);
            if (empty($app)){
                throw new PDOException('Select by id with error');
            }
            $result = $this->DeleteFromApplication($id);
            if (!$result){
                throw new PDOException('Insert application with error');
            }
            $result = $this->InsertHistory('unapply',$app['vacancy_ID'], $app['candidate_ID']);
            if (!$result){
                throw new PDOException('Insert in history with error');
            }
            $this->pdo->commit();
            return true;
        }
        catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function insertApplication($vacancy_ID, $candidate_ID)
    {
        try {
            $sql = "INSERT INTO applications (vacancy_ID, candidate_ID, application_date,status) VALUES (:vacancy_ID, :candidate_ID, :application_date,:status)";
            $stmt = $this->pdo->prepare($sql);
            $status = 'не просмотрен';
            $current =0;
            $date = date("Y-m-d H:i:s");
            $stmt->bindParam(':vacancy_ID', $vacancy_ID);
            $stmt->bindParam(':candidate_ID', $candidate_ID);
            $stmt->bindParam(':application_date', $date);
            $stmt->bindParam(':status', $status);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function InsertHistory($action,$vacancy_ID,$candidate_ID)
    {
        try{
            $sql = "INSERT INTO user_history (user_ID,vacancy_ID,action,creating_date) values (:user_ID,:vacancy_ID,:action,:date)";
            $stmt = $this->pdo->prepare($sql);
            $date = date("Y-m-d H:i:s");
            $stmt->bindParam(':vacancy_ID', $vacancy_ID);
            $stmt->bindParam(':user_ID', $candidate_ID);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':action', $action);
            return $stmt->execute();
        }catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function DeleteFromApplication($id)
    {
        try{
            $sql = "DELETE FROM applications 
                    where application_ID= :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        }catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectApplicationsWithParam($id,$search)
    {
        try {
            $sql = "SELECT application_ID, V.name,D.name as department,description,experience_required as experience,salary,posting_date as `posting date`,
            application_date as `application date`,A.status as `application status`,current_process 
            FROM vacancies as V 
            INNER JOIN applications as A 
            ON V.vacancy_ID=A.vacancy_ID 
            INNER JOIN
            departments as D
            ON V.department_ID = D.department_id
            Where candidate_ID= :id and V.name LIKE :search";
            $stmt = $this->pdo->prepare($sql);
            $search = "%".$search."%";
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':search', $search,PDO::PARAM_STR);
            $stmt->execute();
            $applications = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $applications[] = new Application(
                    $row['application_ID'],
                    null,
                    $row['application date'],
                    $row['application status'],
                    $row['name'],
                    $row['department'],
                    $row['description'],
                    $row['experience'],
                    $row['salary'],
                    $row['posting date'],
                    $row['current_process']
                );
            }
            return $applications;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectApplicationsManagerWithParam($id,$search)
    {
        try {
            $sql = "SELECT CONCAT(last_name,' ',first_name) as candidate,  application_ID, V.name,D.name as department,description,experience_required as experience,salary,posting_date as `posting date`,
            application_date as `application date`,A.status as `application status`, current_process 
            FROM vacancies as V 
            INNER JOIN applications as A 
            ON V.vacancy_ID=A.vacancy_ID 
            INNER JOIN
            departments as D
            ON V.department_ID = D.department_id
            INNER JOIN users as U
            ON U.user_ID=A.candidate_ID 
            Where author= :id and V.name LIKE :search";
            $stmt = $this->pdo->prepare($sql);
            $search = "%".$search."%";
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':search', $search,PDO::PARAM_STR);
            $stmt->execute();
            $applications = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $applications[] = new Application(
                    $row['application_ID'],
                    $row['candidate'],
                    $row['application date'],
                    $row['application status'],
                    $row['name'],
                    $row['department'],
                    $row['description'],
                    $row['experience'],
                    $row['salary'],
                    $row['posting date'],
                    $row['current_process']
                );
            }
            return $applications;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function UpdateApplication($id,$status)
    {
        try{
            $this->pdo->beginTransaction();
            if($status=='приглашение'){
                $sql="UPDATE applications 
                SET current_process=current_process+1
                where applications.application_ID=:id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':id',$id);
                $result=  $stmt->execute();
                if(!$result){
                    $this->pdo->rollBack();
                    return false;
                }
            }elseif($status=='отказ'){
                $process = $this->getCurrentProcess($id)[0];

                $isInterview = true;
                if($process['type']=='Тестовое задание'){
                    $isInterview = false;
                }
                $item_id = 0;
                if($isInterview){
                    $item_id = $this->selectInterviewId($id)['interview_ID'];
                }else{
                    $item_id = $this->selectTaskIdByApplication($id)['task_ID'];
                }
                if(!$item_id){
                    $this->pdo->rollBack();
                    return false;
                }
                $sql = "UPDATE " . ($isInterview ? "interviews":"tasks" ) . " set result= 'не пройдено'  WHERE " . ($isInterview ?  "interview_ID":"task_ID" ) . "=:id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':id', $item_id);
                $result = $stmt->execute();
                if(!$result){
                    $this->pdo->rollBack();
                    return false;
                }
                $sql="UPDATE applications 
                SET current_process=0
                where applications.application_ID=:id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':id',$id);
                $result=  $stmt->execute();
                if(!$result){
                    $this->pdo->rollBack();
                    return false;
                }
            }
            $sql="UPDATE applications 
            SET status= :status
            where applications.application_ID=:id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id',$id);
            $stmt->bindParam(':status',$status);
            $result=  $stmt->execute();
            if(!$result){
                $this->pdo->rollBack();
                return false;
            }
            $this->pdo->commit();
            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectApplicationByIdForDelete($id)
    {
        try{
            $sql = "SELECT * FROM applications where application_ID = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function getChat($id)
    {
        try{
            $sql = "SELECT * FROM chat where application_ID = :id ORDER BY date";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function getCurrentProcess($id)
    {
        try{
            $sql = "SELECT p.process_ID, p.type, p.orderable FROM processes as p
            INNER JOIN processes_vacancy as pv
            on p.process_ID = pv.process_ID 
            INNER JOIN applications as a
            ON a.vacancy_ID=pv.vacancy_ID
            where application_ID=:id and p.orderable=(SELECT current_process from applications where application_ID=:id)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function NextProcess($app_id,$process,$all_process)
    {
        try{
            $this->pdo->beginTransaction();

            if($process['orderable']==$all_process){
                $sql = "UPDATE applications SET status='вас приняли' WHERE application_ID=:id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':id', $app_id);
                $result = $stmt->execute();
                if(!$result){
                    $this->pdo->rollBack();
                    return false;
                }
            }

            $isInterview = true;
            if($process['type']=='Тестовое задание'){
                $isInterview = false;
            }
            $id = 0;
            if($isInterview){
                $id = $this->selectInterviewId($app_id)['interview_ID'];
            }else{
                $id = $this->selectTaskIdByApplication($app_id)['task_ID'];
            }
            if($id==0){
                $this->pdo->rollBack();
                return false;
            }
            $sql = "UPDATE " . ($isInterview ? "interviews":"tasks" ) . " set result= 'пройдено'  WHERE " . ($isInterview ?  "interview_ID":"task_ID" ) . "=:id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $result = $stmt->execute();
            if(!$result){
                $this->pdo->rollBack();
                return false;
            }
            $sql = "UPDATE applications SET current_process=current_process+1 WHERE application_ID=:id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $app_id);
            $result = $stmt->execute();
            if(!$result){
                $this->pdo->rollBack();
                return false;
            }
            $this->pdo->commit();
            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function selectInterviewId($id)
    {
        try{
            $sql = "SELECT interview_ID FROM interviews 
                    where application_ID = :id AND
                    process_ID = (SELECT p.process_ID from processes as p
                                INNER JOIN processes_vacancy as pv 
                                on p.process_ID = pv.process_ID
                                where vacancy_ID = (select vacancy_ID from applications where application_ID=:id)
                                AND orderable = (select current_process from applications where application_ID=:id))";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    private function selectTaskIdByApplication($app_id)
    {
        try{
            $sql = "SELECT task_ID from tasks where application_ID=:id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $app_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

}