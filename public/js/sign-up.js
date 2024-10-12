document.querySelector("form").addEventListener("submit", function (event) {
  event.preventDefault(); // Предотвращаем отправку формы
  let role = document.querySelector('input[name="role"]:checked');
  let firstname = document.querySelector('input[name="firstname"]');
  let lastname = document.querySelector('input[name="lastname"]');
  let email = document.querySelector('input[name="email"]');
  let password = document.querySelector('input[name="password"]');
  let confirmPassword = document.querySelector(
    'input[name="confirmPassword"]'
  );
  let error = "";

  if (!role) {
    error = "Пожалуйста, выберете свою роль";
  }

  if (!firstname.value || !lastname.value) {
    error = "Пожалуйста, введите свое имя и фамилию";
  }

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email.value || !emailPattern.test(email.value)) {
    error += "<br>Пожалуйста, введите корректный email";
  }

  const passwordPattern = /^[A-Za-z0-9!@#$%^&*()_+={}:;.,<>?-]+$/;
  if (!password.value || password.value.length < 6) {
    error += "<br>Пароль должен быть не менее 6 символов";
  } else if (!passwordPattern.test(password.value)) {
    error +=
      "<br>Пароль может содержать только латинские буквы и специальные символы";
  }

  if (password.value != confirmPassword.value) {
    error += "<br>Пароли не совпадают";
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
  fetch("/signup", {
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
        if (role.value==4){
          window.location.href = "/resume";
        }else{
          window.location.href = "/";
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      document.getElementById("result").innerHTML =
        '<p style="color: red;">An error occurred. Please try again.</p>';
    });
});
