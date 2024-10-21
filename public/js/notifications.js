function loadNotifications() {
  fetch('/get-notifications')
  .then(response => response.json())
  .then(notifications => {
    const notificationsContainer = document.getElementById('notifications');
    notificationsContainer.innerHTML = ''; // Очищаем контейнер

    const hiddenNotifications = JSON.parse(
        localStorage.getItem('hiddenNotifications')) || [];

    console.log("Загруженные уведомления:", notifications);

    notifications.forEach(notification => {
      if (hiddenNotifications.includes(notification.id)) {
        return;
      }

      console.log("Notification ID: " + notification.id);
      const notificationDiv = document.createElement('div');
      notificationDiv.classList.add('notification-card');
      notificationDiv.setAttribute('data-id', notification.id);
      notificationDiv.innerHTML = `
            <p>${notification.message}</p>
             <p>Заявка: ${notification.vacancy_name}</p>
             <p>Описание заявки: ${notification.vacancy_description}</p>
            <button onclick="handleNotification(${notification.id}, 'ok')">OK</button>
            <button onclick="handleNotification(${notification.id}, 'cancel')">Отмена</button>
          `;
      notificationsContainer.appendChild(notificationDiv);
    });
  })
  .catch(error => console.error("Ошибка загрузки уведомлений:", error));
}

function handleNotification(notificationId, action) {
  console.log("Обработка уведомления:", notificationId, "Действие:", action); // Логируем вызов функции

  if (action === 'cancel') {
    let hiddenNotifications = JSON.parse(
        localStorage.getItem('hiddenNotifications')) || [];
    hiddenNotifications.push(notificationId); // Добавляем ID в список скрытых
    localStorage.setItem('hiddenNotifications',
        JSON.stringify(hiddenNotifications));

    const notificationElement = document.querySelector(
        `.notification-card[data-id='${notificationId}']`);
    if (notificationElement) {
      notificationElement.remove();
    } else {
      console.error("Элемент уведомления не найден для удаления.");
    }
    return;
  }

  const params = new URLSearchParams();
  params.append('notification_id', notificationId);
  params.append('action', action);

  fetch('/update-notification-status', {
    method: 'POST',
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: params.toString()
  })
  .then(response => response.json())
  .then(result => {
    console.log("Ответ сервера:", result);
    if (result.success) {
      loadNotifications();
    } else {
      console.error("Ошибка обновления уведомления:", result);
    }
  })
  .catch(error => console.error("Ошибка запроса:", error));
}

loadNotifications();
