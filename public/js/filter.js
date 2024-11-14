import { SetApplyButtons } from './apply.js';

document.getElementById('search-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Отменяем отправку формы
    sendFilterData(); // Вызываем функцию для отправки фильтров
});

// Функция для отправки пустого запроса на сервер
function sendEmptyPostRequest() {
    fetch('/search', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({}) // Пустое тело запроса
    })
        .then(response => {
            if (response.ok) {
                return response.json(); // Получаем JSON-ответ
            } else if (response.status === 301) {
                window.location.href = '/';
            } else {
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

// Функция для записи JSON-данных в cookies без кодирования
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = `${name}=${encodeURIComponent(JSON.stringify(value))}${expires}; path=/`;
}

// Функция для получения JSON-значения из cookies
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return JSON.parse(decodeURIComponent(parts.pop().split(";").shift()));
    }
    return null;
}

// Функция для отправки фильтров на сервер при добавлении/изменении
function sendFilterData() {
    const selectElement = document.getElementById('column-select');
    const inputElement = document.getElementById('search-input');
    const comparisonElement = document.getElementById('comparison-select');
    let prefix = '';
    if (comparisonElement) {
        prefix = comparisonElement.value;
    }

    let key = selectElement ? selectElement.options[selectElement.selectedIndex].textContent.trim() : null;
    const value = inputElement ? inputElement.value : null;
    key = prefix + key;

    // Получаем текущие фильтры из cookies
    let filtersData = getCookie('filtersData') || {};

    if (!value) {
        showError('Поле ввода не должно быть пустым');
        return;
    }

    if (key && value) {
        // Добавляем или обновляем фильтр в виде ключ-значение (map)
        filtersData[key] = { id: Date.now(), column: key, value: value };

        // Сохраняем обновленный объект в cookies
        setCookie('filtersData', filtersData, 1);
    }

    renderFilters(filtersData);
    sendEmptyPostRequest(); // Отправляем пустой POST-запрос
}

function showError(message) {
    const inputElement = document.getElementById('error');
    const errorElement = document.createElement('div');
    errorElement.textContent = message;
    errorElement.classList.add('error-message');
    inputElement.insertAdjacentElement('afterend', errorElement);
    setTimeout(() => {
        errorElement.remove();
    }, 1500);
}

function renderFilters(filtersData) {
    const filtersContainer = document.getElementById('filters');
    filtersContainer.innerHTML = '';

    // Перебираем каждый элемент в объекте filtersData и добавляем его в DOM
    Object.values(filtersData).forEach((item) => {
        const resultContainer = document.createElement('div');
        resultContainer.textContent = `${item.column}: ${item.value}`;
        resultContainer.id = `filter-${item.id}`;
        resultContainer.classList.add('filter');

        const deleteButton = document.createElement('button');
        deleteButton.classList.add('button-delete-filter');
        deleteButton.dataset.id = item.column; // Используем column в качестве идентификатора для удаления

        const icon = document.createElement('i');
        icon.classList.add('fa-solid', 'fa-xmark');
        deleteButton.appendChild(icon);
        resultContainer.appendChild(deleteButton);
        filtersContainer.appendChild(resultContainer);
    });

    attachDeleteEvents();
}

function attachDeleteEvents() {
    const deleteButtons = document.querySelectorAll('.button-delete-filter');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterColumn = button.dataset.id;
            removeFilter(filterColumn);
        });
    });
}

// Функция для удаления фильтра по column
function removeFilter(column) {
    let filtersData = getCookie('filtersData') || {};
    delete filtersData[column]; // Удаляем фильтр по ключу column
    setCookie('filtersData', filtersData, 1); // Обновляем cookies
    renderFilters(filtersData);
    sendEmptyPostRequest(); // Отправляем пустой POST-запрос
}

// Функция для обновления таблицы с результатами
function updateTable(responseData) {
    const table = document.querySelector('table');

    if (!table) {
        console.error('Таблица не найдена в DOM.');
        return;
    }

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

    tableHead.innerHTML = '';
    tableBody.innerHTML = '';

    const columns = Object.values(responseData.columns);

    if (Array.isArray(columns) && columns.length > 0) {
        const headerRow = document.createElement('tr');
        columns.forEach(column => {
            const th = document.createElement('th');
            th.textContent = column;
            headerRow.appendChild(th);
        });

        const actionTh = document.createElement('th');
        actionTh.textContent = '';
        headerRow.appendChild(actionTh);
        tableHead.appendChild(headerRow);
    } else {
        console.error("Ошибка: responseData.columns не является массивом");
        return;
    }

    if (Array.isArray(responseData.data) && responseData.data.length > 0) {
        responseData.data.forEach(row => {
            const tableRow = document.createElement('tr');
            columns.forEach(column => {
                const td = document.createElement('td');
                td.textContent = row[column] ? row[column] : '';
                tableRow.appendChild(td);
            });

            const actionTd = document.createElement('td');
            const actionButton = document.createElement('button');
            const script = document.createElement('script');
            script.type = 'module';
            script.src = '/js/apply.js';
            actionButton.classList.add('button-apply');
            actionButton.value = row['vacancy_ID'];
            actionButton.innerHTML = '<i class="fa-solid fa-check"></i>';
            actionTd.appendChild(actionButton);
            actionTd.appendChild(script);
            tableRow.appendChild(actionTd);

            tableBody.appendChild(tableRow);
        });
        SetApplyButtons();
    } else {
        const noDataRow = document.createElement('tr');
        const noDataCell = document.createElement('td');
        noDataCell.colSpan = columns.length + 1;
        noDataCell.textContent = 'Нет данных для отображения';
        noDataRow.appendChild(noDataCell);
        tableBody.appendChild(noDataRow);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let filtersData = getCookie('filtersData') || {};
    if (Object.keys(filtersData).length > 0) {
        renderFilters(filtersData);
        sendEmptyPostRequest();
    } else {
        renderFilters({});
    }
    SetApplyButtons();
});
