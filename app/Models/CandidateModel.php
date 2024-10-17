<?php

namespace app\Models;

use app\Entities\Candidate;
use Exception;
use PDO;
use PDOException;

class CandidateModel {
    
    protected $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function findCandidateById($user_ID) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM candidates WHERE candidate_ID = ?");
            $stmt->execute([$user_ID]);
            $candidate = null;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $candidate = new Candidate(
                    $row['candidate_ID'],
                    null,
                    null,
                    null,
                    $row['phone_number'],
                    $row['resume'],
                    $row['experience_years'],
                    $row['location'],
                    $row['status']
                );
            }
            return $candidate;
        }catch (PDOException $e){
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function createCandidate($user_ID,$phone_number,$resume,$exp,$location,$status){
        try {
        $stmt = $this->pdo->prepare("INSERT INTO candidates (candidate_ID, phone_number, resume,experience_years, location,status) VALUES (:user_ID, :phone_number, :resume, :experience_year, :location,:status)");
        $stmt->bindParam(':user_ID', $user_ID);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':resume', $resume);
        $stmt->bindParam(':experience_year', $exp);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function updateCandidate($id, $number, $resume, $exp, $location,$status)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE candidates 
            SET phone_number=:phone_number, resume=:resume,experience_years=:experience_year, location=:location, status=:status
            WHERE candidate_ID=:user_ID");
            $stmt->bindParam(':user_ID', $id);
            $stmt->bindParam(':phone_number', $number);
            $stmt->bindParam(':resume', $resume);
            $stmt->bindParam(':experience_year', $exp);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':status', $status);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Ошибка при регистрации пользователя: " . $e->getMessage());
        }
    }
}
