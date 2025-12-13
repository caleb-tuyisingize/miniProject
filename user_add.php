<?php
// user_add.php - Add/Create a new user account (Admin only)
require_once 'db_config.php';
require_once 'auth.php';

// Role check for admin access
if (!isAdmin()) {
    header("Location: index.php?error=Forbidden Access");
    exit();
}

$errors = [];
$success = "";
$username = $email = $role = ''; // Initialize variables

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password']; 
    $email = trim($_POST['email']);
    $role = $_POST['role'] ?? 'user';

    // --- 1. PHP Form Data Validation ---
    if (empty($username)) { $errors[] = "Username is required."; }
    if (empty($password)) { $errors[] = "Password is required."; }
    if (empty($email)) { $errors[] = "Email is required."; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Email format is invalid."; }
    if (!in_array($role, ['user', 'admin'])) { $errors[] = "Invalid role selected."; }
    if (strlen($password) < 6) { $errors[] = "Password must be at least 6 characters long."; }

    if (empty($errors)) {
        try {
            // --- 2. Securely Hash the Password ---
            // This is critical for security: NEVER store raw passwords.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // --- 3. PDO Prepared Statement for Insertion ---
            $sql = "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            
            // Bind parameters
            $stmt->bindValue(1, $username, PDO::PARAM_STR);
            $stmt->bindValue(2, $hashed_password, PDO::PARAM_STR);
            $stmt->bindValue(3, $email, PDO::PARAM_STR);
            $stmt->bindValue(4, $role, PDO::PARAM_STR);

            $stmt->execute();
            $success = "User '{$username}' added successfully as a {$role}!";
            
            // Clear fields after success (except $role)
            $username = $email = '';
            
        } catch (\PDOException $e) {
            // --- 4. Exception Handling (e.g., Duplicate Entry) ---
            // Code 23000 typically means integrity constraint violation (UNIQUE constraint on username/email)
            if ($e->getCode() == 23000) { 
                if (strpos($e->getMessage(), 'username')) {
                    $errors[] = "The username '{$username}' is already taken.";
                } elseif (strpos($e->getMessage(), 'email')) {
                    $errors[] = "The email '{$email}' is already in use.";
                } else {
                    $errors[] = "A user with that unique identifier already exists.";
                }
            } else {
                $errors[] = "Database error: Could not add the user. Please check the server logs.";
            }
        }
    }
}

$page_title = "Add New User";
include 'header.php';
?>

<div class="flex justify-center items-center">
    <div class="w-full max-w-lg">
        <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">Add New User Account</h1>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Validation/Error:</strong>
                <ul class="list-disc ml-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 font-bold mb-2">Username</label>
                <input type="text" id="username" name="username" required value="<?= htmlspecialchars($username) ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" id="password" name="password" required 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <p class="text-xs text-gray-500 mt-1">Must be at least 6 characters.</p>
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label for="role" class="block text-gray-700 font-bold mb-2">User Role</label>
                <select id="role" name="role" required 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="user" <?= $role == 'user' ? 'selected' : '' ?>>User (Borrower)</option>
                    <option value="admin" <?= $role == 'admin' ? 'selected' : '' ?>>Admin (Manager)</option>
                </select>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Create User
                </button>
                <a href="users_list.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Cancel and Back to List
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
echo '</main></body></html>'; 
?>
