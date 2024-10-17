<?php

namespace app\Controllers;

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';

use app\Entities\User;
use app\Helpers\AuthHelper;
use app\Models\UserModel;
use Exception;
use PDOException;

class SignUpController
{
    private $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function ShowRegistration()
    {
        require_once __DIR__ . '/../Views/signup_form.html';
    }

    public function signUp()
    {
        try {
            header('Content-Type: application/json');
            $user = new User(null, (int)trim($_POST['role']),
                trim($_POST['email']),
                trim($_POST['password']),
                trim($_POST['lastname']),
                trim($_POST['firstname']));
            $confirmPassword = trim($_POST['confirmPassword']);
            if (empty($confirmPassword)) {
                throw new Exception('пожалуйста, заполните все поля');
            }
            if ($user->getPassword() != $confirmPassword) {
                throw new Exception('пароли не совпадают');
            }
            $result = $this->model->findUserByEmail($user->getEmail());
            if ($result) {
                throw new Exception('существует аккаунт с данной почтой');
            }
            $user->setPassword(password_hash($user->getPassword(), PASSWORD_DEFAULT));
            $result = $this->model->createUser($user);
            if ($result) {
                $user->setId($result);
                AuthHelper::login($user);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Database error']);
            }
            exit();
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }
}