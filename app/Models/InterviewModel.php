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
}