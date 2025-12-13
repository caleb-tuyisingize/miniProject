<?php
// books_list.php - View/Read all books (Admin only)
require_once 'db_config.php';
require_once 'auth.php'; 

// Role check for admin access
if (!isAdmin()) {
    header("Location: index.php?error=Forbidden Access");
    exit();
}

$books = [];
try {
    // PDO Prepared Statement for selecting all books
    $stmt = $pdo->prepare("SELECT * FROM books ORDER BY title ASC");
    $stmt->execute();
    $books = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_message = "Could not load books: " . $e->getMessage();
}

$page_title = "Manage Books";
include 'header.php';
?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Book Inventory Management</h1>

<div class="flex justify-end mb-4">
    <a href="book_add.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
        + Add New Book
    </a>
</div>

<?php if (isset($error_message)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><?= $error_message ?></div>
<?php endif; ?>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Title</th>
                <th class="py-3 px-6 text-left">Author</th>
                <th class="py-3 px-6 text-center">ISBN</th>
                <th class="py-3 px-6 text-center">Quantity</th>
                <th class="py-3 px-6 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php if (empty($books)): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-100"><td colspan="5" class="py-3 px-6 text-center">No books found in the inventory.</td></tr>
            <?php endif; ?>
            <?php foreach ($books as $book): ?>
            <tr class="border-b border-gray-200 hover:bg-gray-100">
                <td class="py-3 px-6 text-left whitespace-nowrap"><?= htmlspecialchars($book['title']) ?></td>
                <td class="py-3 px-6 text-left"><?= htmlspecialchars($book['author']) ?></td>
                <td class="py-3 px-6 text-center"><?= htmlspecialchars($book['isbn']) ?></td>
                <td class="py-3 px-6 text-center"><?= $book['quantity'] ?></td>
                <td class="py-3 px-6 text-center">
                    <div class="flex item-center justify-center space-x-2">
                        <a href="book_edit.php?id=<?= $book['book_id'] ?>" class="bg-yellow-500 text-white p-2 rounded hover:bg-yellow-600 text-xs">Edit</a>
                        <form method="POST" action="book_delete.php" onsubmit="return confirm('Are you sure you want to delete this book? This cannot be undone.');" class="inline">
                            <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
                            <button type="submit" class="bg-red-500 text-white p-2 rounded hover:bg-red-600 text-xs">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php 
// Close the HTML tags (simple footer)
echo '</main></body></html>'; 
?>