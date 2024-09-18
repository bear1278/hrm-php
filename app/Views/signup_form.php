<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/signUp.css">
    <title>Auth</title>
</head>
<body>
    <main>
        <div class="circle"></div>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form class="register-form-container" action='/signup' method='POST'>
            <h1 class="form-title">
                Регистрация
            </h1>
            <div class="form-fields">
                <div class="form-field">
                    <input type="text" placeholder="First name" name="firstname">
                </div>
                <div class="form-field">
                    <input type="text" placeholder="Last name" name="lastname">
                </div>
                <div class="form-field">
                    <input type="text" placeholder="Email" name="email">
                </div>
                <div class="form-field">
                    <input type="password" placeholder="Password" name="password">
                </div>
                <div class="form-field">
                    <input type="password" placeholder="Confirm password" name="confirmPassword">
                </div>
                <div class="form-buttons">
                    <button class="button" type="submit">Зарегистрироваться</button>
                    <div class="divider">Уже есть аккаунт?</div>
                    <a href="http://localhost/login" class="button button-signup">Войти</a>
                </div>
            </div>
        </form>
    </main>
    <script type="module" src="/js/sign-up.js"></script>
</body>
</html>