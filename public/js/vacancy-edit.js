document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.edit-button');

    editButtons.forEach((button) => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            let department = document.getElementById('edit-vacancy-department');
            let skillsList = document.getElementById('edit-vacancy-skills')
            document.getElementById('vacancy_id').value=this.value;
            fetch('/vacancy/'+this.value+'/json', {
                method: 'GET'
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
                    document.getElementById('edit-vacancy-title').value=data.name;
                    department.selectedIndex=data.department-1;
                    data.skills.forEach(function (value) {
                        let skill = value.skill_ID;
                        let options = skillsList.querySelectorAll('option');
                        options.forEach((option) => {
                            if (option.value == skill) {
                                option.selected = true;
                            }
                        });
                    });
                    document.getElementById('edit-vacancy-description').value=data.description;
                    document.getElementById('edit-vacancy-experience').value=data.experience;
                    document.getElementById('edit-vacancy-salary').value=data.salary;
                })
                .catch((error) => {
                    alert("Ошибка загрузки данных о вакансии")
                });
        });
    });
});