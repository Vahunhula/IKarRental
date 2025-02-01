<?php
session_start();
include 'storage.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userStorage = new Storage(new JsonIO('users.json'));
$carStorage = new Storage(new JsonIO('cars.json'));
$bookingStorage = new Storage(new JsonIO('bookings.json'));

$user = $_SESSION['user'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking'])) {
    $bookingId = $_POST['booking_id'];
    $bookingStorage->delete($bookingId);
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_car'])) {
    $carId = $_POST['car_id'];
    $carStorage->delete($carId);
    header('Location: profile.php');
    exit;
}

$bookings = array_filter($bookingStorage->findAll(), function($booking) use ($user) {
    return $booking['user_id'] === $user['id'];
});

if ($user['is_admin'] === 1) {
    $bookings = $bookingStorage->findAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<nav>
    <div class="nav-left"><a href="index.php">iKarRental</a></div>
    <div class="nav-right">
        <a href="logout.php" class="btn-link">Logout</a>
    </div>
</nav>

<div class="profile-container">
    <h1>Profile</h1>
    <img src="<?= htmlspecialchars($user['profile_picture'] ?? 'default.png') ?>" alt="Profile Picture" class="profile-picture">
    <p>Full Name: <?= htmlspecialchars($user['full_name']) ?></p>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>

    <h2>Your Bookings</h2>
    <?php if (!empty($bookings)): ?>
        <ul>
            <?php foreach ($bookings as $booking): ?>
                <li>
                    Car ID: <?= htmlspecialchars($booking['car_id']) ?><br>
                    Start Date: <?= htmlspecialchars($booking['start_date']) ?><br>
                    End Date: <?= htmlspecialchars($booking['end_date']) ?><br>
                    Total Price: <?= number_format($booking['total_price']) ?> HUF
                    <?php if ($user['is_admin'] === 1): ?>
                        <form action="profile.php" method="post">
                            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id']) ?>">
                            <button type="submit" name="delete_booking" class="btn-yellow small-btn">Delete Booking</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have no bookings.</p>
    <?php endif; ?>
</div>
</body>
</html>