<?php
// borrowings.php - View Outstanding Loans and Process Returns (READ & UPDATE)
require_once 'db_config.php';
require_once 'auth.php'; 

if (!isAdmin()) {
    header("Location: index.php?error=Forbidden Access");
    exit();
}

$success = "";
$error = "";

// --- Handle Return Action (UPDATE Operation) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'return_book') {
    $borrow_id = $_POST['borrow_id'];
    $book_id = $_POST['book_id'];

    try {
        // --- Use PDO Transaction for Return Process ---
        $pdo->beginTransaction();

        // 1. Update the borrowings record (Set return_date)
        $sql_return = "UPDATE borrowings SET return_date = CURDATE() WHERE borrow_id = ?";
        $stmt_return = $pdo->prepare($sql_return);
        $stmt_return->execute([$borrow_id]);

        // 2. Increment the book quantity (Update inventory)
        $sql_increment = "UPDATE books SET quantity = quantity + 1 WHERE book_id = ?";
        $stmt_increment = $pdo->prepare($sql_increment);
        $stmt_increment->execute([$book_id]);

        $pdo->commit();
        $success = "Book successfully returned and inventory updated!";

    } catch (\PDOException $e) {
        $pdo->rollBack();
        $error = "Return failed (Database Error): " . $e->getMessage();
    }
}


// --- Fetch All Outstanding Loans (READ Operation) ---
$outstanding_loans = [];
try {
    $sql_loans = "
        SELECT 
            b.borrow_id, 
            b.book_id,
            u.username, 
            bo.title, 
            bo.isbn,
            b.borrow_date, 
            b.due_date,
            DATEDIFF(b.due_date, CURDATE()) AS days_left
        FROM borrowings b
        JOIN users u ON b.user_id = u.user_id
        JOIN books bo ON b.book_id = bo.book_id
        WHERE b.return_date IS NULL
        ORDER BY b.due_date ASC";

    $stmt_loans = $pdo->query($sql_loans);
    $outstanding_loans = $stmt_loans->fetchAll();

} catch (\PDOException $e) {
    $error = "Error fetching outstanding loans: " . $e->getMessage();
}

$page_title = "Manage Borrowings";
include 'header.php';
?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Outstanding Book Loans</h1>

<div class="flex justify-between mb-4">
    <a href="borrow_new.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        + Record New Loan
    </a>
</div>

<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Borrower</th>
                <th class="py-3 px-6 text-left">Book Title / ISBN</th>
                <th class="py-3 px-6 text-center">Date Borrowed</th>
                <th class="py-3 px-6 text-center">Due Date</th>
                <th class="py-3 px-6 text-center">Status</th>
                <th class="py-3 px-6 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php if (empty($outstanding_loans)): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-100"><td colspan="6" class="py-3 px-6 text-center">No outstanding loans currently.</td></tr>
            <?php endif; ?>
            <?php foreach ($outstanding_loans as $loan): 
                $days_status = $loan['days_left'];
                $status_color = 'bg-green-200 text-green-700';
                $status_text = 'Due in ' . $days_status . ' days';
                if ($days_status < 0) {
                    $status_color = 'bg-red-200 text-red-700 font-bold';
                    $status_text = 'OVERDUE by ' . abs($days_status) . ' days';
                } elseif ($days_status <= 3) {
                    $status_color = 'bg-yellow-200 text-yellow-700 font-bold';
                    $status_text = 'Due Soon (' . $days_status . ' days)';
                }
            ?>
            <tr class="border-b border-gray-200 hover:bg-gray-100">
                <td class="py-3 px-6 text-left"><?= htmlspecialchars($loan['username']) ?></td>
                <td class="py-3 px-6 text-left">
                    <strong class="text-gray-800"><?= htmlspecialchars($loan['title']) ?></strong>
                    <br><span class="text-xs italic"><?= htmlspecialchars($loan['isbn']) ?></span>
                </td>
                <td class="py-3 px-6 text-center"><?= htmlspecialchars($loan['borrow_date']) ?></td>
                <td class="py-3 px-6 text-center"><?= htmlspecialchars($loan['due_date']) ?></td>
                <td class="py-3 px-6 text-center">
                    <span class="p-1 rounded-full text-xs <?= $status_color ?>"><?= $status_text ?></span>
                </td>
                <td class="py-3 px-6 text-center">
                    <form method="POST" action="borrowings.php" onsubmit="return confirm('Confirm return of <?= htmlspecialchars($loan['title']) ?>?');" class="inline">
                        <input type="hidden" name="action" value="return_book">
                        <input type="hidden" name="borrow_id" value="<?= $loan['borrow_id'] ?>">
                        <input type="hidden" name="book_id" value="<?= $loan['book_id'] ?>">
                        <button type="submit" class="bg-green-500 text-white p-2 rounded hover:bg-green-600 text-xs font-bold">Process Return</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php 
echo '</main></body></html>'; 
?>