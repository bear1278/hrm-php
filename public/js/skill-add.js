import {add} from './add.js'
add()
form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('/skills/add', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            console.log('Success:', data);
            modal.style.display = 'none'; // Закрытие модального окна
            window.location.href='/skills';
        })
        .catch((error) => {
            console.error('Error:', error);
        });
});
