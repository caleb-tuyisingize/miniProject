<?php
// db_config.php

$host = 'localhost';
$db   = 'library_db';
$user = 'root';      
$pass = '';          
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Required Exception Handling
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Controlled error display
     die("
        <div style='border: 1px solid #d9534f; padding: 15px; background-color: #f2dede; color: #a94442; font-family: sans-serif;'>
            <h2>Database Connection Error</h2>
            <p><strong>Code:</strong> {$e->getCode()}</p>
            <p>Cannot connect to the library database. Please ensure MySQL is running and credentials in <code>db_config.php</code> are correct.</p>
        </div>
    ");
}
?>