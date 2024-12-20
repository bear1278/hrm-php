const buttons = document.querySelectorAll(".button-apply");

buttons.forEach(function (button) {
    button.addEventListener("click", function (event) {
        event.stopPropagation();
        event.preventDefault();

        let card;
        const vacancyID = this.value;
        if (this.id!=='vacancy-page'){
            card = this.closest(".vacancy-item");
        }

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
                        if (this.id!=='vacancy-page'){
                            card.remove();
                        }
                        alert("Отклик оставлен.");
                        window.location.reload();
                    } else {
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

