const modal = document.getElementById("vacancyModal");
const form = document.getElementById('vacancy-form');
const errorMessageDiv = document.getElementById('error-message');

document.getElementById('submit-vacancy').addEventListener('click', function () {
    const formData = new FormData(form);

    let processes = [];
    document.querySelectorAll('.modal-form[id^="process-"]').forEach(function (process, index) {
        let processData = {
            orderable: process.querySelector('input[name="orderable"]').value,
            description: process.querySelector('textarea[name="process-description"]').value,  // Используем value для textarea
            type: process.querySelector('select[name="type"]').value
        };
        processes.push(processData);
    });

    // Преобразуем FormData в объект
    const formDataObj = {};
    formData.forEach((value, key) => {
        formDataObj[key] = value;
    });

    // Собираем массив с выбранными навыками (ID навыков)
    const skills = Array.from(document.getElementById('vacancy-skills').selectedOptions)
        .map(option => parseInt(option.value));  // Преобразуем каждое значение в int

    formDataObj.skills = skills;  // Добавляем массив skills в объект данных
    formDataObj.processes = processes;  // Добавляем процессы

    fetch('/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',  // Устанавливаем тип контента
        },
        body: JSON.stringify(formDataObj),  // Отправляем JSON
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
                errorMessageDiv.textContent = data.error;
                errorMessageDiv.style.display = 'block';
            } else {
                console.log('Success:', data);
                document.getElementById('close').click();
                window.location.href = '/';
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            errorMessageDiv.textContent = `${error}`;
            errorMessageDiv.style.display = 'block';
        });
});
