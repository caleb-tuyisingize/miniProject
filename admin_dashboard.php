<?php
// admin_dashboard.php - Admin Landing Page
require_once 'db_config.php';
require_once 'auth.php'; 

// Role check for admin access
if (!isAdmin()) {
    header("Location: index.php?error=Forbidden Access");
    exit();
}

$page_title = "Admin Dashboard";
include 'header.php';
?>

<h1 class="text-4xl font-extrabold text-gray-800 mb-6 border-b pb-2">
    Administrator Control Panel
</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    
    <a href="books_list.php" class="block bg-white p-6 rounded-lg shadow-xl hover:shadow-2xl transition duration-300 transform hover:scale-105">
        <h2 class="text-2xl font-bold text-blue-600 mb-3">Manage Books</h2>
        <p class="text-gray-600 mb-4">Add, view, edit, and delete book records in the inventory. (Full CRUD)</p>
        <div class="text-sm font-semibold text-blue-500">Go to Book Management →</div>
    </a>
    
    <a href="borrowings.php" class="block bg-white p-6 rounded-lg shadow-xl hover:shadow-2xl transition duration-300 transform hover:scale-105">
        <h2 class="text-2xl font-bold text-green-600 mb-3">Manage Borrowings</h2>
        <p class="text-gray-600 mb-4">Track outstanding loans, process returns, and manage due dates.</p>
        <div class="text-sm font-semibold text-green-500">Go to Borrowing Tracking →</div>
    </a>

    <a href="users_list.php" class="block bg-red-900 p-6 rounded-lg shadow-xl hover:shadow-2xl transition duration-300 transform hover:scale-105 opacity-50 cursor-pointer">
        <h2 class="text-2xl font-bold text-red-200 mb-3">Manage Users</h2>
        <p class="text-red-200 mb-4">View and manage library member accounts and roles. (Optional)</p>
         <div class="text-sm font-semibold text-green-500">Go to Manage users →</div>
    </a>

</div>

<?php 
// Close the HTML tags
echo '</main></body></html>'; 
?>