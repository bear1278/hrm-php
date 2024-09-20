<?php
class ErrorHelper{
    private function redirectToErrorPage($message) {
        $encodedMessage = urlencode($message);
        header("Location: /error?message=" . $encodedMessage);
        exit();
    }
}