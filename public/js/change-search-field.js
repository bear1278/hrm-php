const columnSelect = document.getElementById('column-select');
const searchInput = document.getElementById('search-input');
let additionalSelect; // Для хранения нового select

columnSelect.addEventListener('change', function() {
    const selectedType = this.value;

    if (additionalSelect) {
        additionalSelect.remove();
        additionalSelect = null;
    }

    if (selectedType === 'LONG' || selectedType === 'DATETIME') {
        searchInput.type = 'number';

        // Создаем новый select с опциями "больше", "меньше", "равно"
        additionalSelect = document.createElement('select');
        additionalSelect.className = this.className; // Используем тот же класс, что и у первого select
        additionalSelect.id = 'comparison-select';
        const options = [
            { value: 'min', text: 'больше' },
            { value: 'max', text: 'меньше' },
            { value: '', text: 'равно' }
        ];

        options.forEach(optionData => {
            const option = document.createElement('option');
            option.value = optionData.value;
            option.textContent = optionData.text;
            additionalSelect.appendChild(option);
        });

        // Вставляем новый select после первого select
        this.parentNode.insertBefore(additionalSelect, searchInput);
    } else if (selectedType === 'BLOB' || selectedType === 'VAR_STRING') {
        searchInput.type = 'text';
    }
    if (selectedType === 'DATETIME') {
        searchInput.type = 'date';
    }
});
