<?php
// books_list_public.php - View/Read all books (Accessible to all)
require_once 'db_config.php';
// IMPORTANT: We do NOT include auth.php here, as this page is public/guest-friendly.

session_start();
$is_logged_in = isset($_SESSION['user_id']);

$books = [];
$error_message = '';

try {
    // Select all books, ordered by title
    $stmt = $pdo->prepare("SELECT book_id, title, author, isbn, quantity FROM books ORDER BY title ASC");
    $stmt->execute();
    $books = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_message = "Could not load the catalog: " . $e->getMessage();
}

$page_title = "Library Catalog";
include 'header.php'; 
?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Library Catalog</h1>
<p class="text-gray-600 mb-8">
    Browse the available books in our collection. If you are logged in, you may be able to check out a book through the librarian.
</p>

<?php if (isset($error_message) && $error_message): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Title</th>
                <th class="py-3 px-6 text-left">Author</th>
                <th class="py-3 px-6 text-center">ISBN</th>
                <th class="py-3 px-6 text-center">Availability</th>
                <th class="py-3 px-6 text-center"><?= $is_logged_in ? 'Action' : 'Status' ?></th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php if (empty($books)): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-100"><td colspan="5" class="py-3 px-6 text-center">No books found in the inventory.</td></tr>
            <?php endif; ?>
            
            <?php foreach ($books as $book): 
                $quantity = (int)$book['quantity'];
                if ($quantity > 0) {
                    $status_color = 'bg-green-100 text-green-700';
                    $status_text = 'In Stock (' . $quantity . ')';
                } else {
                    $status_color = 'bg-red-100 text-red-700';
                    $status_text = 'Out of Stock';
                }
            ?>
            <tr class="border-b border-gray-200 hover:bg-gray-100">
                <td class="py-3 px-6 text-left whitespace-nowrap font-semibold"><?= htmlspecialchars($book['title']) ?></td>
                <td class="py-3 px-6 text-left"><?= htmlspecialchars($book['author']) ?></td>
                <td class="py-3 px-6 text-center"><?= htmlspecialchars($book['isbn']) ?></td>
                <td class="py-3 px-6 text-center">
                    <span class="p-1 rounded-full text-xs font-semibold <?= $status_color ?>">
                        <?= $status_text ?>
                    </span>
                </td>
                <td class="py-3 px-6 text-center">
                    <?php if ($is_logged_in): ?>
                        <?php if ($quantity > 0): ?>
                             <span class="text-xs text-blue-500">Ask Admin to Loan</span>
                        <?php else: ?>
                            <span class="text-xs text-gray-400">Unavailable</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="text-xs text-purple-600 hover:underline">Login to Check Out</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php 
echo '</main></body></html>'; 
?>