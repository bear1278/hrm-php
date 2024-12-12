<?php

namespace app\Models;

use PDO;
use PDOException;

class InterviewModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo=$pdo;
    }

    public function createInterview($app_id,$user_id,$link,$process_id,$date)
    {
        try{
            $stmt = $this->pdo->prepare("INSERT INTO interviews (application_ID,user_ID,interview_link,process_ID,date) VALUES (:application_ID,:user_ID,:interview_link,:process_ID,:date)");
            $stmt->bindParam(':application_ID', $app_id);
            $stmt->bindParam(':user_ID', $user_id);
            $stmt->bindParam(':interview_link', $link);
            $stmt->bindParam(':process_ID', $process_id);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            return $this->pdo->lastInsertId();
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function selectInterviewByAppIDProcessID($app_id,$process_id)
    {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM interviews WHERE application_ID=:application_ID AND process_ID = :process_id");
            $stmt->bindParam(':application_ID', $app_id);
            $stmt->bindParam(':process_id', $process_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function selectInterviewByUser($user_id)
    {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM interviews WHERE user_ID=:user_ID");
            $stmt->bindParam(':user_ID', $user_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function selectInterviewById($id)
    {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM interviews WHERE interview_ID=:id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function feedback($id,$skills,$result)
    {
        try{
            $this->pdo->beginTransaction();
            $isOk = $this->ChangeInterviewResult($id,$result);
            if (!$isOk){
                $this->pdo->rollBack();
                return $isOk;
            }
            foreach ($skills as $skill){
                $isOk = $this->CreateFeedback($id,$skill);
                if (!$isOk){
                    $this->pdo->rollBack();
                    return $isOk;
                }
            }
            $this->pdo->commit();
            return $isOk;
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function ChangeInterviewResult($id,$result)
    {
        try{
            $stmt = $this->pdo->prepare("UPDATE interviews  SET result=:result where interview_ID=:id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':result', $result);
            return $stmt->execute();
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function CreateFeedback($id, mixed $skill)
    {
        try{
            $stmt = $this->pdo->prepare("INSET INTO feedback values (:interview_ID, :skill, :mark, :imp)");
            $skill_id = $skill['id'];
            $mark = $skill['mark'];
            $importance = $skill['importance'];
            $stmt->bindParam(':interview_ID', $id);
            $stmt->bindParam(':skill', $skill_id);
            $stmt->bindParam(':mark', $mark);
            $stmt->bindParam(':imp', $importance);
            return $stmt->execute();
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }
}