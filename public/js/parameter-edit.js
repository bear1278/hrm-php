// Функция для работы с модальным окном и формой
export function add() {
  const modal = document.getElementById("vacancyModal");
  const addButton = document.querySelector("#button-add");
  const closeButton = document.querySelector(".close");
  const form = document.getElementById('vacancy-form');
  const errorMessageDiv = document.getElementById('error-message'); // Элемент для ошибок

  // Получаем элементы select и input
  const parameterSelect = document.getElementById('parameter');
  const valueInput = document.getElementById('value');

  // Показать модальное окно при нажатии на кнопку "Добавить вакансию"
  addButton.addEventListener("click", function(event) {
    modal.style.display = "block"; // Показываем модальное окно
    errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при открытии формы
    updateInputAttributes(); // Обновляем атрибуты input при открытии модального окна
  });

  // Закрытие модального окна при нажатии на крестик
  closeButton.addEventListener("click", function() {
    modal.style.display = "none"; // Скрываем модальное окно
    errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при закрытии
  });

  // Обновление атрибутов input при изменении значения select
  parameterSelect.addEventListener('change', updateInputAttributes);

  // Функция для обновления атрибутов input в зависимости от выбранного значения
  function updateInputAttributes() {
    if (parameterSelect.options[parameterSelect.selectedIndex].text === 'unapply_penalty') { // проверка на конкретное значение
      valueInput.removeAttribute('min');  // Удаляем атрибут min
      valueInput.setAttribute('max', '0'); // Устанавливаем max=0
      valueInput.value = ''; // Очищаем поле ввода для предотвращения ошибок
    } else {
      valueInput.removeAttribute('max');  // Удаляем атрибут max
      valueInput.setAttribute('min', '0'); // Устанавливаем min=0
      valueInput.value = ''; // Очищаем поле ввода для предотвращения ошибок
    }
  }

  // Закрыть модальное окно при клике за его пределами
  window.addEventListener("click", function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
      errorMessageDiv.style.display = 'none'; // Скрываем сообщение об ошибке при закрытии
    }
  });

  // Обработка отправки формы
  form.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('/history/change', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (!response.ok) {
        return response.json().then((data) => {
          throw new Error(data.error || "Произошла ошибка");
        });
      }
      return response.json(); // Обрабатываем ответ как JSON
    })
    .then(data => {
      if (data.error) {
        // Если есть ошибка, показываем ее
        errorMessageDiv.textContent = data.error; // Устанавливаем текст ошибки
        errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
      } else {
        console.log('Success:', data);
        modal.style.display = 'none'; // Закрытие модального окна
        window.location.href = '/history'; // Перенаправление
      }
    })
    .catch((error) => {
      console.error('Error:', error);
      errorMessageDiv.textContent = `${error}`; // Сообщение о внутренней ошибке
      errorMessageDiv.style.display = 'block'; // Показываем сообщение об ошибке
    });
  });
}

// Запуск функции
add();
