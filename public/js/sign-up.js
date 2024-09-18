document.querySelector("form").addEventListener("submit", function (event) {
  event.preventDefault(); // Предотвращаем отправку формы
  let firstnmae = document.querySelector('input[name="firstname"]');
  let lastname = document.querySelector('input[name="lastname"]');
  let email = document.querySelector('input[name="email"]');
  let password = document.querySelector('input[name="password"]');
  let confirmPpassword = document.querySelector(
    'input[name="confirmPassword"]'
  );
  let error = "";

  if (!firstnmae.value || !lastname.value) {
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

  if (password.value != confirmPpassword.value) {
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
    .then((response) => response.json()) // Parse the JSON response
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
