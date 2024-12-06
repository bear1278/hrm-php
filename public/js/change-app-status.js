document.addEventListener('DOMContentLoaded', function() {
    const Button = document.querySelectorAll(".button-change");

    Button.forEach((button) => {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const id = this.id.slice(0,-1);
            const status = this.value;

            if (confirm("Вы уверены?")) {
                fetch("/applications/"+status, {
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
                            alert("Успех!");
                            window.location.reload();
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
