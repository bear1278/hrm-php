<?php
require_once __DIR__ . '/../../config/database.php';



class ApplicationModel
{

    protected $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function selectApplications($id)
    {
        try{
            $sql = "SELECT application_ID, V.name,D.name as department,description,experience_required as experience,salary,posting_date as `postingdate`,
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectAllApplicationManager($id)
    {
        try{
            $sql = "SELECT CONCAT(last_name,' ',first_name) as candidate,  application_ID, V.name,D.name as department,description,experience_required as experience,salary,posting_date as `postingdate`,
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function getTableColumns() {
        try{
            $sql = "SELECT CONCAT(last_name,' ',first_name) as candidate,V.name,D.name as department,description,experience_required as experience,salary,posting_date as `postingdate`,
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
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function createApplication($vacancy_ID, $candidate_ID)
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
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function DeleteApplication($id)
    {
        try{
            $sql = "DELETE FROM applications 
                    where application_ID= :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        }catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectApplicationsWithParam($id,$search)
    {
        try {
            $sql = "SELECT application_ID, V.name,D.name as department,description,experience_required as experience,salary,posting_date as `postingdate`,
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectApplicationsManagerWithParam($id,$search)
    {
        try {
            $sql = "SELECT CONCAT(last_name,' ',first_name) as candidate,  application_ID, V.name,D.name as department,description,experience_required as experience,salary,posting_date as `postingdate`,
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
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
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }
}