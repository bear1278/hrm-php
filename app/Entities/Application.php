<?php

namespace app\Entities;

class Application extends Vacancy
{
    private $application_id;
    private $candidate_name;
    private $application_date;
    private $application_status;
    const fieldMapping = [
        'application_ID' => 'getApplicationId',
        'name' => 'getName',
        'department' => 'getDepartment',
        'description' => 'getDescription',
        'experience' => 'getExperience',
        'salary' => 'getSalary',
        'posting date' => 'getPostingDate',
        'vacancy status' => 'getStatus',
        'application date'=> 'getApplicationDate',
        'application status' => 'getApplicationStatus',
        'candidate' => 'getCandidateName'
    ];

    /**
     * @param $application_id
     * @param $candidate_name
     * @param $application_date
     * @param $application_status
     */
    public function __construct($application_id,
                                $candidate_name,
                                $application_date,
                                $application_status,
                                $name, $department, $description, $experience, $salary, $posting_date, $status)
    {
        $this->application_id = $application_id;
        $this->candidate_name = $candidate_name;
        $this->application_date = $application_date;
        $this->application_status = $application_status;
        $this->setName($name);
        $this->setDepartment($department);
        $this->setDescription($description);
        $this->setExperience($experience);
        $this->setSalary($salary);
        $this->setPostingDate($posting_date);
        $this->setStatus($status);
    }

    /**
     * @return mixed
     */
    public function getCandidateName()
    {
        return $this->candidate_name;
    }

    /**
     * @param mixed $candidate_name
     */
    public function setCandidateName($candidate_name)
    {
        $this->candidate_name = $candidate_name;
    }

    /**
     * @return mixed
     */
    public function getApplicationId()
    {
        return $this->application_id;
    }

    /**
     * @param mixed $application_id
     */
    public function setApplicationId($application_id)
    {
        $this->application_id = $application_id;
    }

    /**
     * @return mixed
     */
    public function getApplicationDate()
    {
        return $this->application_date;
    }

    /**
     * @param mixed $application_date
     */
    public function setApplicationDate($application_date)
    {
        $this->application_date = $application_date;
    }

    /**
     * @return mixed
     */
    public function getApplicationStatus()
    {
        return $this->application_status;
    }

    /**
     * @param mixed $application_status
     */
    public function setApplicationStatus($application_status)
    {
        $this->application_status = $application_status;
    }


}