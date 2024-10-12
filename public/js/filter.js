import {SetApplyButtons} from './apply.js'
document.getElementById('search-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Отменяем отправку формы
    sendFilterData(); // Вызываем функцию для отправки фильтров
});

// Функция для отправки фильтров на сервер
function sendUpdatedFiltersToServer(filtersData) {
    fetch('/search', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ search: filtersData })
    })
        .then(response => {
            if (response.ok) {
                return response.json(); // Получаем JSON-ответ
            } else if (response.status===301){
                window.location.href='/';
            }else
            {
                throw new Error('Ошибка при отправке данных');
            }
        })
        .then(data => {
            // Обновляем таблицу с новыми данными
            updateTable(data);
        })
        .catch(error => {
            console.error('Ошибка сети:', error);
        });
}

// Функция для отправки фильтров на сервер при добавлении/изменении
function sendFilterData() {
    const selectElement = document.getElementById('column-select');
    const inputElement = document.getElementById('search-input');
    const comparisonElement = document.getElementById('comparison-select');
    let prefix='';
    if (comparisonElement){
        prefix=comparisonElement.value;
    }

    // Получаем текст внутри выбранного option и убираем лишние пробелы
    let key = selectElement ? selectElement.options[selectElement.selectedIndex].textContent.trim() : null;
    const value = inputElement ? inputElement.value : null; // Берем значение из input, если есть
    key= prefix+key;
    // Получаем данные из localStorage по ключу "filtersData" или создаем пустой массив, если данных нет
    let filtersData = JSON.parse(localStorage.getItem('filtersData')) || [];

    if (!value) {
        showError('Поле ввода не должно быть пустым');
        return;
    }

    // Если есть новые данные для добавления
    if (key && value) {
        // Проверяем, существует ли уже фильтр с таким ключом и значением
        const existingIndex = filtersData.findIndex(item => item.column === key && item.value === value);

        if (existingIndex !== -1) {
            // Если фильтр уже существует, обновляем его значение
            filtersData[existingIndex].value = value;
        } else {
            // Если фильтра нет, добавляем новый с уникальным id
            filtersData.push({ id: Date.now(), column: key, value: value });
        }

        // Сохраняем обновленный массив в localStorage под ключом "filtersData"
        localStorage.setItem('filtersData', JSON.stringify(filtersData));
    }

    // Обновляем отображение фильтров
    renderFilters(filtersData);

    // Отправляем обновленные фильтры на сервер
    sendUpdatedFiltersToServer(filtersData);
}

function showError(message) {
    const inputElement = document.getElementById('error');
    const errorElement = document.createElement('div');
    errorElement.textContent = message;
    errorElement.classList.add('error-message');

    // Добавляем сообщение об ошибке под input
    inputElement.insertAdjacentElement('afterend', errorElement);

    // Удаляем сообщение об ошибке через 500 мс
    setTimeout(() => {
        errorElement.remove();
    }, 1500);
}

function renderFilters(filtersData) {
    const filtersContainer = document.getElementById('filters');
    filtersContainer.innerHTML = ''; // Очищаем контейнер перед добавлением новых данных

    filtersData.forEach((item) => {
        const resultContainer = document.createElement('div');
        resultContainer.textContent = `${item.column}: ${item.value}`;
        resultContainer.id = `filter-${item.id}`; // Присваиваем уникальный id контейнеру
        resultContainer.classList.add('filter');

        // Создаем кнопку для удаления
        const deleteButton = document.createElement('button');
        deleteButton.classList.add('button-delete-filter');
        deleteButton.dataset.id = item.id; // Присваиваем уникальный id кнопке удаления

        // Создаем элемент <i> для иконки
        const icon = document.createElement('i');
        icon.classList.add('fa-solid', 'fa-xmark'); // Добавляем классы для иконки

        // Добавляем иконку в кнопку
        deleteButton.appendChild(icon);

        // Добавляем кнопку удаления в контейнер
        resultContainer.appendChild(deleteButton);
        filtersContainer.appendChild(resultContainer);
    });

    // Привязываем события удаления к каждой кнопке
    attachDeleteEvents();
}

