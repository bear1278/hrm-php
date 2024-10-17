import { add } from './add.js';

// Вызов функции add для инициализации
add();

// Получаем элементы формы
const form = document.getElementById('vacancy-form');
const errorMessageDiv = document.getElementById('error-message-edit'); // Элемент для отображения ошибок

form.addEventListener('submit', function (e) {
    e.preventDefault(); // Предотвращаем отправку формы

    const formData = new FormData(form); // Создаем объект FormData из формы

    fetch('/skills/add', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (response.status===302) {
                window.location.href='/skills';
            }
        })
        .then(data => {
            if (data.error) {
                errorMessageDiv.textContent = data.error; // Устанавливаем текст ошибки
                errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
            } else {
                console.log('Success:', data);
                modal.style.display = 'none'; // Закрываем модальное окно
                window.location.href = '/skills'; // Перенаправление
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            errorMessageDiv.textContent = 'Произошла ошибка при обработке запроса.'; // Сообщение о внутренней ошибке
            errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
        });
});