<?php
session_start();

// Simple hardcoded credentials - change these!
$valid_username = 'admin';
$valid_password = 'ChangeThisPassword123';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #f5f5f5; }
        .login-box { background: white; border: 1px solid #e0e0e0; }
        input { border: 1px solid #d4d4d4; padding: 0.5rem; width: 100%; }
        input:focus { border-color: #000; outline: none; }
        button { background: #2d2d2d; color: white; padding: 0.5rem 1rem; width: 100%; }
        button:hover { background: #000; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="login-box p-8 rounded w-96">
        <h1 class="text-2xl font-light mb-6">Admin Access</h1>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 mb-4"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>