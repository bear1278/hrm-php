const form = document.getElementById('vacancy-form');
const errorMessageDiv = document.getElementById('error-message'); // Элемент для ошибок


document.getElementById('edit-image').addEventListener('click', function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    const file = formData.get('image');
    const maxSize = 2 * 1024 * 1024; // Максимальный размер в байтах (2MB)


    if (!file) {
        errorMessageDiv.innerText = "Выберите файл.";
        errorMessageDiv.style.display = 'block';
        return;
    }

    if (!file.type.startsWith("image/")) {
        errorMessageDiv.innerText = "Файл не является изображением.";
        errorMessageDiv.style.display = 'block';
        return;
    }
    if (file.size > maxSize) {
        errorMessageDiv.innerText = "Файл слишком большой. Пожалуйста, выберите файл меньше 2MB.";
        errorMessageDiv.style.display = 'block';
        return;
    }

    const img = new Image();
    const url = URL.createObjectURL(file);

    img.onload = function () {
        console.log("Файл успешно загружен и не повреждён.");
        errorMessageDiv.style.display = 'none';
        URL.revokeObjectURL(url);

        fetch('/profile/edit', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 500) {
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
                    document.getElementById('close').click();
                    window.location.href = '/profile'; // Перенаправление
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                errorMessageDiv.textContent = `${error}`; // Сообщение о внутренней ошибке
                errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
            });
    };

    img.onerror = function () {
        errorMessageDiv.innerText = "Файл повреждён или не может быть загружен.";
        errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
        URL.revokeObjectURL(url); // Освобождаем память
    };

    img.src = url;
});

