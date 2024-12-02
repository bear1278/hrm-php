<?php

namespace app\Entities;

use DateTime;
use InvalidArgumentException;

class Vacancy
{

    const fieldMapping = [
        'vacancy_ID' => 'getId',
        'name' => 'getName',
        'department' => 'getDepartment',
        'description' => 'getDescription',
        'experience' => 'getExperience',
        'salary' => 'getSalary',
        'posting date' => 'getPostingDate',
        'status' => 'getStatus',
        'image' => 'getImage'
    ];
    const maxInt = 2147483647;
    private $id;
    protected $name;
    protected $department;
    protected $description;
    protected $experience;
    protected $salary;
    protected $posting_date;
    protected $status;
    private $author;
    private $skills;
    private $image;
    private ?array $processes;


    public function getProcesses():?array
    {
        return $this->processes;
    }


    public function setProcesses(?array $processes): void
    {
        $this->processes = $processes;
    }

    public function __construct($id, $name, $department, $description, $experience, $salary, $posting_date, $status, $author, $skills,$image,$processes)
    {
        $this->setId($id);
        $this->setName($name);
        $this->setDepartment($department);
        $this->setDescription($description);
        $this->setExperience($experience);
        $this->setSalary($salary);
        $this->setPostingDate($posting_date);
        $this->setStatus($status);
        $this->setAuthor($author);
        $this->setSkills($skills);
        $this->setImage($image);
        $this->setProcesses($processes);
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function copy(Vacancy $vacancy)
    {
        if (empty($this->name)) {
            $this->name = $vacancy->getName();
        }
        if (empty($this->department)) {
            $this->department = $vacancy->getDepartment();
        }
        if (empty($this->description)) {
            $this->description = $vacancy->getDescription();
        }
        if (empty($this->experience)) {
            $this->experience = $vacancy->getExperience();
        }
        if (empty($this->salary)) {
            $this->salary = $vacancy->getSalary();
        }
        if (empty($this->posting_date)) {
            $this->posting_date = $vacancy->getPostingDate();
        }
        if (empty($this->status)) {
            $this->status = $vacancy->getStatus();
        }
        if ($this->image==null && $vacancy->getImage()!=null){
            $this->setImage($vacancy->getImage());
        }
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setName($name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Название вакансии не может быть пустым.');
        }
        $this->name = $name;
    }

    public function setDepartment($department)
    {
        if (empty($department)) {
            throw new InvalidArgumentException('Отдел не может быть пустым.');
        }
        $this->department = $department;
    }

    public function setDescription($description)
    {
        if (empty($description)) {
            throw new InvalidArgumentException('Описание вакансии не может быть пустым.');
        }
        $this->description = $description;
    }

    public function setExperience($experience)
    {
        if (!is_int($experience) || $experience < 0 || $experience > self::maxInt) {
            throw new InvalidArgumentException('Невалидное значение опыта.');
        }
        $this->experience = $experience;
    }

    public function setSalary($salary)
    {
        if (!is_numeric($salary) || $salary < 0 || $salary > self::maxInt) {
            throw new InvalidArgumentException('Невалидное значение зарплаты.');
        }
        $this->salary = $salary;
    }

    public function setPostingDate($posting_date)
    {
        if ($posting_date != null) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $posting_date);
            if (!$date) {
                throw new InvalidArgumentException('Дата размещения должна быть в формате YYYY-MM-DD или YYYY-MM-DD HH:MM:SS.');
            }
            if ($date->format('Y-m-d H:i:s') !== $posting_date && $date->format('Y-m-d') !== substr($posting_date, 0, 10)) {
                throw new InvalidArgumentException('Дата размещения должна быть в формате YYYY-MM-DD или YYYY-MM-DD HH:MM:SS.');
            }
            $this->posting_date = $date;
        } else {
            $this->posting_date = $posting_date;
        }

    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function setSkills(array $skills)
    {
        if ($skills != null) {
            if (empty($skills)) {
                throw new InvalidArgumentException('Список навыков не может быть пустым.');
            }
        }
        $this->skills = $skills;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDepartment()
    {
        return $this->department;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getExperience()
    {
        return $this->experience;
    }

    public function getSalary()
    {
        return $this->salary;
    }

    public function getPostingDate()
    {
        return $this->posting_date;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getSkills()
    {
        return $this->skills;
    }
}

