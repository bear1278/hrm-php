import {add} from './add.js'
add()

const form = document.getElementById('vacancy-form');
const errorMessageDiv = document.getElementById('error-message-edit'); // Элемент для отображения ошибок

form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('/status/add', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (response.status===302) {
                window.location.href='/status'
            }
            return response.json(); // Обрабатываем ответ как JSON
        })
        .then(data => {
            if (data.error) {
                // Если есть ошибка, показываем ее
                errorMessageDiv.textContent = data.error; // Устанавливаем текст ошибки
                errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
            } else {
                console.log('Success:', data);
                modal.style.display = 'none'; // Закрываем модальное окно
                window.location.href = '/status'; // Перенаправление
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            errorMessageDiv.textContent = 'Произошла ошибка при обработке запроса.'; // Сообщение о внутренней ошибке
            errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
        });
});
