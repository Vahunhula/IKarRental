<?php
session_start();
include 'storage.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] !== 1) {
    die("Access denied. Only admins can add cars.");
}

$jsonFile = 'cars.json';
$cars = json_decode(file_get_contents($jsonFile), true);

$lastCarId = 0;
if (!empty($cars)) {
    $lastCarId = max(array_column($cars, 'id'));
}

$newCarId = $lastCarId + 1;

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $newCarId;
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
        $newCar = [
            'id' => $id,
            'brand' => $brand,
            'model' => $model,
            'year' => $year,
            'fuel_type' => $fuel_type,
            'passengers' => $passengers,
            'daily_price_huf' => $daily_price_huf,
            'transmission' => $transmission,
            'image' => $image
        ];
        $cars[] = $newCar;
        file_put_contents($jsonFile, json_encode($cars, JSON_PRETTY_PRINT));
        $successMessage = "Car added successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Car</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<nav>
    <div class="nav-left"><a href="index.php">iKarRental</a></div>
    <div class="nav-right">
    </div>
</nav>

<div class="add-car-container">
    <h1>Add Car</h1>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php elseif (isset($successMessage)): ?>
        <div class="success">
            <p><?= htmlspecialchars($successMessage) ?></p>
        </div>
    <?php endif; ?>
    <form action="add_car.php" method="post">
        <label for="brand">Brand:</label>
        <input type="text" id="brand" name="brand" required>

        <label for="model">Model:</label>
        <input type="text" id="model" name="model" required>

        <label for="year">Year:</label>
        <input type="number" id="year" name="year" min="1886" required>

        <label for="fuel_type">Fuel Type:</label>
        <input type="text" id="fuel_type" name="fuel_type" required>

        <label for="passengers">Passenger Capacity:</label>
        <input type="number" id="passengers" name="passengers" min="1" required>

        <label for="daily_price_huf">Daily Price in HUF:</label>
        <input type="number" id="daily_price_huf" name="daily_price_huf" min="0" required>

        <label for="gearType">Gear Type:</label>
        <select id="gearType" name="gearType" required>
            <option value="Manual">Manual</option>
            <option value="Automatic">Automatic</option>
        </select>

        <label for="image">Image URL:</label>
        <input type="url" id="image" name="image" required>

        <button type="submit" class="btn-yellow">Add Car</button>
    </form>
</div>
</body>
</html>