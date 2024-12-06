<?php

require __DIR__ . '/vendor/autoload.php';  // Подключаем автозагрузчик Composer


use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $chatRooms;
    private $pdo;

    public function __construct() {

        $pdo = new PDO("mysql:host=localhost;dbname=hrmc", '', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->clients = new \SplObjectStorage;  // Храним все соединения
        $this->chatRooms = [];
        $this->pdo=$pdo;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Новый клиент подключен: " . $conn->resourceId . "\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        $messageParts = explode(' ', $msg);
        $command = $messageParts[0];

        if ($command === '/join') {
            $chatName = $messageParts[1];
            $this->joinChat($from, $chatName);
        } elseif ($command === '/leave') {
            $chatName = $messageParts[1];
            $this->leaveChat($from, $chatName);
        } else {
            $chatName = $this->getUserChat($from);

            if ($chatName) {
                $object = json_decode($msg);

                $this->sendMessageToChat($from, $chatName, $object->message);
                $this->SaveMessage($chatName,$object->user,$object->message);
            } else {
                $from->send("Вы не присоединились ни к одному чату. Используйте /join <название_чата>");
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $this->removeUserFromAllChats($conn);
        echo "Клиент {$conn->resourceId} отключился\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Ошибка: " . $e->getMessage() . "\n";
        $conn->close();
    }

    // Метод для присоединения пользователя к чату
    protected function joinChat(ConnectionInterface $conn, $chatName) {
        if (!isset($this->chatRooms[$chatName])) {
            $this->chatRooms[$chatName] = [];
        }

        // Добавляем пользователя в чат
        $this->chatRooms[$chatName][] = $conn;
        $this->clients->attach($conn);
    }

    // Метод для выхода пользователя из чата
    protected function leaveChat(ConnectionInterface $conn, $chatName) {
        if (isset($this->chatRooms[$chatName])) {
            $key = array_search($conn, $this->chatRooms[$chatName]);
            if ($key !== false) {
                unset($this->chatRooms[$chatName][$key]);
            }
        }
    }

    protected function getUserChat(ConnectionInterface $conn) {
        foreach ($this->chatRooms as $chatName => $clients) {
            if (in_array($conn, $clients)) {
                return $chatName;
            }
        }
        return null;
    }

    protected function sendMessageToChat(ConnectionInterface $from, $chatName, $msg) {
        foreach ($this->chatRooms[$chatName] as $client) {
            if ($from !== $client) {
                $client->send("{$msg}");
            }
        }
    }

    protected function removeUserFromAllChats(ConnectionInterface $conn) {
        foreach ($this->chatRooms as $chatName => $clients) {
            $key = array_search($conn, $clients);
            if ($key !== false) {
                unset($this->chatRooms[$chatName][$key]);
            }
        }
    }

    public function SaveMessage($application_ID,$user,$message)
    {
        try {
            $sql = "INSERT INTO chat (application_ID, user, message,date) VALUES (:id, :user, :message,:date)";
            $stmt = $this->pdo->prepare($sql);
            $date = date("Y-m-d H:i:s");
            $stmt->bindParam(':id', $application_ID);
            $stmt->bindParam(':user', $user);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':date', $date);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Ошибка: " . $e->getMessage());
        }
    }
}

$server = new App('127.0.0.1', 8080);
$server->route('/chat', new Chat, ['*']);
$server->run();





