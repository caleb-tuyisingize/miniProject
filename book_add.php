<?php
// book_add.php - Add/Create a new book
require_once 'db_config.php';
require_once 'auth.php';

// Role check for admin access
if (!isAdmin()) {
    header("Location: index.php?error=Forbidden Access");
    exit();
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $quantity = trim($_POST['quantity']);

    // --- 2. PHP Form Data Validation ---
    if (empty($title)) { $errors[] = "Title is required."; }
    if (empty($author)) { $errors[] = "Author is required."; }
    if (empty($isbn)) { $errors[] = "ISBN is required."; }
    // Check if quantity is a positive integer
    if (!filter_var($quantity, FILTER_VALIDATE_INT) || $quantity < 1) { 
        $errors[] = "Quantity must be a positive whole number."; 
    }
    
    // Simple ISBN format check (10 or 13 digits, allowing dashes)
    if (!preg_match('/^[0-9-]{10,17}$/', $isbn)) { $errors[] = "ISBN format is invalid."; }

    if (empty($errors)) {
        try {
            // --- 1. PDO with Prepared Statements (Positional Placeholders) ---
            $sql = "INSERT INTO books (title, author, isbn, quantity) VALUES (?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            
            // --- Using bindValue() ---
            $stmt->bindValue(1, $title, PDO::PARAM_STR);
            $stmt->bindValue(2, $author, PDO::PARAM_STR);
            $stmt->bindValue(3, $isbn, PDO::PARAM_STR);
            $stmt->bindValue(4, $quantity, PDO::PARAM_INT);

            $stmt->execute();
            $success = "Book '{$title}' added successfully!";
            // Clear fields after success
            $title = $author = $isbn = $quantity = '';
        
        } catch (\PDOException $e) {
            // --- 5. Exception Handling ---
            // Check for specific duplicate entry error (e.g., duplicate ISBN)
            if ($e->getCode() == 23000) { 
                $errors[] = "A book with that ISBN already exists.";
            } else {
                $errors[] = "Database error: Could not add the book. Please check the server logs.";
            }
        }
    }
}

$page_title = "Add Book";
include 'header.php';
?>

<div class="flex justify-center items-center">
    <div class="w-full max-w-lg">
        <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">Add New Book</h1>

        <?php if ($success): ?>
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
                <input type="text" id="title" name="title" required value="<?= htmlspecialchars($title ?? '') ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="mb-4">
                <label for="author" class="block text-gray-700 font-bold mb-2">Author</label>
                <input type="text" id="author" name="author" required value="<?= htmlspecialchars($author ?? '') ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="mb-4">
                <label for="isbn" class="block text-gray-700 font-bold mb-2">ISBN</label>
                <input type="text" id="isbn" name="isbn" required value="<?= htmlspecialchars($isbn ?? '') ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label for="quantity" class="block text-gray-700 font-bold mb-2">Quantity</label>
                <input type="number" id="quantity" name="quantity" required min="1" value="<?= htmlspecialchars($quantity ?? 1) ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Book
                </button>
                <a href="books_list.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
// Close the HTML tags (simple footer)
echo '</main></body></html>'; 
?>