<?php
// book_edit.php - Edit/Update an existing book record
require_once 'db_config.php';
require_once 'auth.php';

// Role check for admin access
if (!isAdmin()) {
    header("Location: index.php?error=Forbidden Access");
    exit();
}

$errors = [];
$success = "";
$book = null; // Variable to hold current book data

// --- 1. Get Book ID and Fetch Existing Data (Read Operation) ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $book_id = $_GET['id'];
} elseif (isset($_POST['book_id']) && is_numeric($_POST['book_id'])) {
    // If the form was submitted, use the hidden ID
    $book_id = $_POST['book_id'];
} else {
    // No valid ID provided
    header("Location: books_list.php?error=No Book ID provided.");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = :id");
    $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);
    $stmt->execute();
    $book = $stmt->fetch();
    
    if (!$book) {
        $errors[] = "Book not found.";
        // Exit if book not found to prevent further processing
        $page_title = "Book Not Found";
        include 'header.php';
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">Book ID ' . htmlspecialchars($book_id) . ' does not exist.</div>';
        echo '</main></body></html>';
        exit();
    }
} catch (\PDOException $e) {
    $errors[] = "Database error while fetching book data.";
    // Log the error $e->getMessage()
}


// --- 2. Handle Form Submission (Update Operation) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $book) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    // Note: ISBN is generally not updated after creation
    $quantity = trim($_POST['quantity']);

    // --- Validation ---
    if (empty($title)) { $errors[] = "Title is required."; }
    if (empty($author)) { $errors[] = "Author is required."; }
    if (!filter_var($quantity, FILTER_VALIDATE_INT) || $quantity < 0) { 
        $errors[] = "Quantity must be zero or a positive whole number."; 
    }
    
    if (empty($errors)) {
        try {
            // --- PDO Prepared Statement for UPDATE ---
            $sql = "UPDATE books SET title = :title, author = :author, quantity = :quantity WHERE book_id = :id";
            
            $stmt = $pdo->prepare($sql);
            
            // Using bindValue() for all parameters
            $stmt->bindValue(':title', $title, PDO::PARAM_STR);
            $stmt->bindValue(':author', $author, PDO::PARAM_STR);
            $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindValue(':id', $book_id, PDO::PARAM_INT);

            $stmt->execute();
            $success = "Book '{$title}' updated successfully!";
            
            // Re-fetch the updated data to reflect changes in the form immediately
            $book['title'] = $title;
            $book['author'] = $author;
            $book['quantity'] = $quantity;
        
        } catch (\PDOException $e) {
             $errors[] = "Database error: Could not update the book. Please check the server logs.";
        }
    }
}

// --- 3. UI Display ---
$page_title = "Edit Book: " . htmlspecialchars($book['title'] ?? 'Loading...');
include 'header.php';
?>

<div class="flex justify-center items-center">
    <div class="w-full max-w-lg">
        <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">Edit Book: <?= htmlspecialchars($book['title'] ?? 'Error') ?></h1>

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
            <input type="hidden" name="book_id" value="<?= htmlspecialchars($book['book_id'] ?? '') ?>">

            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-bold mb-2">Title</label>
                <input type="text" id="title" name="title" required 
                       value="<?= htmlspecialchars($book['title'] ?? '') ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="mb-4">
                <label for="author" class="block text-gray-700 font-bold mb-2">Author</label>
                <input type="text" id="author" name="author" required 
                       value="<?= htmlspecialchars($book['author'] ?? '') ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="mb-4">
                <label for="isbn" class="block text-gray-700 font-bold mb-2">ISBN (Fixed)</label>
                <input type="text" id="isbn" name="isbn" readonly disabled
                       value="<?= htmlspecialchars($book['isbn'] ?? '') ?>"
                       class="shadow appearance-none border bg-gray-200 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label for="quantity" class="block text-gray-700 font-bold mb-2">Quantity</label>
                <input type="number" id="quantity" name="quantity" required min="0" 
                       value="<?= htmlspecialchars($book['quantity'] ?? 0) ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Save Changes
                </button>
                <a href="books_list.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Cancel and Back to List
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
echo '</main></body></html>'; 
?>