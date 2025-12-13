<?php
// auth.php - Checks if a user is logged in
session_start();

if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Optional: Function to check role if needed for specific page access
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Usage in admin pages:
// if (!isAdmin()) { header("Location: index.php?error=Forbidden"); exit(); }
?>