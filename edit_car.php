<?php
session_start();
include 'storage.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] !== 1) {
    die("Access denied. Only admins can edit cars.");
}

$carStorage = new Storage(new JsonIO('cars.json'));
$carId = $_GET['id'] ?? '';
$car = $carStorage->findById($carId);

if (!$car) {
    die("Car not found.");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $year = (int)$_POST['year'];
    $fuel_type = trim($_POST['fuel_type']);
    $passengers = (int)$_POST['passengers'];
    $daily_price_huf = (int)$_POST['daily_price_huf'];
    $transmission = $_POST['gearType'];
    $image = trim($_POST['image']);

    if (empty($brand)) {
        $errors[] = 'Brand is required.';
    }
    if (empty($model)) {
        $errors[] = 'Model is required.';
    }
    if ($year < 1886) {
        $errors[] = 'Year must be a valid year.';
    }
    if (empty($fuel_type)) {
        $errors[] = 'Fuel type is required.';
    }
    if ($passengers < 1) {
        $errors[] = 'Passenger capacity must be at least 1.';
    }
    if ($daily_price_huf <= 0) {
        $errors[] = 'Daily price must be a positive number.';
    }
    if (empty($transmission)) {
        $errors[] = 'Transmission type is required.';
    }
    if (empty($image)) {
        $errors[] = 'Image URL is required.';
    }

    if (empty($errors)) {
        $car = [
            'id' => $carId,
            'brand' => $brand,
            'model' => $model,
            'year' => $year,
            'fuel_type' => $fuel_type,
            'passengers' => $passengers,
            'daily_price_huf' => $daily_price_huf,
            'transmission' => $transmission,
            'image' => $image
        ];
        $carStorage->update($carId, $car);
        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Car</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<nav>
    <div class="nav-left"><a href="index.php">iKarRental</a></div>
    <div class="nav-right">
        <a href="logout.php" class="btn-link">Logout</a>
    </div>
</nav>

<div class="edit-car-container">
    <h1>Edit Car</h1>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="edit_car.php?id=<?= htmlspecialchars($carId) ?>" method="post">
        <label>Brand: <input type="text" name="brand" value="<?= htmlspecialchars($car['brand']) ?>"></label><br>
        <label>Model: <input type="text" name="model" value="<?= htmlspecialchars($car['model']) ?>"></label><br>
        <label>Year: <input type="number" name="year" value="<?= htmlspecialchars($car['year']) ?>"></label><br>
        <label>Fuel Type: <input type="text" name="fuel_type" value="<?= htmlspecialchars($car['fuel_type']) ?>"></label><br>
        <label>Passengers: <input type="number" name="passengers" value="<?= htmlspecialchars($car['passengers']) ?>"></label><br>
        <label>Daily Price (HUF): <input type="number" name="daily_price_huf" value="<?= htmlspecialchars($car['daily_price_huf']) ?>"></label><br>
        <label>Transmission: <input type="text" name="gearType" value="<?= htmlspecialchars($car['transmission']) ?>"></label><br>
        <label>Image URL: <input type="text" name="image" value="<?= htmlspecialchars($car['image']) ?>"></label><br>
        <button type="submit" class="btn-yellow">Update Car</button>
    </form>
</div>
</body>
</html>