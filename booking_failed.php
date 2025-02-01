<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Failed</title>
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
        <img src="fail.png" alt="Fail" width="200" height="200">
        <h1>Booking Failed</h1>
        <p>The car is already booked for the selected period. Please choose a different period.</p>
        <a href="car_details.php?id=<?= htmlspecialchars($_GET['car_id']) ?>" class="btn-yellow">Back to Car Details</a>
    </div>
</body>
</html>