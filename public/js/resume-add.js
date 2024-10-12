document.querySelector("form").addEventListener("submit", function (event) {
    event.preventDefault(); // Предотвращаем отправку формы
    let phone_number = document.querySelector('input[name="phone_number"]');
    let resume = document.querySelector('input[name="resume"]');
    let experience_years = document.querySelector('input[name="experience_years"]');
    let location = document.querySelector('input[name="location"]');

    let error = "";

    if (!phone_number.value || !resume.value || !experience_years.value || !location.value) {
        error = "Пожалуйста, заполните все поля";
    }

    const phonePattern = /^\+?[0-9]{1,4}[-.\s]?(\(?\d{1,3}\)?[-.\s]?)?\d{1,3}[-.\s]?\d{1,4}[-.\s]?\d{1,4}$/;
    if (!phone_number.value || !phonePattern.test(phone_number.value)) {
        error += "<br>Пожалуйста, введите корректный номер телефона";
    }

    if (experience_years.value<0){
        error += "<br>Пожалуйста, введите корректное значение опыта";
    }

    if (error) {
        // Отображаем ошибки
        document.getElementById(
            "result"
        ).innerHTML = `<p style="color: red;">${error}</p>`;
        return;
    }

    let form = event.target;
    let formData = new FormData(form);

    // Send the form data to the server using fetch
    fetch("/resume", {
        method: "POST",
        body: formData,
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
                if (response.status===301){
                    window.location.href = "/"
                }
                // Если статус ответа вне диапазона 200-299, например 400 или 401
                return response.json().then((data) => {
                    throw new Error(data.error || "Произошла ошибка");
                });
            }
        }) // Parse the JSON response
        .then((data) => {
            // Clear previous error message
            document.getElementById("result").innerHTML = "";

            if (data.error) {
                // Display error if exists
                document.getElementById(
                    "result"
                ).innerHTML = `<p style="color: red;">${data.error}</p>`;
            } else if (data.success) {
                // Redirect on successful login

                    window.location.href = "/";

            }
        })
        .catch((error) => {
            console.error("Error:", error);
            document.getElementById("result").innerHTML =
                '<p style="color: red;">An error occurred. Please try again.</p>';
        });
});
