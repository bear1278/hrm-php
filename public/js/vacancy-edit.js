document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editModal');
    const closeBtn = document.getElementById('edit-close');
    const editButtons = document.querySelectorAll('.edit-button');
    const vacancyForm = document.getElementById('vacancy-form-edit');
    const errorMessageDiv = document.getElementById('error-message-edit'); // Элемент для ошибок

    // Функция для открытия модального окна
    const openModal = () => {
        modal.style.display = 'block';
        errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при открытии
    };

    vacancyForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(vacancyForm);

        fetch('/edit', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then((data) => {
                        throw new Error(data.error || "Произошла ошибка");
                    });
                }
                return response.json();
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
                errorMessageDiv.textContent = `${error}`; // Сообщение о внутренней ошибке
                errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
            });
    });

    const closeModal = () => {
        modal.style.display = 'none';
        errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при закрытии
    };

    // Закрыть модальное окно по клику на кнопку закрытия
    closeBtn.addEventListener('click', closeModal);

    // Закрыть модальное окно по клику вне его
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    // При нажатии на кнопку "Редактировать"
    editButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const vacancyRow = this.closest('tr');
            const vacancyData = {
                name: vacancyRow.querySelector('td:nth-child(1)').textContent,
                description: vacancyRow.querySelector('td:nth-child(3)').textContent,
                experience: vacancyRow.querySelector('td:nth-child(4)').textContent.value,
                salary: vacancyRow.querySelector('td:nth-child(5)').textContent,
            };

            // Заполнить форму модального окна
            document.getElementById('vacancy_ID').value = this.value;
            document.getElementById('vacancy-title1').value = vacancyData.name.trim();
            document.getElementById('vacancy-description1').value = vacancyData.description.trim();
            document.getElementById('vacancy-experience1').value = parseInt(vacancyData.experience);
            document.getElementById('vacancy-salary1').value = parseInt(vacancyData.salary);

            openModal(); // Открыть модальное окно
        });
    });
});
