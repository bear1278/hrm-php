<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/loginForm.css">
    <title>Auth</title>
</head>
<body>
<header>
    <main>
        <div class="circle"></div>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <!-- Added action and method to the form -->
        <form class="register-form-container" action="/login" method="POST">
            <h1 class="form-title">
                Вход
            </h1>
            <div class="form-fields">
                <div class="form-field">
                    <!-- Added 'required' for validation -->
                    <input type="text" placeholder="Username" name="username" required>
                </div>
                <div class="form-field">
                    <!-- Added 'required' for validation -->
                    <input type="password" placeholder="Password" name="password" required>
                </div>
                <div class="form-buttons">
                    <button class="button" type="submit">Войти</button>
                    <div class="divider">OR</div>
                    <a style="text-decoration: none" href="http://localhost/signup" class="button button-signup">Регистрация</a>
                </div>
            </div>
        </form>
    </main>
</header>
<script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault();  // Prevent the form from refreshing the page

            // Get form data
            const formData = new FormData(this);

            // Send AJAX request using Fetch API
            fetch('/login', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())  // Parse the response
            .then(data => {
                // Display the response or error
                document.getElementById('result').innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('result').innerHTML = 'An error occurred';
            });
        });
    </script>
</body>
</html>
