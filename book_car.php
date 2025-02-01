<?php
session_start();
include 'storage.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$carStorage = new Storage(new JsonIO('cars.json'));
$bookingStorage = new Storage(new JsonIO('bookings.json'));

$car = $carStorage->findById($_POST['car_id'] ?? '');
if (!$car) {
    echo json_encode(['success' => false, 'message' => 'Car not found.']);
    exit;
}

$start_date = new DateTime($_POST['start_date']);
$end_date = new DateTime($_POST['end_date']);
$days = $start_date->diff($end_date)->days + 1;
$total_price = $car['daily_price_huf'] * $days;

$bookings = $bookingStorage->findAll();
foreach ($bookings as $booking) {
    if ($booking['car_id'] == $car['id']) {
        $booking_start_date = new DateTime($booking['start_date']);
        $booking_end_date = new DateTime($booking['end_date']);
        if (($start_date >= $booking_start_date && $start_date <= $booking_end_date) ||
            ($end_date >= $booking_start_date && $end_date <= $booking_end_date) ||
            ($start_date <= $booking_start_date && $end_date >= $booking_end_date)) {
            echo json_encode(['success' => false, 'message' => 'Selected dates are already booked.']);
            exit;
        }
    }
}

$bookingStorage->add([
    'car_id' => $car['id'],
    'user_id' => $_SESSION['user']['id'],
    'start_date' => $start_date->format('Y-m-d'),
    'end_date' => $end_date->format('Y-m-d'),
    'total_price' => $total_price
]);

echo json_encode([
    'success' => true,
    'car' => $car,
    'start_date' => $start_date->format('Y-m-d'),
    'end_date' => $end_date->format('Y-m-d')
]);
exit;