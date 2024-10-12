 export function SetApplyButtons(){
    const Button = document.querySelectorAll(".button-apply");

    Button.forEach((button) => {
        button.addEventListener("click", function () {
            const vacancyID = this.value; // Получаем ID вакансии из значения кнопки
            const row = this.closest("tr"); // Находим строку таблицы, к которой относится эта кнопка

            // Подтверждение удаления
            if (confirm("Вы уверены, что хотите откликнуться на эту вакансию?")) {
                fetch("/apply", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: "vacancy_ID=" + encodeURIComponent(vacancyID),
                })
                    .then((response) => {
                        if (response.ok) {
                            // Если статус ответа 200-299
                            return response.json(); // Получаем тело ответа
                        } else if (response.status === 500) {
                            // Если статус ответа 500, перенаправляем на страницу ошибки
                            return response.json().then((data) => {
                                const errorMessage =
                                    data.error || "Произошла внутренняя ошибка сервера";
                                window.location.href = `/error?message=${encodeURIComponent(
                                    errorMessage
                                )}`;
                            });
                        } else {
                            // Если статус ответа вне диапазона 200-299, например 400 или 401
                            return response.json().then((data) => {
                                throw new Error(data.error || "Произошла ошибка");
                            });
                        }
                    })
                    .then((data) => {
                        if (data.success) {
                            // Если запрос успешен, удаляем строку из таблицы
                            row.remove();
                            alert("Отклик оставлен.");
                        } else {
                            // Если возникла ошибка
                            alert("Ошибка");
                        }
                    })
                    .catch((error) => {
                        alert("Произошла ошибка: " + error);
                        console.log(error);
                    });
            }
        });
    });
}
