<?php

namespace app\Controllers;

use app\Models\NotificationModel;

class NotificationController
{

    private $model;

    public function __construct()
    {
        $this->model = new NotificationModel();
    }

    public function getNotifications()
    {
        $userId = $_SESSION['user_id'];
        $notifications = $this->model->getUserNotificationsWithApplicationNames($userId);

        $notificationsArray = array_map(function ($notification) {
            return $notification->toArray();
        }, $notifications);

        echo json_encode($notificationsArray);
    }

    public function updateNotificationStatus()
    {
        $notificationId = $_POST['notification_id'];
        $action = $_POST['action'];

        if ($action === 'ok') {
            $this->model->markNotificationAsShown($notificationId);
        } elseif ($action === 'cancel') {
            $this->model->dismissNotification($notificationId);
        }

        echo json_encode(['success' => true]);
    }
}