// Функция привязки событий удаления к кнопкам
function attachDeleteEvents() {
    const deleteButtons = document.querySelectorAll('.button-delete-filter');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterId = parseInt(button.dataset.id);
            removeFilter(filterId);
        });
    });
}

// Функция для удаления фильтра по id
function removeFilter(id) {
    let filtersData = JSON.parse(localStorage.getItem('filtersData')) || [];

    // Удаляем фильтр с указанным id
    filtersData = filtersData.filter(item => item.id !== id);

    // Сохраняем обновленные фильтры в localStorage
    localStorage.setItem('filtersData', JSON.stringify(filtersData));

    // Обновляем отображение фильтров
    renderFilters(filtersData);

    // Отправляем обновленные фильтры на сервер
    sendUpdatedFiltersToServer(filtersData);
}


// Функция для обновления таблицы с результатами (ранее написанная)
function updateTable(responseData) {
    const table = document.querySelector('table');

    if (!table) {
        console.error('Таблица не найдена в DOM.');
        return; // Прекращаем выполнение, если таблицы нет
    }

    // Проверяем наличие <thead> и <tbody> и создаем их, если они отсутствуют
    let tableHead = table.querySelector('thead');
    if (!tableHead) {
        tableHead = document.createElement('thead');
        table.appendChild(tableHead);
    }

    let tableBody = table.querySelector('tbody');
    if (!tableBody) {
        tableBody = document.createElement('tbody');
        table.appendChild(tableBody);
    }

    // Очищаем текущее содержимое thead и tbody
    tableHead.innerHTML = '';
    tableBody.innerHTML = '';

    // Создаем шапку таблицы (thead)
    const headerRow = document.createElement('tr');
    responseData.columns.forEach(column => {
        const th = document.createElement('th');
        th.textContent = column; // Добавляем название столбца
        headerRow.appendChild(th);
    });

    // Добавляем пустую колонку для кнопки действия
    const actionTh = document.createElement('th');
    actionTh.textContent = ''; // Пусть будет пустая ячейка
    headerRow.appendChild(actionTh);

    // Добавляем строку шапки в thead
    tableHead.appendChild(headerRow);

    // Заполняем тело таблицы (tbody) данными
    if (responseData.data && responseData.data.length > 0) {
        responseData.data.forEach(row => {
            const tableRow = document.createElement('tr');

            responseData.columns.forEach(column => {
                const td = document.createElement('td');
                td.textContent = row[column] ? row[column] : ''; // Добавляем значение ячейки
                tableRow.appendChild(td);
            });

            // Добавляем кнопку действий для каждой строки
            const actionTd = document.createElement('td');
            const actionButton = document.createElement('button');
            const script = document.createElement('script');
            script.type='module';
            script.src='/js/apply.js';
            actionButton.classList.add('button-apply');
            actionButton.value = row['vacancy_ID']; // Передаем значение ID вакансии
            actionButton.innerHTML = '<i class="fa-solid fa-check"></i>'; // Иконка
            actionTd.appendChild(actionButton);
            actionTd.appendChild(script);
            tableRow.appendChild(actionTd);

            tableBody.appendChild(tableRow);
        });
        SetApplyButtons();
    } else {
        // Если нет данных, добавляем сообщение "Нет данных для отображения"
        const noDataRow = document.createElement('tr');
        const noDataCell = document.createElement('td');
        noDataCell.colSpan = responseData.columns.length + 1; // +1 для колонки с кнопкой
        noDataCell.textContent = 'Нет данных для отображения';
        noDataRow.appendChild(noDataCell);
        tableBody.appendChild(noDataRow);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let filtersData = JSON.parse(localStorage.getItem('filtersData')) || [];
    if (filtersData.length > 0) {
        renderFilters(filtersData);
        sendUpdatedFiltersToServer(filtersData);
    } else {
        renderFilters([]);
    }
    SetApplyButtons();
});
