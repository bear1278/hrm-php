// Получаем элементы
export function add(){
    const modal = document.getElementById("vacancyModal");
    const addButton = document.querySelector("#button-add");
    const closeButton = document.querySelector(".close");
    const form = document.getElementById('vacancy-form');

// Показать модальное окно при нажатии на кнопку "Добавить вакансию"
    addButton.addEventListener("click", function(event) {
        modal.style.display = "block"; // Показываем модальное окно
    });

    closeButton.addEventListener("click", function() {
        modal.style.display = "none"; // Скрываем модальное окно
    });

// Закрыть модальное окно при клике за его пределами
    window.addEventListener("click", function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });
}
