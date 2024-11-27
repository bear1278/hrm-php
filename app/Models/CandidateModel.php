<?php

namespace app\Models;

use app\Entities\Candidate;
use Exception;
use PDO;
use PDOException;

class CandidateModel
{

    protected $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function findCandidateById($user_ID)
    {
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
                    $row['status'],
                    $row['image'],
                    $row['position'],
                    null
                );
            }
            return $candidate;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function createCandidate($user_ID, $phone_number, $resume, $exp, $location, $status,$position,$skills)
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("INSERT INTO candidates (candidate_ID, phone_number, resume,experience_years, location,status,position) VALUES (:user_ID, :phone_number, :resume, :experience_year, :location,:status, :position)");
            $stmt->bindParam(':user_ID', $user_ID);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':resume', $resume);
            $stmt->bindParam(':experience_year', $exp);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':position', $position);
            $result = $stmt->execute();
            $result = $stmt->execute();
            if(!$result){
                $this->pdo->rollBack();
            }
            foreach($skills as $skill){
                $result= $this->insertCandidateSkills($user_ID,$skill);
                if(!$result){
                    $this->pdo->rollBack();
                }
            }
            $this->pdo->commit();
            return $result;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function updateCandidate($id, $number, $resume, $exp, $location, $status,$position,$skills)
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("UPDATE candidates 
            SET phone_number=:phone_number, resume=:resume,experience_years=:experience_year, location=:location, status=:status, position=:position 
            WHERE candidate_ID=:user_ID");
            $stmt->bindParam(':user_ID', $id);
            $stmt->bindParam(':phone_number', $number);
            $stmt->bindParam(':resume', $resume);
            $stmt->bindParam(':experience_year', $exp);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':position', $position);
            $result = $stmt->execute();
            if(!$result){
                $this->pdo->rollBack();
            }
            foreach($skills as $skill){
                $result= $this->insertCandidateSkills($id,$skill);
                if(!$result){
                    $this->pdo->rollBack();
                }
            }
            $this->pdo->commit();
            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Ошибка при регистрации пользователя: " . $e->getMessage());
        }
    }

    public function insertCandidateSkills($id, $skill)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT candidate_skills (candidate_ID,skill_ID) values (:id, :skill)");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':skill', $skill);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Ошибка при регистрации пользователя: " . $e->getMessage());
        }
    }
}
