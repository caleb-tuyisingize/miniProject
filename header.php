<?php
// header.php - Includes the start of the HTML and the navigation bar
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Manager | <?= $page_title ?? "Home" ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Optional custom styles */
        .container { max-width: 1200px; }
    </style>
</head>
<body class="bg-green-400 min-h-screen">
    <nav class="bg-blue-600 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-extrabold tracking-tight">📚 Library Manager</a>
            
            <div class="flex space-x-4 items-center">
                <?php if ($is_logged_in): ?>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin_dashboard.php" class="hover:text-blue-200">Dashboard</a>
                        <a href="books_list.php" class="hover:text-blue-200">Manage Books</a>
                        <a href="users_list.php" class="hover:text-blue-200">Manage Users</a>
                        <a href="borrowings.php" class="hover:text-blue-200">Borrowings</a>
                    <?php else: ?>
                    <a href="index.php" class="hover:text-blue-200">Home</a>
                    <?php endif; ?>

                    <span class="text-sm border-l border-blue-400 pl-4">Hello, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                    <a href="logout.php" class="bg-red-500 px-3 py-1 rounded hover:bg-red-600">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="bg-green-500 px-3 py-1 rounded hover:bg-green-600">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container mx-auto mt-8 p-4">