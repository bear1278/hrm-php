<?php

namespace app\Entities;

use InvalidArgumentException;

class Candidate extends User
{
    const DEFAULT_IMAGE="/img/user.svg";
    const DIR_IMAGES="/../../public/img/candidates/";
    const DIR_IMG_FOR_VIEW = "/img/candidates/";
    const ADDITION_TO_PATH = "/../../public";

    const fieldMapping = [
        'candidate_ID' => 'getId',
        'last name' => 'getLastName',
        'first name' => 'getFirstName',
        'phone number' => 'getPhone',
        'resume' => 'getResume',
        'email' => 'getEmail',
        'experience' => 'getExperience',
        'salary' => 'getSalary',
        'experience years' => 'getExperience',
        'location' => 'getLocation',
        'status' => 'getStatus',
        'image' => 'getImage'
    ];

    private $phone;
    private $resume;
    private $experience;
    private $location;
    private $status;
    private $image;

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }


    public function __construct($id, $email, $last_name, $first_name, $phone, $resume, $experience, $location, $status,$image)
    {
        $this->setId($id);
        $this->setEmail($email);
        $this->setLastName($last_name);
        $this->setFirstName($first_name);
        $this->setPhone($phone);
        $this->setResume($resume);
        $this->setExperience($experience);
        $this->setLocation($location);
        $this->setStatus($status);
        $this->setImage($image);
    }

    /**
     * @param mixed $first_name
     */
    public function setFirstName($first_name)
    {
        if ($first_name != null) {
            if (empty($first_name)) {
                throw new InvalidArgumentException('First name cannot be empty');
            }
            if (!is_string($first_name)) {
                throw new InvalidArgumentException('First name must be a string');
            }
        }
        $this->first_name = $first_name;
    }

    public function setEmail($email)
    {
        if ($email != null) {
            if (empty($email)) {
                throw new InvalidArgumentException('Email cannot be empty');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email format');
            }
        }
        $this->email = $email;
    }

    public function setLastName($last_name)
    {
        if ($last_name != null) {
            if (empty($last_name)) {
                throw new InvalidArgumentException('Last name cannot be empty');
            }
            if (!is_string($last_name)) {
                throw new InvalidArgumentException('Last name must be a string');
            }
        }
        $this->last_name = $last_name;
    }

    public function setPhone($phone)
    {
        if ($this->validatePhone($phone)) {
            $this->phone = $phone;
        } else {
            throw new InvalidArgumentException("Неверный формат телефона.");
        }
    }

    public function setResume($resume)
    {
        if ($this->validateResume($resume)) {
            $this->resume = $resume;
        } else {
            throw new InvalidArgumentException("Резюме не может быть пустым.");
        }
    }

    public function setExperience($experience)
    {
        if ($this->validateExperience($experience)) {
            $this->experience = $experience;
        } else {
            throw new InvalidArgumentException("Неверный формат опыта.");
        }
    }

    public function setLocation($location)
    {
        if ($this->validateLocation($location)) {
            $this->location = $location;
        } else {
            throw new InvalidArgumentException("Неверный формат местоположения.");
        }
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return mixed
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * @return mixed
     */
    public function getExperience()
    {
        return $this->experience;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        if ($this->validateStatus($status)) {
            $this->status = $status;
        } else {
            throw new InvalidArgumentException("Неверный статус.");
        }
    }

    private function validatePhone($phone)
    {
        return preg_match('/^\+?\d{10,15}$/', $phone);
    }

    private function validateResume($resume)
    {
        return !empty($resume);
    }

    private function validateExperience($experience)
    {
        return is_numeric($experience) && $experience >= 0 && $experience <= 2147483647;
    }

    private function validateLocation($location)
    {
        return !empty($location);
    }

    private function validateStatus($status)
    {
        return !empty($status);
    }
}