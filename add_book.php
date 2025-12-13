// add_book.php

require_once 'db_config.php';
session_start();
// Include 'auth.php' or similar check to ensure only 'admin' can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Access Denied");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $quantity = trim($_POST['quantity']);

    // --- 2. PHP Form Data Validation ---

    if (empty($title)) { $errors[] = "Title is required."; }
    if (empty($author)) { $errors[] = "Author is required."; }
    if (empty($isbn)) { $errors[] = "ISBN is required."; }
    if (!is_numeric($quantity) || $quantity < 1) { $errors[] = "Quantity must be a number greater than 0."; }
    
    // Additional validation for ISBN format (simple example)
    if (!preg_match('/^[0-9-]{10,17}$/', $isbn)) { $errors[] = "ISBN is not valid."; }

    if (empty($errors)) {
        try {
            // --- 1. PDO with Prepared Statements (Positional Placeholders) ---
            $sql = "INSERT INTO books (title, author, isbn, quantity) VALUES (?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            
            // --- Using bindParam() ---
            // Note: bindParam binds the variable, bindValue binds the value at time of execution
            // We use bindValue for direct values here for simplicity
            $stmt->bindValue(1, $title, PDO::PARAM_STR);
            $stmt->bindValue(2, $author, PDO::PARAM_STR);
            $stmt->bindValue(3, $isbn, PDO::PARAM_STR);
            $stmt->bindValue(4, $quantity, PDO::PARAM_INT);

            $stmt->execute();
            $success = "Book '{$title}' added successfully!";
        
        } catch (\PDOException $e) {
            // --- 5. Exception Handling ---
            if ($e->getCode() == 23000) { // 23000 is for integrity constraint violation (e.g., duplicate unique key)
                $errors[] = "A book with that ISBN already exists.";
            } else {
                // Generic error for other DB issues
                $errors[] = "Database error: Could not add the book. Please try again.";
                // You should log $e->getMessage() for debugging.
            }
        }
    }
}
// HTML form follows, showing $errors or $success messages...
?>