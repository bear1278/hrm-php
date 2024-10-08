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
        <form class="register-form-container" action='/resume' method='POST'>
            <h1 class="form-title">
                Резюме
            </h1>
            <div class="form-fields">
                <div class="form-field">
                    <input type="tel" placeholder="Phone number" name="phone_number" pattern="+[0-9]{3}-[0-9]{2}-[0-9]{3}-[0-9]{2}-[0-9]{2}>
                </div>
                <div class="form-field">
                    <input type="text" placeholder="Resume" name="resume">
                </div>
                <div class="form-field">
                    <input type="number" placeholder="Experience" name="experience_years" min="0">
                </div>
                <div class="form-field">
                    <input type="text" placeholder="Location" name="location">
                </div>
                
                <div class="form-buttons">
                    <button class="button" type="submit">Отправить</button>
                </div>
            </div>
        </form>
    </main>
    <script type="module" src="/js/sign-up.js"></script>
</body>
</html>