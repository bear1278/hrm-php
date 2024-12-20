document.addEventListener('DOMContentLoaded', function() {
    const Button = document.querySelectorAll(".button-unapply");

    Button.forEach((button) => {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const id = this.value;
            const row = this.closest(".vacancy-item");

            if (confirm("Вы уверены, что хотите отказаться от отклика на эту вакансию?")) {
                fetch("/applications/delete", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: "application_ID=" + encodeURIComponent(id),
                })
                    .then((response) => {
                        if (response.ok) {
                            return response.json();
                        } else if (response.status === 500) {
                            return response.json().then((data) => {
                                const errorMessage =
                                    data.error || "Произошла внутренняя ошибка сервера";
                                window.location.href = `/error?message=${encodeURIComponent(
                                    errorMessage
                                )}`;
                            });
                        } else {
                            return response.json().then((data) => {
                                throw new Error(data.error || "Произошла ошибка");
                            });
                        }
                    })
                    .then((data) => {
                        if (data.success) {
                            row.remove();
                            alert("Отклик отменен.");
                        } else {
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
});
