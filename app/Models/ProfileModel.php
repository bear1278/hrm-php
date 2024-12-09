<?php

namespace app\Models;

use app\Entities\Candidate;
use PDO;
use PDOException;

class ProfileModel
{
    protected $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function SelectCandidate($id)
    {
        try {
            $sql = "SELECT candidate_ID,position, first_name as `first name`, last_name as 'last name', email, phone_number as `phone number`, 
            resume, experience_years as `experience years`, location,image, C.status 
            FROM users as U
            INNER JOIN candidates as C 
            ON U.user_ID = C.candidate_ID
            WHERE user_ID= :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $candidate = null;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $candidate = new Candidate(
                    $row['candidate_ID'],
                    $row['email'],
                    $row['last name'],
                    $row['first name'],
                    $row['phone number'],
                    $row['resume'],
                    $row['experience years'],
                    $row['location'],
                    $row['status'],
                    $row['image'],
                    $row['position'],
                    null
                );
            }
            return $candidate;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectColumns()
    {
        try {
            $sql = "SELECT image,position,first_name as `first name`, last_name as 'last name', email, phone_number as `phone number`, 
            resume, experience_years as `experience years`, location 
            FROM users as U
            INNER JOIN candidates as C 
            ON U.user_ID = C.candidate_ID
            LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
            return array_keys($candidate);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function UpdateImage($id,$image)
    {
        try {
            $sql = "UPDATE candidates set image=:image where candidate_ID=:id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':image', $image);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function selectSkillForCandidate($id)
    {
        try{
            $sql="SELECT name FROM candidate_skills c
            inner join hrm.skills s on c.skill_ID = s.skill_ID
            where candidate_ID=:id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }
}