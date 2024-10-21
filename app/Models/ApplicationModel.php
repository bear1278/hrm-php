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
            $sql = "SELECT application_ID, candidate_ID, V.name, D.name as department, description, experience_required as experience, 
                salary, posting_date as `posting date`, S.name as `vacancy status`, 
                application_date as `application date`, St.name as `application status`
                FROM vacancies as V
                INNER JOIN applications as A 
                ON V.vacancy_ID = A.vacancy_ID
                INNER JOIN departments as D
                ON V.department_ID = D.department_id
                INNER JOIN status as S
                ON V.status = S.status_ID
                INNER JOIN status as St
                ON A.status = St.status_ID
                WHERE A.application_ID = :applicationId";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':applicationId', $applicationId, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return new Application(
                    $row['application_ID'],
                    $row['candidate_ID'],
                    $row['application date'],
                    $row['application status'],
                    $row['name'],
                    $row['department'],
                    $row['description'],
                    $row['experience'],
                    $row['salary'],
                    $row['posting date'],
                    $row['vacancy status']
                );
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
            S.name as `vacancy status`,application_date as `application date`,St.name as `application status` 
            FROM vacancies as V 
            INNER JOIN applications as A 
            ON V.vacancy_ID=A.vacancy_ID 
            INNER JOIN
            departments as D
            ON V.department_ID = D.department_id  
            INNER JOIN status as S
            ON V.status = S.status_ID 
            INNER JOIN status as St
            ON A.status = St.status_ID 
            Where candidate_ID= :id";
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
                    $row['vacancy status']
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
            S.name as `vacancy status`,application_date as `application date`,St.name as `application status` 
            FROM vacancies as V 
            INNER JOIN applications as A 
            ON V.vacancy_ID=A.vacancy_ID 
            INNER JOIN
            departments as D
            ON V.department_ID = D.department_id
            INNER JOIN status as S
            ON V.status = S.status_ID 
            INNER JOIN status as St
            ON A.status = St.status_ID 
            INNER JOIN users as U
            ON U.user_ID=A.candidate_ID 
            Where author= :id";
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
                    $row['vacancy status']
                );
            }
            return $applications;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function getTableColumns() {
        try{
            $sql = "SELECT CONCAT(last_name,' ',first_name) as candidate,V.name,D.name as department,description,experience_required as experience,salary,posting_date as `posting date`,
            V.status as `vacancy status`,application_date as `application date`,A.status as `application status` 
            FROM vacancies as V 
            INNER JOIN applications as A 
            ON V.vacancy_ID=A.vacancy_ID 
            INNER JOIN
            departments as D
            ON V.department_ID = D.department_id
            INNER JOIN users as U
            ON U.user_ID=A.candidate_ID 
            LIMIT 1";
            $query = $this->pdo->query($sql);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                return array_keys($result[0]);
            } else {
                throw new Exception("No records found.");
            }
        }catch (PDOException $e) {
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
            $app = $this->SelectApplicationByIdForDelete($id);
            if (empty($app)){
                throw new PDOException('Select by id with error');
            }
            $this->pdo->beginTransaction();
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
            $status = 1;
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
            S.name as `vacancy status`,application_date as `application date`,St.name as `application status` 
            FROM vacancies as V 
            INNER JOIN applications as A 
            ON V.vacancy_ID=A.vacancy_ID 
            INNER JOIN
            departments as D
            ON V.department_ID = D.department_id
            INNER JOIN status as S
            ON V.status = S.status_ID 
            INNER JOIN status as St
            ON A.status = St.status_ID 
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
                    $row['vacancy status']
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
            S.name as `vacancy status`,application_date as `application date`,St.name as `application status` 
            FROM vacancies as V 
            INNER JOIN applications as A 
            ON V.vacancy_ID=A.vacancy_ID 
            INNER JOIN
            departments as D
            ON V.department_ID = D.department_id
            INNER JOIN status as S
            ON V.status = S.status_ID 
            INNER JOIN status as St
            ON A.status = St.status_ID 
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
                    $row['vacancy status']
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
            $sql="UPDATE applications 
            SET status= :status
            where applications.application_ID=:id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id',$id);
            $stmt->bindParam(':status',$status);
            return $stmt->execute();
        } catch (PDOException $e) {
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
}