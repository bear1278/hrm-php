document.querySelector("#create-review-btn").addEventListener("click", function (event) {
    event.stopPropagation();
    event.preventDefault();
    let data = {};
    data.skills = [];
    let marks = document.querySelectorAll("input[name='mark']");
    marks.forEach(function (elem,index){
        let id = elem.id;
        id = id.slice(4);
        console.log(id);
        let important = document.getElementById(`important${id}`);
        data.skills[index] ={
            id: id,
            mark: elem.value,
            importance: important.value
        }
    })
    data.result=document.querySelector('input[name="result"]:checked').value;
    console.log(data);
    data = JSON.stringify(data);

    fetch(window.location.href+'/feedback', {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "data="+encodeURIComponent(data),
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
            if (data) {
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


