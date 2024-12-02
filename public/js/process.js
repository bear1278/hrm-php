var countOfProcesses = 1;

document.getElementById("add-process").addEventListener("click", function () {
    var body = document.querySelector(".modal-body");
    if (countOfProcesses < 3) {
        countOfProcesses++;
        let container = document.createElement('div');
        container.classList.add('modal-form');
        container.id = 'process-' + countOfProcesses.toString();
        container.innerHTML = `
                <h5 class="modal-title">Этап ${countOfProcesses}</h5>
                <input type="hidden" name="orderable" value="${countOfProcesses}">
                <label for="vacancy-skills">Тип</label>
                <select class="input-modal" name="type" id="process-select-${countOfProcesses}" required>
                    <option value="Интервью с Hr">
                        Интервью с Hr
                    </option>
                    <option value="Техническое интервью">
                        Техническое интервью
                    </option>
                    <option value="Тестовое задание">
                        Тестовое задание
                    </option>
                </select>
                <label for="process-description">Описание этапа</label>
                <textarea class="input-modal skills-select" id="process-description" name="process-description"
                          required></textarea>`;
        body.appendChild(container);
        changeSelectProcess(document.getElementById('process-select-1'));
        document.getElementById('process-select-' + countOfProcesses).addEventListener('change', function () {
            changeSelectProcess(this);
        })
    }
});

document.getElementById("delete-process").addEventListener("click", function () {
    if (countOfProcesses > 1) {
        let container = document.getElementById('process-' + countOfProcesses.toString());
        container.remove();
        countOfProcesses--;
    }
});

document.getElementById('process-select-1').addEventListener('change', function () {
    changeSelectProcess(this);
})

function changeSelectProcess(currentSelect) {
    let indexes = [0, 1, 2];
    if (countOfProcesses > 1 && countOfProcesses < 3) {
        let firstSelect = document.getElementById('process-select-1');
        let secondSelect = document.getElementById('process-select-2');
        if (currentSelect === firstSelect) {
            if (firstSelect.selectedIndex === secondSelect.selectedIndex) {
                if (secondSelect.selectedIndex === 2) {
                    secondSelect.selectedIndex = secondSelect.selectedIndex - 1;
                } else {
                    secondSelect.selectedIndex = secondSelect.selectedIndex + 1;
                }
            }
        } else if (currentSelect === secondSelect) {
            if (firstSelect.selectedIndex === secondSelect.selectedIndex) {
                if (firstSelect.selectedIndex === 2) {
                    firstSelect.selectedIndex = firstSelect.selectedIndex - 1;
                } else {
                    firstSelect.selectedIndex = firstSelect.selectedIndex + 1;
                }
            }
        }
    } else if (countOfProcesses === 3) {
        let firstSelect = document.getElementById('process-select-1');
        let secondSelect = document.getElementById('process-select-2');
        let thirdSelect = document.getElementById('process-select-3');
        if (currentSelect === firstSelect) {
            if (firstSelect.selectedIndex === secondSelect.selectedIndex) {
                if (secondSelect.selectedIndex === 2) {
                    secondSelect.selectedIndex = secondSelect.selectedIndex - 1;
                } else {
                    secondSelect.selectedIndex = secondSelect.selectedIndex + 1;
                }
            }
        } else if (currentSelect === secondSelect) {
            if (firstSelect.selectedIndex === secondSelect.selectedIndex) {
                if (firstSelect.selectedIndex === 2) {
                    firstSelect.selectedIndex = firstSelect.selectedIndex - 1;
                } else {
                    firstSelect.selectedIndex = firstSelect.selectedIndex + 1;
                }
            }
        }
        indexes.splice(indexes.indexOf(firstSelect.selectedIndex), 1);
        indexes.splice(indexes.indexOf(secondSelect.selectedIndex), 1);
        thirdSelect.selectedIndex = indexes.pop();
    }

}