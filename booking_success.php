<?php
session_start();
include 'storage.php';

$carStorage = new Storage(new JsonIO('cars.json'));
$car = $carStorage->findById($_GET['car_id'] ?? '');

if (!$car) {
    die("Car not found.");
}

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Successful</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<nav>
    <div class="nav-left"><a href="index.php">iKarRental</a></div>
    <div class="nav-right">
        <?php if (isset($_SESSION['user'])): ?>
            <a href="profile.php" class="btn-link">
                <img src="<?= htmlspecialchars($_SESSION['user']['profile_picture'] ?? 'default.png') ?>" alt="Profile Picture" class="profile-picture">
            </a>
            <a href="logout.php" class="btn-link">Logout</a>
            <?php if ($_SESSION['user']['is_admin'] === 1): ?>
                <a href="add_car.php" class="btn-link">Add Car</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php" class="btn-link">Login</a>
            <a href="register.php" class="btn-yellow">Registration</a>
        <?php endif; ?>
    </div>
</nav>

    <div class="message-container">
        <img src="success.png" alt="Success" width="200" height="200">
        <h1>Successful Booking!</h1>
        <p>The <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> has been successfully booked for the interval <?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?>.</p>
        <p>You can track the status of your reservation on your profile page.</p>
        <a href="profile.php" class="btn-yellow">My Profile</a>
    </div>
</body>
</html>