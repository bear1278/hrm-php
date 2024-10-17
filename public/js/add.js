export function add() {
    const modal = document.getElementById("vacancyModal");
    const addButton = document.querySelector("#button-add");
    const closeButton = document.querySelector(".close");

    // Показать модальное окно при нажатии на кнопку "Добавить вакансию"
    addButton.addEventListener("click", function (event) {
        modal.style.display = "block"; // Показываем модальное окно
        errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при открытии
    });

    closeButton.addEventListener("click", function () {
        modal.style.display = "none"; // Скрываем модальное окно
        errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при закрытии
    });

    // Закрыть модальное окно при клике за его пределами
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
            errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при закрытии
        }
    });
}