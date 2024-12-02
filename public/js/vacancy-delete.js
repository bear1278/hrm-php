document.addEventListener("DOMContentLoaded", function () {
  const trashButtons = document.querySelectorAll(".trash-button");

  trashButtons.forEach((button) => {
    button.addEventListener("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      const vacancyID = this.value;
      const card = this.closest(".vacancy-item");

      if (confirm("Вы уверены, что хотите удалить эту вакансию?")) {
        fetch("/delete", {
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
              card.remove();
              alert("Вакансия успешно удалена.");
            } else {
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
