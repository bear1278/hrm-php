<?php

namespace app\Helpers;

use app\Entities\User;

class AuthHelper
{

    public static function login(User $user)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['firstname'] = $user->getFirstName();
        $_SESSION['lastname'] = $user->getLastName();
        $_SESSION['email'] = $user->getEmail();
        $_SESSION['role'] = $user->getRole();
    }

    public static function logout()
    {
        setcookie('filtersData', '', time() - 3600, '/');   
        session_destroy();
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public static function ensureLoggedIn()
    {
        if (!self::isLoggedIn()) {
            session_destroy();
            header('Location: /login');
            exit();
        }
    }

    public static function isCandidate()
    {
        if ($_SESSION['role'] == 4) {
            return true;
        }
        return false;
    }

    public static function isManager()
    {
        if ($_SESSION['role'] == 2) {
            return true;
        }
        return false;
    }

    public static function isAdmin()
    {
        if ($_SESSION['role'] == 1) {
            return true;
        }
        return false;
    }
}
