<?php
// app/Helpers/AuthHelper.php



class AuthHelper {
    
    public static function login($userId, $firstname, $lastname, $email, $role_ID) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['firstname'] = $firstname;
        $_SESSION['lastname'] = $lastname;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role_ID;
    }

    public static function logout() {
        session_start();
        session_destroy();
    }

    public static function isLoggedIn() {
        session_start();
        return isset($_SESSION['user_id']);
    }

    public static function ensureLoggedIn() {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
    }
}
