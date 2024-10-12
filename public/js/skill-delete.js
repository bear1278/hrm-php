document.addEventListener("DOMContentLoaded", function () {
    // Находим все кнопки с классом .trash-button
    const trashButtons = document.querySelectorAll(".trash-button");

    // Для каждой кнопки вешаем обработчик события
    trashButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const ID = this.value; // Получаем ID вакансии из значения кнопки
            const row = this.closest("tr"); // Находим строку таблицы, к которой относится эта кнопка

            // Подтверждение удаления
            if (confirm("Вы уверены, что хотите удалить?")) {
                // Отправляем AJAX-запрос на сервер
                fetch("/skills/delete", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: "ID=" + encodeURIComponent(ID),
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
                            alert("Успешно удалено.");
                        } else {
                            // Если возникла ошибка
                            alert("Ошибка");
                        }
                    })
                    .catch((error) => {
                        alert("Произошла ошибка при удалении: " + error);
                        console.log(error);
                    });
            }
        });
    });
});
