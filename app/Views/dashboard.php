<?php
// Start the session to access session variables

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page
    header('Location: /login');
    exit();
}

// You can access session variables like the username or user ID
$username = $_SESSION['user_id'];  // Assuming 'user_id' contains the username
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="/css/dashboard.css"> <!-- Optional CSS -->
</head>
<body>
    <header>
        <h1>Welcome to Your Dashboard, <?php echo htmlspecialchars($username); ?>!</h1>
    </header>
    <main>
        <p>This is your personalized dashboard where you can view and manage your account.</p>
        <!-- Add dashboard functionality here -->
        <div class="dashboard-actions">
            <ul>
                <li><a href="/profile">View Profile</a></li>
                <li><a href="/settings">Account Settings</a></li>
                <!-- Add more dashboard options here -->
            </ul>
        </div>

        <!-- Logout Button -->
        <form action="/logout" method="POST">
            <button type="submit">Logout</button>
        </form>
    </main>
</body>
</html>
