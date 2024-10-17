// Получаем элементы
export function add() {
    const modal = document.getElementById("vacancyModal");
    const addButton = document.querySelector("#button-add");
    const closeButton = document.querySelector(".close");
    const form = document.getElementById('vacancy-form');
    const errorMessageDiv = document.getElementById('error-message'); // Элемент для ошибок

    // Показать модальное окно при нажатии на кнопку "Добавить вакансию"
    addButton.addEventListener("click", function(event) {
        modal.style.display = "block"; // Показываем модальное окно
        errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при открытии формы
    });

    closeButton.addEventListener("click", function() {
        modal.style.display = "none"; // Скрываем модальное окно
        errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при закрытии
    });

    // Закрыть модальное окно при клике за его пределами
    window.addEventListener("click", function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
            errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при закрытии
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch('/add', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
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
                    modal.style.display = 'none'; // Закрытие модального окна
                    window.location.href = '/'; // Перенаправление
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                errorMessageDiv.textContent = 'Произошла ошибка при обработке запроса.'; // Сообщение о внутренней ошибке
                errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
            });
    });
}
add();