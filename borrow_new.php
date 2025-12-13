<?php
// borrow_new.php - Initiate a new borrowing transaction (CREATE)
require_once 'db_config.php';
require_once 'auth.php'; 

if (!isAdmin()) {
    header("Location: index.php?error=Forbidden Access");
    exit();
}

$errors = [];
$success = "";
$users = [];
$books = [];
$selected_user_id = '';
$selected_book_id = '';

// --- Fetch necessary data: Users and Available Books ---
try {
    // Fetch all users
    $stmt_users = $pdo->query("SELECT user_id, username FROM users ORDER BY username ASC");
    $users = $stmt_users->fetchAll();

    // Fetch books that have at least 1 copy available (quantity > 0)
    $stmt_books = $pdo->query("SELECT book_id, title, author, quantity FROM books WHERE quantity > 0 ORDER BY title ASC");
    $books = $stmt_books->fetchAll();
} catch (\PDOException $e) {
    $errors[] = "Error loading users or books: " . $e->getMessage();
}


// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $selected_user_id = $_POST['user_id'];
    $selected_book_id = $_POST['book_id'];
    $due_days = 14; // Default loan period: 14 days

    // Validation
    if (!is_numeric($selected_user_id) || !is_numeric($selected_book_id)) {
        $errors[] = "Invalid User or Book selected.";
    }
    
    if (empty($errors)) {
        try {
            // --- CRITICAL: Use PDO Transaction for Data Integrity (Atomicity) ---
            $pdo->beginTransaction();

            // 1. Check book availability just before borrowing
            $stmt_check = $pdo->prepare("SELECT quantity FROM books WHERE book_id = ? AND quantity > 0");
            $stmt_check->execute([$selected_book_id]);
            if (!$stmt_check->fetch()) {
                $errors[] = "Selected book is out of stock.";
                $pdo->rollBack();
            } else {
                
                // 2. Insert into borrowings table
                $sql_borrow = "INSERT INTO borrowings (book_id, user_id, borrow_date, due_date) 
                               VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY))";
                $stmt_borrow = $pdo->prepare($sql_borrow);
                $stmt_borrow->execute([$selected_book_id, $selected_user_id, $due_days]);

                // 3. Decrement quantity in books table
                $sql_decrement = "UPDATE books SET quantity = quantity - 1 WHERE book_id = ?";
                $stmt_decrement = $pdo->prepare($sql_decrement);
                $stmt_decrement->execute([$selected_book_id]);

                // If both queries succeed, commit the transaction
                $pdo->commit();
                $success = "Loan recorded successfully. Book is due in $due_days days.";
                // Reset selected IDs
                $selected_user_id = $selected_book_id = '';
            }

        } catch (\PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Transaction failed (Database Error): " . $e->getMessage();
            // In a production environment, log $e->getMessage() 
        }
    }
}

$page_title = "New Loan";
include 'header.php';
?>

<div class="flex justify-center items-center">
    <div class="w-full max-w-xl">
        <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">Initiate New Book Loan</h1>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <strong class="font-bold">Errors:</strong>
                <ul class="list-disc ml-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
            
            <div class="mb-4">
                <label for="user_id" class="block text-gray-700 font-bold mb-2">Borrower</label>
                <select id="user_id" name="user_id" required 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Select User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['user_id'] ?>" <?= $selected_user_id == $user['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-6">
                <label for="book_id" class="block text-gray-700 font-bold mb-2">Book to Borrow</label>
                <select id="book_id" name="book_id" required 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Select Book (Copies Available)</option>
                    <?php foreach ($books as $book): ?>
                        <option value="<?= $book['book_id'] ?>" <?= $selected_book_id == $book['book_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($book['title']) ?> by <?= htmlspecialchars($book['author']) ?> (<?= $book['quantity'] ?> in stock)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Record Loan
                </button>
                <a href="borrowings.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Cancel and Back to List
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
echo '</main></body></html>'; 
?>