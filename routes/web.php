session_start();  // Start the session

$loginController = new LoginController();

// Handle GET request to show login form
if ($_SERVER['REQUEST_URI'] == '/login' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if the user is already logged in
    if (isset($_SESSION['user_id'])) {
        // If logged in, redirect to dashboard to avoid looping back to login
        header('Location: /dashboard');
        exit();
    }

    // Show login form if not logged in
    $loginController->showLoginForm();
} elseif ($_SERVER['REQUEST_URI'] == '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle the login form submission
    $loginController->login();
} elseif ($_SERVER['REQUEST_URI'] == '/dashboard') {
    // Check if the user is logged in before allowing access to the dashboard
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');  // Redirect to login if not authenticated
        exit();
    }

    // Load dashboard view
    require_once __DIR__ . '/../app/Views/dashboard.php';
} else {
    // Default 404 error for undefined routes
    http_response_code(404);
    echo "404 - Page Not Found";
}
