<?php
// book_delete.php - Handles deletion of a book record
require_once 'db_config.php';
require_once 'auth.php';

// Role check for admin access
if (!isAdmin()) {
    header("Location: index.php?error=Forbidden Access");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['book_id'])) {
    header("Location: books_list.php?error=Invalid action.");
    exit();
}

$book_id = $_POST['book_id'];

try {
    // --- PDO Prepared Statement for DELETE ---
    // Use a transaction here for safety if you had related inventory/log tables, but simple delete for now.
    $sql = "DELETE FROM books WHERE book_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Check if any row was affected
    if ($stmt->rowCount() > 0) {
        header("Location: books_list.php?success=Book deleted successfully.");
    } else {
        header("Location: books_list.php?error=Book not found or could not be deleted.");
    }
    exit();

} catch (\PDOException $e) {
    // If the book is currently borrowed (Foreign Key Constraint violation)
    if ($e->getCode() == 23000) {
        header("Location: books_list.php?error=Cannot delete book: it is currently involved in a borrowing transaction. All loans must be returned first.");
    } else {
        header("Location: books_list.php?error=Database error during deletion.");
    }
    exit();
}
?>