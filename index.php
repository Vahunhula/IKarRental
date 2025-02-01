<?php
session_start();
include 'storage.php';

$carStorage = new Storage(new JsonIO('cars.json'));
$bookingStorage = new Storage(new JsonIO('bookings.json'));

$seats = 0;
$gearType = null;
$minPrice = null;
$maxPrice = null;
$startDate = null;
$endDate = null;
$filteredCars = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $seats = isset($_GET['seats']) ? (int)$_GET['seats'] : 0;
    $gearType = isset($_GET['gear_type']) ? $_GET['gear_type'] : null;
    $minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : null;
    $maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : null;
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

    $cars = $carStorage->findAll();
    $bookings = $bookingStorage->findAll();
    $filteredCars = array_filter($cars, function($car) use ($seats, $gearType, $minPrice, $maxPrice, $startDate, $endDate, $bookings) {
        $matchesFilters = ($seats === 0 || $car['passengers'] >= $seats) &&
                          (!$gearType || $car['transmission'] === $gearType) &&
                          ($minPrice === null || $car['daily_price_huf'] >= $minPrice) &&
                          ($maxPrice === null || $car['daily_price_huf'] <= $maxPrice);

        if ($matchesFilters && $startDate && $endDate) {
            $startDateObj = new DateTime($startDate);
            $endDateObj = new DateTime($endDate);
            foreach ($bookings as $booking) {
                if ($booking['car_id'] == $car['id']) {
                    $bookingStartDate = new DateTime($booking['start_date']);
                    $bookingEndDate = new DateTime($booking['end_date']);
                    if (($startDateObj >= $bookingStartDate && $startDateObj <= $bookingEndDate) ||
                        ($endDateObj >= $bookingStartDate && $endDateObj <= $bookingEndDate) ||
                        ($startDateObj <= $bookingStartDate && $endDateObj >= $bookingEndDate)) {
                        return false; 
                    }
                }
            }
        }

        return $matchesFilters;
    });
} else {
    $filteredCars = $carStorage->findAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental</title>
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

    <header class="hero">
        <h1>Rent cars easily!</h1>
    </header>

    <section class="filters">
        <form method="GET" action="index.php">
            <div class="filter-box">
                <label>Seats:</label>
                <button type="button" onclick="updateSeats(-1)">-</button>
                <span id="seats-count"><?= htmlspecialchars($seats) ?></span>
                <input type="hidden" name="seats" id="seats-input" value="<?= htmlspecialchars($seats) ?>">
                <button type="button" onclick="updateSeats(1)">+</button>
            </div>

            <select name="gear_type">
                <option value="">Gear type</option>
                <option value="Automatic" <?= $gearType == 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                <option value="Manual" <?= $gearType == 'Manual' ? 'selected' : '' ?>>Manual</option>
            </select>

            <div class="price-inputs">
                <label for="min_price">Min Price (Ft):</label>
                <input type="number" name="min_price" id="min_price" min="0" max="9999999" value="<?= htmlspecialchars($minPrice) ?>">

                <label for="max_price">Max Price (Ft):</label>
                <input type="number" name="max_price" id="max_price" min="0" max="9999999999" value="<?= htmlspecialchars($maxPrice) ?>">
            </div>

            <div class="date-inputs">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($startDate) ?>">

                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($endDate) ?>">
            </div>

            <button type="submit" class="btn-yellow">Filter</button>
        </form>
    </section>

    <main class="car-listings">
        <?php if (count($filteredCars) > 0): ?>
            <?php foreach ($filteredCars as $car): ?>
                <div class="car-card">
                    <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['model']) ?>">
                    <div class="price"><?= number_format($car['daily_price_huf']) ?> Ft</div>
                    <div class="car-details">
                        <h3><?= htmlspecialchars($car['brand']) ?> <strong><?= htmlspecialchars($car['model']) ?></strong></h3>
                        <p><?= $car['passengers'] ?> seats - <?= htmlspecialchars($car['transmission']) ?></p>
                        <a href="car_details.php?id=<?= htmlspecialchars($car['id']) ?>" class="btn-yellow small-btn">Details</a>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_admin'] === 1): ?>
                            <a href="edit_car.php?id=<?= htmlspecialchars($car['id']) ?>" class="btn-link small-btn">Edit</a>
                            <button onclick="deleteCar('<?= htmlspecialchars($car['id']) ?>')" class="btn-link small-btn">Delete</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No cars match your filter criteria.</p>
        <?php endif; ?>
    </main>

    <script>
        function updateSeats(change) {
            let seatsCount = document.getElementById('seats-count');
            let seatsInput = document.getElementById('seats-input');
            let currentSeats = parseInt(seatsCount.innerText) || 0;
            let newSeats = currentSeats + change;
            if (newSeats >= 0) {
                seatsCount.innerText = newSeats;
                seatsInput.value = newSeats;
            }
        }

        function deleteCar(carId) {
            if (confirm('Are you sure you want to delete this car?')) {
                window.location.href = 'delete_car.php?id=' + carId;
            }
        }
    </script>
</body>
</html>