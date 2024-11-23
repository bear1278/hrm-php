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
        if (event.target === modal) {
            modal.style.display = "none";
            errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при закрытии
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Отключаем стандартное действие отправки формы

        const formData = new FormData(form);
        const file = formData.get('image');
        const maxSize = 2*1024*1024; // Максимальный размер в байтах (2MB)

        // Проверка на наличие файла
        if (!file) {
            errorMessageDiv.innerText = "Выберите файл.";
            errorMessageDiv.style.display = 'block';
            return;
        }

        // Проверка: является ли файл изображением
        if (!file.type.startsWith("image/")) {
            errorMessageDiv.innerText = "Файл не является изображением.";
            errorMessageDiv.style.display = 'block';
            return;
        }

        // Проверка: размер файла
        if (file.size > maxSize) {
            errorMessageDiv.innerText = "Файл слишком большой. Пожалуйста, выберите файл меньше 2MB.";
            errorMessageDiv.style.display = 'block';
            return;
        }

        // Проверка: файл не битый
        const img = new Image();
        const url = URL.createObjectURL(file);

        img.onload = function() {
            console.log("Файл успешно загружен и не повреждён.");
            errorMessageDiv.style.display = 'none'; // Очищаем сообщение об ошибке, если изображение загрузилось
            URL.revokeObjectURL(url); // Освобождаем память

            // Отправка формы после всех успешных проверок
            fetch('/profile/edit', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        if(response.status === 500){
                            const redirectUrl = response.headers.get('Location');
                            window.location.href = redirectUrl;
                            return;
                        }
                        return response.json().then((data) => {
                            throw new Error(data.error || "Произошла ошибка");
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        errorMessageDiv.textContent = data.error; // Устанавливаем текст ошибки
                        errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
                    } else {
                        console.log('Success:', data);
                        modal.style.display = 'none'; // Закрытие модального окна
                        window.location.href = '/profile'; // Перенаправление
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    errorMessageDiv.textContent = `${error}`; // Сообщение о внутренней ошибке
                    errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
                });
        };

        img.onerror = function() {
            errorMessageDiv.innerText = "Файл повреждён или не может быть загружен.";
            errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
            URL.revokeObjectURL(url); // Освобождаем память
        };

        img.src = url; // Устанавливаем URL для загрузки изображения
    });
}

add();
