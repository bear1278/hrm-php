async function sendRequest()  {
    let key = 0;
    try {
        const response = await fetch('/key', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
        });
        if (response.ok) {
            const data = await response.json();
            key = data.key;
        } else if (response.status === 301) {
            window.location.href = '/';
        } else {
            throw new Error('Ошибка при отправке данных');
        }
    } catch (error) {
        alert('Ошибка получения ключа' + error);
    }
    return key;
}

document.addEventListener('DOMContentLoaded', async function() {
    console.log(document.cookie);
    let key = await sendRequest();  // Wait for the key to be fetched
    let data = [];
    if (key !== 0) {
        let filtersData = getCookie('filtersData'+key.toString()) || {};
        console.log(decodeURIComponent(filtersData.toString()));
        console.log(key);
        data = JSON.parse(decrypt(decodeURIComponent(filtersData.toString()), key % 10));
        console.log(key);
        console.log(data);
    }
    if (Object.keys(data).length > 0) {
        renderFilters(data);
    } else {
        renderFilters({});
    }
});


function renderFilters(filtersData) {
    const filtersContainer = document.getElementById('filters');


    Object.values(filtersData).forEach((item) => {
        let form = document.createElement('form');
        form.setAttribute('method','post');
        form.setAttribute('action',"/search/delete")
        form.textContent = `${item.column}: ${item.value}`;

        let input = document.createElement('input');
        input.setAttribute('type','hidden');
        input.setAttribute('value',item.column);
        input.setAttribute('name','delete-column');
        form.appendChild(input);

        let btn = document.createElement('button');
        btn.classList.add('button-delete-filter');
        btn.setAttribute('type','submit');


        const icon = document.createElement('i');
        icon.classList.add('fa-solid', 'fa-xmark');
        btn.appendChild(icon);

        form.appendChild(btn);

        const resultContainer = document.createElement('div');
        resultContainer.classList.add('filter');
        resultContainer.appendChild(form);
        filtersContainer.appendChild(resultContainer);
    });

}

function decodeBase64(encodedString) {
    return atob(encodedString);
}

function getCookie(name) {
    let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    console.log(document.cookie);
    if (match) return match[2];
    return null;
}

function decrypt(text, shift) {
    shift = 26 - shift;
    let result = "";

    for (let i = 0; i < text.length; i++) {
        let char = text[i];

        if (/[a-zA-Z]/.test(char)) {
            let ascii = char.charCodeAt(0);
            let base = (char.toLowerCase() === char) ? 97 : 65;
            result += String.fromCharCode((ascii - base + shift) % 26 + base);
        } else {
            result += char;
        }
    }

    return atob(result);
}






