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

        fetch('/edit', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
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
            const vacancyRow = this.closest('tr');
            const vacancyData = {
                name: vacancyRow.querySelector('td:nth-child(1)').textContent,
                description: vacancyRow.querySelector('td:nth-child(3)').textContent,
                experience: vacancyRow.querySelector('td:nth-child(4)').textContent,
                salary: vacancyRow.querySelector('td:nth-child(5)').textContent,
            };

            // Заполнить форму модального окна
            document.getElementById('vacancy_ID').value = this.value;
            document.getElementById('vacancy-title1').value = vacancyData.name;
            document.getElementById('vacancy-description1').value = vacancyData.description;
            document.getElementById('vacancy-experience1').value = vacancyData.experience;
            document.getElementById('vacancy-salary1').value = vacancyData.salary;

            openModal(); // Открыть модальное окно
        });
    });
});
