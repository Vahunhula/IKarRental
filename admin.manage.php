<?php
include 'storage.php';

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] !== 1) {
    die("Access denied.");
}

$userStorage = new Storage(new JsonIO('users.json'));
$users = $userStorage->findAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $isAdmin = isset($_POST['is_admin']) ? 1 : 0;

    foreach ($users as &$user) {
        if ($user['id'] === $userId) {
            $user['is_admin'] = $isAdmin;
            $userStorage->update($userId, $user);
            break;
        }
    }
    header('Location: admin_manage.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav>
        <div class="nav-left">iKarRental</div>
        <div class="nav-right">
            <a href="index.php" class="btn-link">Home</a>
        </div>
    </nav>

    <div class="admin-container">
        <h1>Admin Management</h1>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Admin Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= $user['is_admin'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <form action="admin_manage.php" method="post">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                <input type="checkbox" name="is_admin" <?= $user['is_admin'] ? 'checked' : '' ?>>
                                <button type="submit" class="btn-yellow small-btn">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>