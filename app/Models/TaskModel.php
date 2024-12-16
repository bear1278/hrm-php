<?php

namespace app\Models;

use PDO;
use PDOException;

class TaskModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo= $pdo;
    }

    public function selectTasksByUser(mixed $user_id)
    {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE user_ID=:user_ID");
            $stmt->bindParam(':user_ID', $user_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function SetInterviewerForTask($id, $interviewer_id,$process)
    {
        try{
            $stmt = $this->pdo->prepare("Insert INTO tasks (application_ID, user_ID, process_ID)  values (:application_ID, :user_ID, :process_ID)");
            $stmt->bindParam(':application_ID', $id);
            $stmt->bindParam(':user_ID', $interviewer_id);
            $stmt->bindParam(':process_ID', $process);
            return $stmt->execute();
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function selectTaskById($id)
    {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE task_ID=:id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function selectTaskByApplicationId(int $param)
    {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE application_ID=:id");
            $stmt->bindParam(':id', $param);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function SetClauseForTask($id, string $clause, mixed $deadline)
    {
        try{
            $stmt = $this->pdo->prepare("UPDATE tasks SET clause = :clause, deadline = :deadline WHERE task_ID=:id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':clause', $clause);
            $stmt->bindParam(':deadline', $deadline);
            return $stmt->execute();
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function SetResponseForTask($id, $response)
    {
        try{
            $stmt = $this->pdo->prepare("UPDATE tasks SET response = :response WHERE task_ID=:id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':response', $response);
            return $stmt->execute();
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function feedback($id,$skills,$result)
    {
        try{
            $this->pdo->beginTransaction();
            $isOk = $this->ChangeTaskResult($id,$result);
            if (!$isOk){
                $this->pdo->rollBack();
                return $isOk;
            }
            foreach ($skills as $skill){
                $isOk = $this->CreateFeedback($id,$skill);
                if (!$isOk){
                    $this->pdo->rollBack();
                    return $isOk;
                }
            }
            $this->pdo->commit();
            return $isOk;
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function ChangeTaskResult($id,$result)
    {
        try{
            $stmt = $this->pdo->prepare("UPDATE tasks SET result = :result where task_ID = :id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':result', $result, PDO::PARAM_STR);
            return $stmt->execute();
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function CreateFeedback($id, mixed $skill)
    {
        try{
            $stmt = $this->pdo->prepare("INSERT INTO feedback_task (task_ID,skill_ID,mark,importance) values (:task_ID, :skill, :mark, :imp)");
            $skill_id = $skill['id'];
            $mark = $skill['mark'];
            $importance =floatval( $skill['importance']);
            $stmt->bindParam(':task_ID', $id);
            $stmt->bindParam(':skill', $skill_id);
            $stmt->bindParam(':mark', $mark);
            $stmt->bindParam(':imp', $importance);
            return $stmt->execute();
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }

    public function selectFeedback($id)
    {
        try{
            $stmt = $this->pdo->prepare("SELECT s.skill_ID, s.name, mark, importance FROM feedback_task 
            INNER JOIN hrmc.skills s on feedback_task.skill_ID = s.skill_ID
            WHERE task_ID=:id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new PDOException("Ошибка базы данных: " . $e->getMessage());
        }
    }
}