<?php
// logout.php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session (required)
session_destroy();

// Redirect to the login page
header("Location: login.php");
exit();
?>