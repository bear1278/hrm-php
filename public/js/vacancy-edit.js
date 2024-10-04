document
  .getElementById("edit-form")
  .addEventListener("submit", function (event) {
    event.preventDefault(); // Предотвращаем отправку формы

    let form = event.target;
    let formData = new FormData(form);

    fetch("/edit", {
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
