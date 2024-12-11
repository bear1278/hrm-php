document.querySelector("#create-interview-btn").addEventListener("click", function (event) {
    event.stopPropagation();
    event.preventDefault();

    let type = document.getElementById('btn-active').value;
    let date = document.getElementById('date').value;
    let name = document.getElementById('user_name').value;
    let user = 0;
    if(document.getElementById('user')){
        user = document.getElementById('user').value;
    }


    const data = {
        name: name,
        type: type,
        date: date,
    };

    if (user!==0){
        data.user = user;
    }

    const urlEncodedData = new URLSearchParams(data).toString();

    fetch(window.location.href+'/interview', {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: urlEncodedData,
    })
        .then((response) => {
            if (response.ok) {
                return response.json();
            } else if (response.status === 500) {
                return response.json().then((data) => {
                    const errorMessage = data.error || "Произошла внутренняя ошибка сервера";
                    window.location.href = `/error?message=${encodeURIComponent(errorMessage)}`;
                });
            }else if (response.status === 302) {
                window.location.href = document.getResponseHeader('Location');
            }
            else {
                return response.json().then((data) => {
                    throw new Error(data.error || "Произошла ошибка");
                });
            }
        })
        .then((data) => {
            if (data.success) {
                alert("Интервью создано.");
                window.location.reload();
            } else {
                alert("Ошибка");
            }
        })
        .catch((error) => {
            alert("Произошла ошибка: " + error.message);
            console.error(error);
        });

});


