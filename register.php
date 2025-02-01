<?php
include 'storage.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($fullName)) {
        $errors[] = 'Full Name is required.';
    }
    if (empty($email)) {
        $errors[] = 'Email Address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid Email Address.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    $userStorage = new Storage(new JsonIO('users.json'));
    $users = $userStorage->findAll();
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            $errors[] = 'Email Address is already registered.';
            break;
        }
    }

    if (empty($errors)) {
        $newUser = [
            'id' => uniqid(),
            'full_name' => $fullName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'is_admin' => 0 // Default to non-admin
        ];
        $users[] = $newUser;
        $userStorage->add($newUser);
        header('Location: login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav>
    <div class="nav-left"><a href="index.php">iKarRental</a></div>
        <div class="nav-right">
        </div>
    </nav>

    <div class="registration-container">
        <h1>Register</h1>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="register.php" method="post">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn-yellow">Register</button>
        </form>
    </div>
</body>
</html>