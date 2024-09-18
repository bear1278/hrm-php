<?php
// app/Models/UserModel.php

require_once __DIR__ . '/../../config/database.php';



class CandidateModel {
    
    protected $pdo;

    // Constructor to initialize the database connection
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Find a user by their username
    public function findCandidateById($user_ID) {
        $stmt = $this->pdo->prepare("SELECT * FROM candidates WHERE candidate_ID = ?");
        $stmt->execute([$user_ID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCandidate($user_ID,$phone_number,$resume,$exp,$location){
        try {
        // Prepare an SQL statement to insert the user data into the "users" table
        $stmt = $this->pdo->prepare("INSERT INTO candidates (candidate_ID, phone_number, resume,experience_years, location) VALUES (:user_ID, :phone_number, :resume, :experience_year, :location)");
        $role=4;
        // Bind parameters to the SQL query
        $stmt->bindParam(':user_ID', $user_ID);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':resume', $resume);
        $stmt->bindParam(':experience_year', $exp);
        $stmt->bindParam(':location', $location);

        // Execute the query
        $stmt->execute();
        
        return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            // Handle any errors
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
