<?php

namespace app\Models;

use Exception;
use PDO;
use PDOException;

class   AdminModel
{

    protected $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function UpdateUserRole($user, $role)
    {
        try {
            $sql = "UPDATE users 
                SET role_ID=:role 
                WHERE user_ID=:user";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':role', $role, PDO::PARAM_INT);
            $stmt->bindParam(':user', $user, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return true;
            } else {
                throw new Exception("Ошибка: Не удалось обновить роль пользователя.");
            }
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function Select($table)
    {
        try {
            $sql = "Select * FROM " . $table;
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectHistory()
    {
        try {
            $sql = "Select id, user_id, v.name as 'vacancy name' , action, creating_date 
                    FROM user_history as uh
                    INNER JOIN vacancies as V 
                    ON uh.vacancy_ID = V.vacancy_ID";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function Insert($name, $table)
    {
        try {
            $sql = "INSERT INTO " . $table . " (name) value (:name)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':name', $name);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function Delete($id, $table, $key)
    {
        try {
            $sql = "DELETE FROM  " . $table . " WHERE " . $key . " = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectRelevanceValues()
    {
        try {
            $sql = "Select  id,parameter_name, value From relevance_weights";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function UpdateRelevanceValues($id, $value)
    {
        try {
            $sql = "UPDATE relevance_weights 
             SET value = :value
             WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }
}