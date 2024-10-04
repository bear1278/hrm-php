<?php
// app/Models/UserModel.php

require_once __DIR__ . '/../../config/database.php';



class VacancyModel {
    
    protected $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getVacancies() {
        try{
        $sql = "SELECT * FROM vacancies";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }


    public function getTableColumns() {
        try{
        $sql = "SHOW COLUMNS FROM vacancies";
        $stmt = $this->pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = [];
        foreach ($columns as $column) {
            $columnNames[] = $column['Field'];
        }
        return $columnNames;
        }catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function getVacanciesWithParam($data) {
        try{
        $sql = "SELECT * FROM vacancies WHERE ";
        $columns = $this->getTableColumns();
        $conditions = [];
        foreach ($columns as $column) {
            $conditions[] = "$column LIKE :search";
        }
        $sql .= implode(' OR ', $conditions);
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':search', '%' . $data . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function updateVacancy($id,$name, $department_ID, $description,$experience_required,$salary,$posting_date,$status)
    {
        try{
            $sql = "CALL UpdateVacancy(:Id, :Name ,:department, :newdescription, :newexperience_required, :newsalary, :newposting_date, :newstatus)";

           
            $stmt = $this->pdo->prepare($sql);
            $postingDate = new DateTime($posting_date);

            // Привязываем параметры
            $stmt->bindParam(':Id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':Name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':department', $department_ID, PDO::PARAM_INT);
            $stmt->bindParam(':newdescription', $description, PDO::PARAM_STR);
            $stmt->bindParam(':newexperience_required', $experience_required, PDO::PARAM_INT);
            $stmt->bindParam(':newsalary', $salary, PDO::PARAM_INT);
            $stmt->bindParam(':newposting_date', $posting_date);
            $stmt->bindParam(':newstatus', $status, PDO::PARAM_INT);

            // Выполнение запроса
            return $stmt->execute();
        }
        catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function getVacancyByID($id) {
        try{
        $sql = "SELECT * FROM vacancies WHERE vacancy_ID=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function deleteVacancyFromDB($vacancyID){
        try{
        $sql = "DELETE FROM vacancies WHERE vacancy_ID = :vacancy_ID";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':vacancy_ID', $vacancyID, PDO::PARAM_INT);
        return $stmt->execute();
        }catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

     public function getRowCount($tableName) {
        try{
        $sql = "SELECT COUNT(*) FROM $tableName";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchColumn();
        }catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function Insert($name, $department_ID, $description,$experience_required,$salary,$posting_date,$status){
        try{
        $sql = "INSERT INTO vacancies (name, department_ID, description,experience_required,salary,posting_date,status) VALUES (:name, :department_ID, :description,:experience_required,:salary,:posting_date,:status)";
    
    // Подготавливаем запрос
    $stmt = $this->pdo->prepare($sql);
    
    // Привязываем параметры
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':department_ID', $department_ID);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':experience_required', $experience_required);
    $stmt->bindParam(':salary', $salary);
    $stmt->bindParam(':posting_date', $posting_date);
    $stmt->bindParam(':status', $status);


     return $stmt->execute();
        }catch (PDOException $e) {
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

}
