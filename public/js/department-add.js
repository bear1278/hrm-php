import {add} from './add.js'
add()

const form = document.getElementById('vacancy-form');
const errorMessageDiv = document.getElementById('error-message-edit'); // Элемент для отображения ошибок

form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('/department/add', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (response.status===302) {
                window.location.href='/department'
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                errorMessageDiv.textContent = data.error; // Устанавливаем текст ошибки
                errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
            } else {
                console.log('Success:', data);
                modal.style.display = 'none'; // Закрываем модальное окно
                window.location.href = '/department'; // Перенаправление
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            errorMessageDiv.textContent = `${error}`; // Сообщение о внутренней ошибке
            errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
        });
});

