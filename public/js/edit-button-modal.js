document.getElementById('edit-vacancy').addEventListener('click', function (e) {
    e.preventDefault();
    let errorMessageDiv = document.getElementById('edit-error-message');

    const formData = new FormData(document.getElementById('edit-vacancy-form'));

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
                errorMessageDiv.textContent = data.error;
                errorMessageDiv.style.display = 'block';
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