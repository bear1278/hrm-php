<?php

namespace app\Entities;

use InvalidArgumentException;

class User
{
    protected $id;
    protected $first_name;
    protected $last_name;
    private $password;
    protected $email;
    private $role;

    public function __construct($id, $role, $email, $password, $last_name, $first_name)
    {
        $this->setId($id);
        $this->setRole($role);
        $this->setEmail($email);
        $this->setPassword($password);
        $this->setLastName($last_name);
        $this->setFirstName($first_name);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $first_name
     */
    public function setFirstName($first_name)
    {
        if (empty($first_name)) {
            throw new InvalidArgumentException('First name cannot be empty');
        }
        if (!is_string($first_name)) {
            throw new InvalidArgumentException('First name must be a string');
        }
        $this->first_name = $first_name;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param mixed $last_name
     */
    public function setLastName($last_name)
    {
        if (empty($last_name)) {
            throw new InvalidArgumentException('Last name cannot be empty');
        }
        if (!is_string($last_name)) {
            throw new InvalidArgumentException('Last name must be a string');
        }
        $this->last_name = $last_name;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        if (empty($password)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }
        if (strlen($password) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters long');
        }
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        if (empty($email)) {
            throw new InvalidArgumentException('Email cannot be empty');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        if (!is_int($role) || $role <= 0 || $role > 4) {
            throw new InvalidArgumentException('Role must be a positive integer');
        }
        $this->role = $role;
    }
}
