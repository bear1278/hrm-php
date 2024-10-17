<?php

namespace app\Models;

use app\Entities\User;
use Exception;
use PDO;
use PDOException;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Entities/User.php';

class UserModel
{
    protected $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * @throws Exception
     */
    public function findUserByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userData){
            return false;
        }
        return new User($userData['user_ID'],
            $userData['role_ID'],
            $userData['email'],
            $userData['password'],
            $userData['last_name'],
            $userData['first_name']);
    }

    public function createUser(User $user)
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (first_name, last_name, email, password,role_ID) VALUES (:firstname, :lastname, :email, :password, :role_ID)");
        $firstname = $user->getFirstName();
        $lastname = $user->getLastName();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $role = $user->getRole();
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role_ID', $role);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }
}
