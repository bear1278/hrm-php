export function SetApplyButtons() {
    const buttons = document.querySelectorAll(".button-apply"); // Находим все кнопки "Откликнуться"

    buttons.forEach((button) => {
        button.addEventListener("click", function () {
            const vacancyID = this.value; // Получаем ID вакансии из значения кнопки
            const card = this.closest(".vacancy-item"); // Находим карточку вакансии, к которой относится эта кнопка

            // Подтверждение отклика
            if (confirm("Вы уверены, что хотите откликнуться на эту вакансию?")) {
                fetch("/apply", {
                    method: "POST", headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    }, body: "vacancy_ID=" + encodeURIComponent(vacancyID),
                })
                    .then((response) => {
                        if (response.ok) {
                            return response.json();
                        } else if (response.status === 500) {
                            return response.json().then((data) => {
                                const errorMessage = data.error || "Произошла внутренняя ошибка сервера";
                                window.location.href = `/error?message=${encodeURIComponent(errorMessage)}`;
                            });
                        } else {
                            return response.json().then((data) => {
                                throw new Error(data.error || "Произошла ошибка");
                            });
                        }
                    })
                    .then((data) => {
                        if (data.success) {
                            // Если запрос успешен, удаляем карточку вакансии
                            card.remove();
                            alert("Отклик оставлен.");
                        } else {
                            // Если возникла ошибка
                            alert("Ошибка");
                        }
                    })
                    .catch((error) => {
                        alert("Произошла ошибка: " + error.message);
                        console.error(error);
                    });
            }
        });
    });
}
