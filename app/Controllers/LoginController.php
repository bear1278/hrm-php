<?php

namespace app\Controllers;

require_once __DIR__ . '/../Models/UserModel.php';

use app\Helpers\AuthHelper;
use app\Models\UserModel;
use Exception;
use PDOException;


class LoginController
{

    private $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function showLoginForm()
    {
        require_once __DIR__ . '/../Views/login_form.html';
    }

    public function login()
    {
        try {
            header('Content-Type: application/json');
            $email = (trim($_POST['email']));
            $password = (trim($_POST['password']));
            $user = $this->model->findUserByEmail($email);
            if (!$user) {
                throw new Exception('пользователя с данной почтой не существует');
            }
            if (password_verify($password, $user->getPassword())) {
                AuthHelper::login($user);
                echo json_encode(['success' => true]);
                exit();
            } else {
                throw new Exception('неверный пароль');
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }

    }

    public function logout()
    {
        AuthHelper::logout();
        header('Location: /login');
        exit();
    }
}
