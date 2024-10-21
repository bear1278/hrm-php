<?php

namespace app\Models;

use app\Entities\Notification;
use PDO;


class NotificationModel
{

    protected $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function createNotification($userId, $applicationId, $message, $status)
    {
        $stmt = $this->pdo->prepare("INSERT INTO notifications (user_id, application_id, message, status) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $applicationId, $message, $status]);
    }

    public function getUserNotificationsWithApplicationNames($userId)
    {
        $sql = "SELECT n.*, a.name as vacancy_name, a.description
            FROM notifications AS n
            JOIN applications AS app ON n.application_id = app.application_ID
            JOIN vacancies AS a ON app.vacancy_ID = a.vacancy_ID
            WHERE n.user_id = ? AND n.is_shown = FALSE";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $notifications = [];
        foreach ($result as $row) {
            $notifications[] = new Notification(
                $row['id'],
                $row['user_id'],
                $row['application_id'],
                $row['message'],
                $row['is_shown'],
                $row['vacancy_name'],
                $row['description']
            );
        }

        return $notifications;
    }

    public function markNotificationAsShown($notificationId)
    {
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_shown = TRUE WHERE id = ?");
        return $stmt->execute([$notificationId]);
    }

    public function dismissNotification($notificationId)
    {
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_dismissed = TRUE WHERE id = ?");
        return $stmt->execute([$notificationId]);
    }
}