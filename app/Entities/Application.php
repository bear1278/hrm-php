<?php

namespace app\Entities;

use InvalidArgumentException;

class Application extends Vacancy
{
    private $application_id;
    private $candidate_name;
    private $application_date;
    private $application_status;
    private $candidate_id;
    protected $current_process;

    /**
     * @return mixed
     */
    public function getCurrentProcess()
    {
        return $this->current_process;
    }

    /**
     * @param mixed $current_process
     */
    public function setCurrentProcess($current_process): void
    {
        $this->current_process = $current_process;
    }

    const fieldMapping = [
        'application_ID' => 'getApplicationId',
        'name' => 'getName',
        'department' => 'getDepartment',
        'description' => 'getDescription',
        'experience' => 'getExperience',
        'salary' => 'getSalary',
        'posting date' => 'getPostingDate',
        'application date' => 'getApplicationDate',
        'application status' => 'getApplicationStatus',
        'candidate' => 'getCandidateName'
    ];

    const TABS =[
        'не просмотрен'=>'Не просмотрены',
        'просмотрен'=>'Просмотрены',
        'отказ' => 'Отказы',
        'приглашение'=>'Приглашения',
        'вас приняли'=>'Приняты'
    ];

    /**
     * @return mixed
     */
    public function getCandidateId()
    {
        return $this->candidate_id;
    }

    /**
     * @param mixed $candidate_id
     */
    public function setCandidateId($candidate_id): void
    {
        $this->candidate_id = $candidate_id;
    }


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
                                $name, $department, $description, $experience, $salary, $posting_date,$current_process)
    {
        $this->application_id = $application_id;
        $this->candidate_name = $candidate_name;
        $this->setApplicationDate($application_date);
        $this->setApplicationStatus($application_status);
        $this->setName($name);
        $this->setDepartment($department);
        $this->setDescription($description);
        $this->setExperience($experience);
        $this->setSalary($salary);
        $this->setPostingDate($posting_date);
        $this->setCurrentProcess($current_process);
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
        if ($application_date != null) {
            $timestamp = strtotime($application_date);
            $date = date("Y-m-d H:i", $timestamp);
            $this->application_date = $date;
        } else {
            $this->application_date = $application_date;
        }
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