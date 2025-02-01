<?php
session_start();
include 'storage.php';

$carStorage = new Storage(new JsonIO('cars.json'));
$bookingStorage = new Storage(new JsonIO('bookings.json'));

$carId = $_GET['id'] ?? '';
$car = $carStorage->findById($carId);
$bookings = $bookingStorage->findAll();

$bookedDates = [];
foreach ($bookings as $booking) {
    if ($booking['car_id'] == $carId) {
        $startDate = new DateTime($booking['start_date']);
        $endDate = new DateTime($booking['end_date']);
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

        foreach ($dateRange as $date) {
            $bookedDates[] = $date->format('Y-m-d');
        }
    }
}
$bookedDates = array_unique($bookedDates);
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .booked-date {
            background-color: red;
            color: white;
        }
    </style>
</head>
<body>
    <div class="car-details-container">
        <h1><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h1>
        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['model']) ?>">
        <p>Year: <?= htmlspecialchars($car['year']) ?></p>
        <p>Transmission: <?= htmlspecialchars($car['transmission']) ?></p>
        <p>Seats: <?= htmlspecialchars($car['passengers']) ?></p>
        <p>Fuel Type: <?= htmlspecialchars($car['fuel_type']) ?></p>
        <p>Price: <?= number_format($car['daily_price_huf']) ?> HUF/day</p>

        <form id="bookingForm" action="book_car.php" method="post" onsubmit="return false;">
            <input type="hidden" name="car_id" value="<?= htmlspecialchars($car['id']) ?>">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" required>
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" required>
            <button type="submit" class="btn-yellow">Book Now</button>
        </form>
    </div>

    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p>Please log in to book a car.</p>
            <a href="login.php" class="btn-yellow">Login</a>
            <a href="register.php" class="btn-link">Register</a>
        </div>
    </div>

    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeConfirmationModal()">&times;</span>
            <p id="confirmationMessage"></p>
        </div>
    </div>

    <script>
        const bookedDates = <?= json_encode($bookedDates) ?>;
        const today = new Date().toISOString().split('T')[0];

        function checkLogin() {
            <?php if (!isset($_SESSION['user'])): ?>
                document.getElementById('loginModal').style.display = 'block';
                return false;
            <?php endif; ?>
            return true;
        }

        function disableBookedDates() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            startDateInput.min = today;
            endDateInput.min = today;

            function isDateBooked(date) {
                return bookedDates.includes(date.toISOString().split('T')[0]);
            }

            function validateDate(input) {
                const date = new Date(input.value);
                if (isDateBooked(date)) {
                    input.setCustomValidity('This date is already booked.');
                } else {
                    input.setCustomValidity('');
                }
            }

            function disableUnavailableDates(input) {
                const date = new Date(input.value);
                if (isDateBooked(date) || date < new Date(today)) {
                    input.value = '';
                    alert('This date is unavailable.');
                }
            }

            function disableBookedDatesInCalendar(input) {
                const datePicker = input;
                datePicker.addEventListener('focus', function() {
                    const dateList = datePicker.valueAsDate;
                    const date = new Date(dateList);
                    if (isDateBooked(date)) {
                        datePicker.value = '';
                        alert('This date is unavailable.');
                    }
                });
            }

            startDateInput.addEventListener('input', function() {
                validateDate(this);
                const selectedStartDate = new Date(this.value);
                endDateInput.min = this.value;
                endDateInput.value = '';
            });

            endDateInput.addEventListener('input', function() {
                validateDate(this);
            });

            startDateInput.addEventListener('change', function() {
                disableUnavailableDates(this);
            });

            endDateInput.addEventListener('change', function() {
                disableUnavailableDates(this);
            });

            disableBookedDatesInCalendar(startDateInput);
            disableBookedDatesInCalendar(endDateInput);
        }

        document.addEventListener('DOMContentLoaded', disableBookedDates);

        document.getElementById('bookingForm').addEventListener('submit', function(event) {
            event.preventDefault();
            if (!checkLogin()) return;

            const formData = new FormData(this);
            fetch('book_car.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const confirmationModal = document.getElementById('confirmationModal');
                const confirmationMessage = document.getElementById('confirmationMessage');
                if (data.success) {
                    confirmationMessage.textContent = `Booking successful! The ${data.car.brand} ${data.car.model} has been booked from ${data.start_date} to ${data.end_date}.`;
                } else {
                    confirmationMessage.textContent = `Booking failed: ${data.message}`;
                }
                confirmationModal.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        function closeConfirmationModal() {
            document.getElementById('confirmationModal').style.display = 'none';
        }
    </script>
</body>
</html>