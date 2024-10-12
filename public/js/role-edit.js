document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editModal');
    const closeBtn = document.getElementById('edit-close');
    const editButtons = document.querySelectorAll('.edit-button');
    const vacancyForm = document.getElementById('vacancy-form-edit');

    // Функция для открытия модального окна
    const openModal = () => {
        modal.style.display = 'block';
    };

    vacancyForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(vacancyForm);

        fetch('/role', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка выполнения запроса');
                }
                return response.text();
            })
            .then(data => {
                console.log('Success:', data);
                modal.style.display = 'none'; // Закрытие модального окна
                window.location.href='/';
            })
            .catch((error) => {
                console.error('Error:', error);
            });

    });

    const closeModal = () => {
        modal.style.display = 'none';
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

            document.getElementById('user_ID').value = this.value;

            openModal(); // Открыть модальное окно
        });
    });
});
