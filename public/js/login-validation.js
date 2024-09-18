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
  if (!password.value || password.value.length < 6) {
    error = error ? error + "<br>" : ""; // Если ошибка уже есть, добавляем новую через <br>
    error += "Пароль должен быть не менее 6 символов";
  }

  if (error) {
    // Отображаем ошибки
    document.getElementById(
      "result"
    ).innerHTML = `<p style="color: red;">${error}</p>`;
    return;
  }

  // Если ошибок нет, отправляем форму
  const formData = new FormData(this);

  fetch("/login", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((data) => {
      document.getElementById("result").innerHTML = data;
    })
    .catch((error) => {
      console.error("Ошибка:", error);
      document.getElementById("result").innerHTML = "Произошла ошибка";
    });
});
