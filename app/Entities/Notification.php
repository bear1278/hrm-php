<?php

namespace app\Entities;

class Notification
{
    private $id;
    private $userId;
    private $applicationId;
    private $message;
    private $isShown;
    private $vacancyName;
    private $vacancyDescription;

    public function __construct($id, $userId, $applicationId, $message,
                                $isShown, $vacancyName, $vacancyDescription)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->applicationId = $applicationId;
        $this->message = $message;
        $this->isShown = $isShown;
        $this->vacancyName = $vacancyName;
        $this->vacancyDescription = $vacancyDescription;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getApplicationId()
    {
        return $this->applicationId;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getIsShown()
    {
        return $this->isShown;
    }

    public function getVacancyName()
    {
        return $this->vacancyName;
    }

    public function getVacancyDescription()
    {
        return $this->vacancyDescription;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'application_id' => $this->applicationId,
            'message' => $this->message,
            'is_shown' => $this->isShown,
            'vacancy_name' => $this->vacancyName,
            'vacancy_description' => $this->vacancyDescription
        ];
    }
}
