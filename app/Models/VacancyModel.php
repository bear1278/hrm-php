<?php

namespace app\Models;
require_once __DIR__ . '/../../config/database.php';

use app\Entities\Vacancy;
use Exception;
use PDO;
use PDOException;


class VacancyModel
{

    protected $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function SelectAllDepartments()
    {
        try {
            $sql = "SELECT * FROM departments";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectAllSkills()
    {
        try {
            $sql = "SELECT * FROM skills";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function getVacancies($id): array
    {
        try {
            $sql = "SELECT vacancy_ID, V.name, D.name as department, description, experience_required as experience, salary, posting_date as `posting date`
                FROM vacancies as V 
                INNER JOIN departments as D 
                ON V.department_ID=D.department_ID
                WHERE author = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $vacancies = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vacancies[] = new Vacancy(
                    $row['vacancy_ID'],
                    $row['name'],
                    $row['department'],
                    $row['description'],
                    $row['experience'],
                    $row['salary'],
                    $row['posting date'],
                    null,
                    [],
                    null
                );
            }
            return $vacancies;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function getVacanciesForManagerWithParams($id, $data): array
    {
        try {
            $sql = "SELECT vacancy_ID, V.name, D.name as department, description, experience_required as experience, salary, posting_date as `posting date`
            FROM vacancies as V 
            INNER JOIN departments as D 
            ON V.department_ID=D.department_ID
            WHERE author = :id AND ";
            $conditions = $this->handleCondition($data);
            $params = $this->handleParams($data);
            $sql .= implode(' AND ', $conditions);
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $vacancies = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vacancies[] = new Vacancy(
                    $row['vacancy_ID'],
                    $row['name'],
                    $row['department'],
                    $row['description'],
                    $row['experience'],
                    $row['salary'],
                    $row['posting date'],
                    null,
                    [],
                    null
                );
            }
            return $vacancies;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectVacanciesForCandidate($id): array
    {
        try {
            $sql = "SELECT DISTINCT VA.vacancy_ID, 
                VA.name, 
                D.name AS department, 
                VA.description, 
                VA.experience_required AS experience, 
                VA.salary, 
                VA.posting_date AS `posting date`,
                F.relevance_score
            FROM vacancies AS VA
            INNER JOIN (
                SELECT 
                    V.vacancy_ID, 
                    SUM(
                        CASE WHEN V.department_ID = R.department_ID THEN (SELECT value FROM relevance_weights Where parameter_name='department_match') ELSE 0 END +
                        CASE
                            WHEN ABS(V.experience_required - R.experience_required) <= 1 THEN (SELECT value FROM relevance_weights Where parameter_name='experience_close')
                            WHEN ABS(V.experience_required - R.experience_required) <= 3 THEN (SELECT value FROM relevance_weights Where parameter_name='experience_medium')
                            ELSE 0
                        END +
                        CASE
                            WHEN V.salary BETWEEN R.salary * 0.9 AND R.salary * 1.1 THEN (SELECT value FROM relevance_weights Where parameter_name='salary_close')
                            WHEN V.salary BETWEEN R.salary * 0.8 AND R.salary * 1.2 THEN (SELECT value FROM relevance_weights Where parameter_name='salary_medium')
                            ELSE 0
                        END +
                        CASE
                            WHEN uh.action = 'unapply' THEN (SELECT value FROM relevance_weights Where parameter_name='unapply_penalty') * (Select count(*) from user_history) ELSE 0
                        END
                    ) AS relevance_score
                FROM vacancies AS V
                LEFT JOIN (
                    SELECT department_ID, experience_required, salary 
                    FROM vacancies 
                    WHERE vacancy_ID IN (
                        SELECT vacancy_ID 
                        FROM user_history 
                        WHERE user_ID = :id 
                        AND action = 'apply'
                    )
                ) R ON 1 = 1 
                LEFT JOIN user_history AS uh ON V.vacancy_ID = uh.vacancy_ID AND uh.user_ID = :id
                GROUP BY V.vacancy_ID
            ) AS F ON F.vacancy_ID = VA.vacancy_ID
            INNER JOIN departments AS D ON VA.department_ID = D.department_ID
            WHERE VA.vacancy_ID NOT IN (SELECT vacancy_ID FROM applications WHERE candidate_ID = :id)
            ORDER BY F.relevance_score DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $vacancies = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vacancies[] = new Vacancy(
                    $row['vacancy_ID'],
                    $row['name'],
                    $row['department'],
                    $row['description'],
                    $row['experience'],
                    $row['salary'],
                    $row['posting date'],
                    null,
                    [],
                    null
                );
            }
            return $vacancies;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }


    public function getTableColumns(): array
    {
        try {
            $sql = "SELECT V.name, D.name as department, experience_required as experience, salary, posting_date as `posting date`
            FROM vacancies as V 
            Left JOIN departments as D 
            ON V.department_ID=D.department_ID
            LIMIT 1";
            $stmt = $this->pdo->query($sql);
            $columns = $stmt->fetch(PDO::FETCH_ASSOC);
            return array_keys($columns);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function getColumnsType(): array
    {
        try {
            $query = "SELECT V.name, D.name as department, description, experience_required as experience, salary, posting_date as `posting date` 
                  FROM vacancies as V 
                  INNER JOIN departments as D 
                  ON V.department_ID=D.department_ID
                  LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $columnCount = $stmt->columnCount();
            $columnTypes = [];
            for ($i = 0; $i < $columnCount; $i++) {
                $meta = $stmt->getColumnMeta($i);
                $columnTypes[$meta['name']] = $meta['native_type']; // Извлекаем название колонки и тип данных
            }
            return $columnTypes;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function getVacanciesWithParamForCandidate($data, $id): array
    {
        try {
            $sql = "SELECT vacancy_ID, V.name, D.name as department, description, experience_required as experience, salary, posting_date as `posting date`
                FROM vacancies as V 
                INNER JOIN departments as D 
                ON V.department_ID=D.department_ID
                WHERE vacancy_ID not in (Select vacancy_ID from applications where candidate_ID= :id) AND ";
            $conditions = $this->handleCondition($data);
            $params = $this->handleParams($data);
            $sql .= implode(' AND ', $conditions);
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $vacancies = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vacancies[] = new Vacancy(
                    $row['vacancy_ID'],
                    $row['name'],
                    $row['department'],
                    $row['description'],
                    $row['experience'],
                    $row['salary'],
                    $row['posting date'],
                    null,
                    [],
                    null
                );
            }
            return $vacancies;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function handleCondition($data): array
    {
        $conditions = [];
        foreach ($data as $index => $filter) {
            if ($filter['column'] === 'experience') {
                $filter['column'] = 'experience_required';
            }
            if ($filter['column'] == "posting date") {
                $filter['column'] = "posting_date";
            }
            if (strpos($filter['column'], 'max') === 0) {
                $columnName = substr($filter['column'], 3);
                if ($columnName == "experience") {
                    $columnName = "experience_required";
                }
                if ($columnName == "posting date") {
                    $columnName = "posting_date";
                }
                $conditions[] = $columnName . " <= :search_" . $index;
            } elseif (strpos($filter['column'], 'min') === 0) {
                $columnName = substr($filter['column'], 3);
                if ($columnName == "experience") {
                    $columnName = "experience_required";
                }
                if ($columnName == "posting date") {
                    $columnName = "posting_date";
                }
                $conditions[] = $columnName . " >= :search_" . $index;
            } else {
                if ($filter['column'] == "department") {
                    $filter['column'] = "D.name";
                }
                if ($filter['column'] == "name") {
                    $filter['column'] = "V.name";
                }
                $conditions[] = $filter['column'] . " LIKE :search_" . $index;
            }
        }
        return $conditions;
    }

    public function handleParams($data): array
    {
        $params = [];
        foreach ($data as $index => $filter) {
            if ($filter['column'] == "department" || $filter['column'] == "name") {
                $params[':search_' . $index] = '%' . $filter['value'] . '%';
            } else {
                $params[':search_' . $index] = $filter['value'];
            }
        }
        return $params;
    }

    public function updateVacancy($id, $name, $department_ID, $description, $experience_required, $salary)
    {
        try {
            $sql = "UPDATE vacancies 
            SET name= :Name,
                department_ID = :department,
                description= :newdescription,
                experience_required=:newexperience_required,
                salary= :newsalary
                where vacancy_ID= :Id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':Id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':Name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':department', $department_ID, PDO::PARAM_INT);
            $stmt->bindParam(':newdescription', $description, PDO::PARAM_STR);
            $stmt->bindParam(':newexperience_required', $experience_required, PDO::PARAM_INT);
            $stmt->bindParam(':newsalary', $salary, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function DeleteVacancySkill($id)
    {
        try {
            $sql = "DELETE FROM vacancy_skills 
                where vacancy_ID= :Id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':Id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function getVacancyByID($id)
    {
        try {
            $sql = "SELECT * FROM vacancies WHERE vacancy_ID=?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $vacancy = null;
            while ($row = $stmt->fetch()) {
                $vacancy = new Vacancy(
                    $row['vacancy_ID'],
                    $row['name'],
                    $row['department_ID'],
                    $row['description'],
                    $row['experience_required'],
                    $row['salary'],
                    $row['posting_date'],
                    null,
                    [],
                    null
                );
            }
            return $vacancy;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function deleteVacancyFromDB($vacancyID)
    {
        try {
            $sql = "DELETE FROM vacancies WHERE vacancy_ID = :vacancy_ID";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':vacancy_ID', $vacancyID, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function Insert($name, $department_ID, $description, $experience_required, $salary, $posting_date, $author)
    {
        try {
            $sql = "INSERT INTO vacancies (name, department_ID, description,experience_required,salary,posting_date,author) 
        VALUES (:name, :department_ID, :description,:experience_required,:salary,:posting_date, :author)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':department_ID', $department_ID);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':experience_required', $experience_required);
            $stmt->bindParam(':salary', $salary);
            $stmt->bindParam(':posting_date', $posting_date);
            $stmt->bindParam(':author', $author);
            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function InsertVacancySkills($id, $skills)
    {
        try {
            $sql = "INSERT INTO vacancy_skills (vacancy_skills.vacancy_ID, vacancy_skills.skill_ID) VALUES (:vacancy_ID,:skill_ID)";
            $stmt = $this->pdo->prepare($sql);
            foreach ($skills as $skill) {
                $stmt->execute(['vacancy_ID' => $id, 'skill_ID' => $skill]);
            }
            return true;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function InsertNewVacancy(Vacancy $vacancy)
    {
        try {
            $this->pdo->beginTransaction();
            $vacancy_ID = $this->Insert($vacancy->getName(),
                $vacancy->getDepartment(),
                $vacancy->getDescription(),
                $vacancy->getExperience(),
                $vacancy->getSalary(),
                $vacancy->getPostingDate()->format('Y-m-d'),
                $vacancy->getAuthor());
            $processes =$vacancy->getProcesses();
            foreach ($processes as $process) {
                $id = $this->InsertProcess($process);
                $this->InsertVacancyProcess($vacancy_ID,$id);
            }
            $result = $this->InsertVacancySkills($vacancy_ID, $vacancy->getSkills());
            $this->pdo->commit();
            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function UpdateVacancyAndSkills(Vacancy $vacancy)
    {
        try {
            $this->pdo->beginTransaction();
            $result = $this->updateVacancy($vacancy->getId(),
                $vacancy->getName(),
                $vacancy->getDepartment(),
                $vacancy->getDescription(),
                $vacancy->getExperience(),
                $vacancy->getSalary());
            if (!$result) {
                throw new PDOException("Ошибка транзакции: ");
            }
            $result = $this->DeleteVacancySkill($vacancy->getId());
            if (!$result) {
                throw new PDOException("Ошибка транзакции: ");
            }
            $result = $this->InsertVacancySkills($vacancy->getId(), $vacancy->getSkills());
            if (!$result) {
                throw new Exception("Ошибка транзакции: ");
            }
            $this->pdo->commit();
            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function InsertProcess($process)
    {
        try {
            $sql = "INSERT INTO processes ( description, type, orderable) VALUES (:description,:type,:orderable)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':description', $process['description']);
            $stmt->bindParam(':type', $process['type']);
            $stmt->bindParam(':orderable', $process['orderable']);
            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function InsertVacancyProcess($id, $process_id)
    {
        try {
            $sql = "INSERT INTO processes_vacancy (vacancy_ID, process_ID) VALUES (:id,:process_id)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id, 'process_id' => $process_id]);
            return true;
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectAllUsers($id)
    {
        try {
            // Запрос для выборки данных пользователя
            $sql = "SELECT user_ID,first_name AS `first name`, last_name AS `last name`, email, R.name as role 
                FROM users 
                INNER JOIN roles AS R ON users.role_ID = R.role_ID
                WHERE user_ID != :id";

            // Подготовка запроса
            $stmt = $this->pdo->prepare($sql);

            // Привязка параметра
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Выполнение запроса
            $stmt->execute();

            // Возврат результата выборки
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Если не было начала транзакции, rollBack не нужен
            throw new Exception("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectRoles()
    {
        try {
            $sql = "SELECT * FROM roles
                WHERE role_ID != 1";
            $stmt = $this->pdo->query($sql);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }




    public function getVacancySkills($id)
    {
        try{
            $sql = "SELECT skill_ID FROM vacancy_skills WHERE vacancy_ID=:id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function selectSkillForVacancy($id)
    {
        try{
            $sql="SELECT name FROM vacancy_skills vs
            inner join skills s on vs.skill_ID = s.skill_ID
            where vacancy_ID=:id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function selectProcessesForVacancy(int $id)
    {
        try{
            $sql="SELECT p.process_ID, type, description,orderable FROM processes_vacancy pv
            inner join processes p on p.process_ID = pv.process_ID
            where vacancy_ID=:id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function SelectApplicationByVacancy(int $id, $user_id)
    {
        try{
            $sql="SELECT application_ID FROM applications
            where vacancy_ID=:id AND candidate_ID=:user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }

    public function getDepartmentById($id)
    {
        try{
            $sql="SELECT name FROM departments
            where department_id=:id ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }
}
