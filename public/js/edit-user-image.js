// Получаем элементы
export function add() {
    const modal = document.getElementById("vacancyModal");
    const addButton = document.querySelector("#button-add");
    const closeButton = document.querySelector(".close");
    const form = document.getElementById('vacancy-form');
    const errorMessageDiv = document.getElementById('error-message');

    addButton.addEventListener("click", function(event) {
        modal.style.display = "block";
        errorMessageDiv.style.display = 'none';
    });

    closeButton.addEventListener("click", function() {
        modal.style.display = "none";
        errorMessageDiv.style.display = 'none';
    });

    window.addEventListener("click", function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
            errorMessageDiv.style.display = 'none';
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const file = formData.get('image');
        const maxSize = 2*1024*1024;

        if (!file) {
            errorMessageDiv.innerText = "Выберите файл.";
            errorMessageDiv.style.display = 'block';
            return;
        }

        if (file.size > maxSize) {
            errorMessageDiv.innerText = "Файл слишком большой. Пожалуйста, выберите файл меньше 2MB.";
            errorMessageDiv.style.display = 'block';
            return;
        }

        if (!file.type.startsWith("image/")) {
            errorMessageDiv.innerText = "Файл не является изображением.";
            errorMessageDiv.style.display = 'block';
            return;
        }

        const img = new Image();
        const url = URL.createObjectURL(file);

        img.onload = function() {
            console.log("Файл успешно загружен и не повреждён.");
            errorMessageDiv.style.display = 'none';
            URL.revokeObjectURL(url);

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
                        errorMessageDiv.textContent = data.error;
                        errorMessageDiv.style.display = 'block';
                    } else {
                        console.log('Success:', data);
                        modal.style.display = 'none';
                        window.location.href = '/profile';
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    errorMessageDiv.textContent = `${error}`;
                    errorMessageDiv.style.display = 'block';
                });
        };

        img.onerror = function() {
            errorMessageDiv.innerText = "Файл повреждён или не может быть загружен.";
            errorMessageDiv.style.display = 'block';
            URL.revokeObjectURL(url);
        };

        img.src = url;
    });
}

add();
