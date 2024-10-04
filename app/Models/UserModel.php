<?php
// app/Models/UserModel.php

require_once __DIR__ . '/../../config/database.php';



class UserModel {
    
    protected $pdo;

    // Constructor to initialize the database connection
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function findUserByUsername($email) {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e){
            throw new Exception("Ошибка при входе пользователя: " . $e->getMessage());
        }
    }

    public function createUser($firstname,$lastname,$email,$password,$role){
        try {
        
        $stmt = $this->pdo->prepare("INSERT INTO users (first_name, last_name, email, password,role_ID) VALUES (:firstname, :lastname, :email, :password, :role_ID)");

        // Bind parameters to the SQL query
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role_ID', $role);

        // Execute the query
        $stmt->execute();
        
        return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Ошибка при регистрации пользователя: " . $e->getMessage());
        }
    }
}
