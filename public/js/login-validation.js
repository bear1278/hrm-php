document.querySelector("form").addEventListener("submit", function (event) {
  event.preventDefault(); // Предотвращаем отправку формы

  let email = document.querySelector('input[name="email"]');
  let password = document.querySelector('input[name="password"]');
  let error = "";

  // Простейшая проверка email
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email.value || !emailPattern.test(email.value)) {
    error = "Пожалуйста, введите корректный email";
  }

  // Проверка пароля (минимум 6 символов)
  const passwordPattern = /^[A-Za-z0-9!@#$%^&*()_+={}:;.,<>?-]+$/;
  if (!password.value || password.value.length < 6) {
    error += "<br>Пароль должен быть не менее 6 символов";
  } else if (!passwordPattern.test(password.value)) {
    error +=
      "<br>Пароль может содержать только латинские буквы и специальные символы";
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

  // Отправляем данные формы на сервер с использованием fetch
  fetch("/login", {
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
    })
    .then((data) => {
      // Если нет ошибок и данные успешны
      document.getElementById("result").innerHTML = "";

      if (data.error) {
        document.getElementById(
          "result"
        ).innerHTML = `<p style="color: red;">${data.error}</p>`;
      }

      if (data.success) {
        // Перенаправление на главную страницу при успешном входе
        window.location.href = "/";
      }
    })
    .catch((error) => {
      // Обработка ошибок
      document.getElementById(
        "result"
      ).innerHTML = `<p style="color: red;">${error.message}</p>`;
    });
});
