<?php
include 'storage.php';

session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email)) {
        $errors[] = 'Email Address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid Email Address.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $userStorage = new Storage(new JsonIO('users.json'));
        $users = $userStorage->findAll();
        $userFound = false;

        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                $userFound = true;
                $_SESSION['user'] = $user;
                header('Location: index.php');
                exit;
            }
        }

        if (!$userFound) {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<nav>
    <div class="nav-left"><a href="index.php">iKarRental</a></div>
    <div class="nav-right">
        <a href="index.php" class="btn-link">Home</a>
    </div>
</nav>

    <div class="login-container">
        <h1>Login</h1>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn-yellow">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php" class="btn-link">Register</a></p>
    </div>
</body>
</html>