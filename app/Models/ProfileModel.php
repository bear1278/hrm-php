<?php
require_once __DIR__ . '/../../config/database.php';



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
        try{
            $sql = "SELECT first_name as `first name`, last_name as 'last name', email, phone_number as `phone number`, 
            resume, experience_years as `experience years`, location 
            FROM users as U
            INNER JOIN candidates as C 
            ON U.user_ID = C.candidate_ID
            WHERE user_ID= :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }
}