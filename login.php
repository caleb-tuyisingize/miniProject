<?php
// login.php - Corrected and SECURE version
require_once 'db_config.php';

session_start();
$errors = [];

// If user is already logged in, redirect them
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 1. Validation
    if (empty($username) || empty($password)) {
        $errors[] = "Username and password are required.";
    }

    if (empty($errors)) {
        try {
            // 2. PDO Prepared Statement (Named Placeholder)
            // CRITICAL FIX: The query must use a placeholder (:username)
            $sql = "SELECT user_id, username, password, role FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            
            // Using bindValue() to safely bind the user input
            $stmt->bindValue(':username', $username, PDO::PARAM_STR); 
            $stmt->execute();
            $user = $stmt->fetch();

            // 3. Password Verification & Session Creation
            if ($user && password_verify($password, $user['password'])) {
                
                // Successful Login: Create Session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
                
            } else {
                $errors[] = "Invalid username or password.";
            }

        } catch (\PDOException $e) {
            $errors[] = "A database error occurred during login. Please try again.";
            // In a real application, log $e->getMessage() for debugging.
        }
    }
}
$page_title = "Login";
include 'header.php';
?>

<div class="flex justify-center items-center">
    <div class="w-full max-w-md">
        <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">System Login</h1>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Login Failed:</strong>
                <ul class="list-disc ml-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white shadow-lg rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                    Username
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" name="username" type="text" placeholder="Username" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Password
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="********" required>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Sign In
                </button>
                <a href='index.php'><label class="bg-green-900 cursor-pointer hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Return
                </label></a>
            </div>
        </form>
    </div>
</div>

<?php 
// Close the HTML tags (simple footer)
echo '</main></body></html>'; 
?>