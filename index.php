<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Book</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="Screenshot 2025-12-14 122145.png">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between">
            <a href="index.php" class="text-xl font-bold">Library Manager</a>
            <div>
           <?php
// index.php - Main Landing Page / User Dashboard
require_once 'db_config.php';
// We don't require 'auth.php' here because this page is accessible to guests and users.
// We just need the session_start() and checks handled in header.php.

$page_title = "Home";
include 'header.php'; // Includes session_start() and checks if $_SESSION['user_id'] is set
?>

<div class="text-center py-12 bg-white rounded-lg shadow-lg">
    <?php if (isset($_SESSION['user_id'])): ?>
        
        <h1 class="text-4xl font-extrabold text-blue-700 mb-4">
            Welcome Back, <?= htmlspecialchars($_SESSION['username']) ?>!
        </h1>
        <p class="text-xl text-gray-600 mb-8">
            This is your personal library dashboard.
        </p>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <p class="text-red-500 font-semibold">
                You are logged in as an Administrator. <br>
                Please proceed to the <a href="admin_dashboard.php" class="text-blue-500 hover:underline">Admin Dashboard</a> to manage inventory.
            </p>
        <?php else: ?>
            <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <div class="p-6 border border-gray-200 rounded-lg hover:shadow-xl transition duration-300">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-3">Your Loans</h2>
                    <p class="text-gray-600 mb-4">View all books you currently have borrowed and their due dates.</p>
                    <a href="borrowings.php?user=<?= $_SESSION['user_id'] ?>" class="text-blue-500 hover:text-blue-700 font-bold">
                        Go to My Borrowings →
                    </a>
                </div>
                <div class="p-6 border border-gray-200 rounded-lg hover:shadow-xl transition duration-300">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-3">Search Books</h2>
                    <p class="text-gray-600 mb-4">Browse the entire library catalog and check current availability.</p>
                    <a href="books_list_public.php" class="text-blue-500 hover:text-blue-700 font-bold">
                        Browse Catalog →
                    </a>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>

        <h1 class="text-5xl font-extrabold text-blue-900 mb-4">
            Welcome to the Digital Library System
        </h1>
        <p class="text-xl text-gray-700 mb-8">
            Your centralized platform for book inventory and loan management.
        </p>
        <div class="mt-10">
            <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold py-3 px-8 rounded-lg shadow-lg transition duration-300">
                Log In to Access Your Account
            </a>
            <p class="mt-4 text-gray-500">
                Admins use your dedicated credentials.
            </p>
        </div>
    
    <?php endif; ?>
</div>

<?php 
// Close the HTML tags
echo '</main></body></html>'; 
?>     <a href="books_list.php" class="mx-2 hover:text-blue-200">Manage Books</a>
                <a href="borrowings.php" class="mx-2 hover:text-blue-200">Borrowings</a>
            </div>
        </div>
    </nav>
    <main class="container mx-auto mt-8 p-4">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Add New Book</h1>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Validation Errors:</strong>
                <ul class="list-disc ml-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-bold mb-2">Title</label>
                <input type="text" id="title" name="title" required 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="mb-4">
                <label for="author" class="block text-gray-700 font-bold mb-2">Author</label>
                <input type="text" id="author" name="author" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="mb-4">
                <label for="isbn" class="block text-gray-700 font-bold mb-2">ISBN</label>
                <input type="text" id="isbn" name="isbn" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label for="quantity" class="block text-gray-700 font-bold mb-2">Quantity</label>
                <input type="number" id="quantity" name="quantity" required min="1" value="1"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Book
                </button>
            </div>
        </form>
    </main>
</body>
</html>