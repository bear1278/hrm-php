<?php

namespace app\Controllers;

use app\Helpers\AuthHelper;
use app\Models\InterviewModel;
use Exception;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use PDOException;

require_once __DIR__.'/../../vendor/autoload.php';

class InterviewController
{
    private $client;
    private $service;
    private $credentialsFile = __DIR__.'/../../client_secret.json';
    private $model;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName('Google Meet API Example');
        $this->client->setScopes(Google_Service_Calendar::CALENDAR);
        $this->client->setAuthConfig($this->credentialsFile);
        $this->client->setAccessType('offline');
        $this->service = new Google_Service_Calendar($this->client);
        $this->model= new InterviewModel();
    }

    public function authenticate()
    {
        AuthHelper::ensureLoggedIn();
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $this->client->setAccessToken($_SESSION['access_token']);
        }
        if ($this->client->isAccessTokenExpired()) {
            // Если токен истек, проверяем наличие refresh токена
            if ($this->client->getRefreshToken()) {
                // Попытаться обновить токен с помощью refresh токена
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                $_SESSION['access_token'] = $this->client->getAccessToken();
            } else {
                // Если нет refresh токена, перенаправляем на страницу авторизации
                $authUrl = $this->client->createAuthUrl();
                header('Location: ' . $authUrl);
                exit();
            }
        }

        $_SESSION['access_token'] = $this->client->getAccessToken();
    }

    /**
     * Создание события с видеоконференцией Google Meet.
     *
     * @param string $summary Название события
     * @param string $startTime Время начала события (формат: YYYY-MM-DDTHH:MM:SS)
     * @param string $endTime Время окончания события (формат: YYYY-MM-DDTHH:MM:SS)
     * @return string Ссылка на видеоконференцию
     */
    public function createEvent($summary, $startTime, $endTime)
    {
        $event = new Google_Service_Calendar_Event([
            'summary' => $summary,
            'location' => 'Онлайн',
            'description' => 'Видеоконференция через Google Meet',
            'start' => [
                'dateTime' => $startTime,
                'timeZone' => 'Europe/Moscow',  // Укажите нужный часовой пояс
            ],
            'end' => [
                'dateTime' => $endTime,
                'timeZone' => 'Europe/Moscow',  // Укажите нужный часовой пояс
            ],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => uniqid(),  // Уникальный идентификатор для запроса
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet',
                    ],
                    'status' => [
                        'statusCode' => 'success',
                    ],
                ],
            ],
        ]);

        $calendarId = 'primary';
        $event = $this->service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
        return $event->getHangoutLink();
    }

    public function createInterview($app_id)
    {
        try {
            $date = $_POST['date'];
            $name = trim($_POST['name']);
            $type = $_POST['type'];
            if(isset($_POST['user'])){
                $user_ID = $_POST['user'];
            }else{
                $user_ID = $_SESSION['user_id'];
            }
            if(empty($date) || empty($name) || empty($type)){
                throw new Exception('Не заполнены данные');
            }
            date_default_timezone_set('Europe/Moscow');
            $startTime = date('Y-m-d\TH:i:sP', strtotime($date));
            $endTime = date('Y-m-d\TH:i:sP', strtotime($date . ' +1 hour'));
            $interview = 'Интервью с кандидатом '.$name;
            $link = $this->createEvent($interview,$startTime,$endTime);
            if(!$link){
                throw new Exception('Ошибка создания встречи');
            }
            $result = $this->model->createInterview($app_id,$user_ID,$link,$type,$startTime);
            if ($result){
                echo json_encode(['success' => true]);
                exit();
            }else{
                throw new Exception('Ошибка записи в базу данных');
            }
        }catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
            exit();
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }

    public function fetchToken()
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['access_token'] = $token;
        header('Location: http://localhost');
        exit;
    }

}