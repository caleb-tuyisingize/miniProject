<?php
// users_list.php - View/Read all users (Admin only)
require_once 'db_config.php';
require_once 'auth.php'; 

// Role check for admin access
if (!isAdmin()) {
    header("Location: index.php?error=Forbidden Access");
    exit();
}

$users = [];
$error_message = '';

try {
    // PDO Prepared Statement for selecting all users
    $stmt = $pdo->prepare("SELECT user_id, username, email, role FROM users ORDER BY username ASC");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_message = "Could not load users: " . $e->getMessage();
}

$page_title = "Manage Users";
include 'header.php';
?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">User Account Management</h1>

<div class="flex justify-end mb-4">
    <a href="user_add.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
        + Add New User
    </a>
</div>

<?php if (isset($error_message) && $error_message): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">ID</th>
                <th class="py-3 px-6 text-left">Username</th>
                <th class="py-3 px-6 text-left">Email</th>
                <th class="py-3 px-6 text-center">Role</th>
                <th class="py-3 px-6 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php if (empty($users)): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-100"><td colspan="5" class="py-3 px-6 text-center">No users found in the system.</td></tr>
            <?php endif; ?>
            <?php foreach ($users as $user): ?>
            <tr class="border-b border-gray-200 hover:bg-gray-100">
                <td class="py-3 px-6 text-left whitespace-nowrap"><?= $user['user_id'] ?></td>
                <td class="py-3 px-6 text-left"><?= htmlspecialchars($user['username']) ?></td>
                <td class="py-3 px-6 text-left"><?= htmlspecialchars($user['email']) ?></td>
                <td class="py-3 px-6 text-center">
                    <span class="p-1 rounded-full text-xs font-semibold <?= $user['role'] === 'admin' ? 'bg-red-200 text-red-700' : 'bg-blue-200 text-blue-700' ?>">
                        <?= ucfirst($user['role']) ?>
                    </span>
                </td>
                <td class="py-3 px-6 text-center">
                    <div class="flex item-center justify-center space-x-2">
                        <a href="user_edit.php?id=<?= $user['user_id'] ?>" class="bg-yellow-500 text-white p-2 rounded hover:bg-yellow-600 text-xs">Edit</a>
                        
                        <form method="POST" action="user_delete.php" onsubmit="return confirm('WARNING: Are you sure you want to delete user <?= htmlspecialchars($user['username']) ?>? This action cannot be undone.');" class="inline">
                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                <button type="submit" class="bg-red-500 text-white p-2 rounded hover:bg-red-600 text-xs">Delete</button>
                            <?php else: ?>
                                <span class="text-xs text-gray-400 p-2 border rounded">Self</span>
                            <?php endif; ?>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php 
// Close the HTML tags
echo '</main></body></html>'; 
?>