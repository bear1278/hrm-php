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
            $sql = "SELECT first_name as `first name`, last_name as 'last name', email, phone_number as `phone number`, 
            resume, experience_years as `experience years`, location, C.status 
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
                    null,
                    $row['email'],
                    $row['last name'],
                    $row['first name'],
                    $row['phone number'],
                    $row['resume'],
                    $row['experience years'],
                    $row['location'],
                    $row['status']
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
            $sql = "SELECT first_name as `first name`, last_name as 'last name', email, phone_number as `phone number`, 
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
}