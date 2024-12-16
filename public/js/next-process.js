document.querySelector("#next-process").addEventListener("click", function (event) {
    event.stopPropagation();
    event.preventDefault();

    if (confirm("Вы уверены, что перейти к следующему этапу?")) {
        fetch(window.location.href + '/next', {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: 'process='+encodeURIComponent(this.value)
        })
            .then((response) => {
                if (response.ok) {
                    return response.json();
                } else if (response.status === 500) {
                    return response.json().then((data) => {
                        const errorMessage = data.error || "Произошла внутренняя ошибка сервера";
                        window.location.href = `/error?message=${encodeURIComponent(errorMessage)}`;
                    });
                } else if (response.status === 302) {
                    window.location.href = document.getResponseHeader('Location');
                } else {
                    return response.json().then((data) => {
                        console.log(data.error);
                        throw new Error(data.error || "Произошла ошибка");
                    });
                }
            })
            .then((data) => {
                if (data.success) {
                    alert("Произошел переход на следующий этап.");
                    window.location.reload();
                } else {
                    alert("Ошибка");
                    console.log(data.error);
                }
            })
            .catch((error) => {
                alert("Произошла ошибка: " + error.message);
                console.error(error);
            });
    }

});


