let flag = false;
const socket = new WebSocket('ws://127.0.0.1:8080/chat');  // Подключение к WebSocket-серверу
let id = document.querySelector(".vacancy-title").id;
let user =
socket.onopen = function () {
    console.log("Подключение установлено");
};

socket.onmessage = function (event) {
    const messagesDiv = document.getElementById("chat");
    const messageElement = document.createElement("div");
    messageElement.classList.add('container-fluid', 'd-flex');
    const message = document.createElement("div");
    message.classList.add('card', 'p-2','bg-gray', 'rounded-pill')
    message.textContent = event.data;
    let date = document.createElement('p');
    date.classList.add('fs-7', 'text-dark', 'm-0');
    const now = new Date();
    const formattedTime = `${now.getMonth() + 1}.${now.getDate()} ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
    date.textContent = formattedTime;
    message.appendChild(date);
    messageElement.appendChild(message);
    messagesDiv.appendChild(messageElement);
};

socket.onerror = function (error) {
    console.log("Ошибка: " + error.message);
    alert('Ошибка отправки сообщения');
};

socket.onclose = function () {
    console.log("Соединение закрыто");
};

document.getElementById('web-send').addEventListener('click',function (e){
    if (!flag) {
        socket.send("/join " + id);
        flag = true;
    }
    const text = document.getElementById("message").value;
    let object = {
        message: text,
        user: this.value
    };
    socket.send(JSON.stringify(object));
    console.log(JSON.stringify(object));
    document.getElementById("message").value = '';
    const messagesDiv = document.getElementById("chat");
    const messageElement = document.createElement("div");
    messageElement.classList.add('container-fluid', 'd-flex', 'justify-content-end');
    const message = document.createElement("div");
    message.classList.add('card', 'p-2','bg-info','rounded-pill');
    message.textContent = text;
    let date = document.createElement('p');
    date.classList.add('fs-7', 'text-secondary', 'm-0');
    const now = new Date();
    const formattedTime = `${now.getMonth() + 1}.${now.getDate()} ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
    date.textContent = formattedTime;
    message.appendChild(date);
    messageElement.appendChild(message);
    messagesDiv.appendChild(messageElement);
});
