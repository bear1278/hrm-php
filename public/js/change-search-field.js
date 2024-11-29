const columnSelect = document.getElementById('column-select');
const searchInput = document.getElementById('search-input');
const columnInput = document.getElementById('column');
let additionalSelect; // Для хранения нового select


columnSelect.addEventListener('change', function() {
    const selectedType = this.value;
    columnInput.value = this.options[this.selectedIndex].textContent.trim();

    if (additionalSelect) {
        additionalSelect.remove();
        additionalSelect = null;
    }

    if (selectedType === 'LONG' || selectedType === 'DATETIME') {
        searchInput.type = 'number';
        additionalSelect = document.createElement('select');
        additionalSelect.className = this.className;
        additionalSelect.id = 'comparison-select';
        const options = [
            { value: '', text: 'равно' },
            { value: 'min', text: 'больше' },
            { value: 'max', text: 'меньше' }
        ];

        options.forEach(optionData => {
            const option = document.createElement('option');
            option.value = optionData.value;
            option.textContent = optionData.text;
            additionalSelect.appendChild(option);
        });
        this.parentNode.insertBefore(additionalSelect, searchInput);
        additionalSelect.addEventListener('change',function (){
            columnInput.value = this.value.trim() + columnInput.value;
        });
    } else if (selectedType === 'BLOB' || selectedType === 'VAR_STRING') {
        searchInput.type = 'text';
    }
    if (selectedType === 'DATETIME') {
        searchInput.type = 'date';
    }
});
