<?php
session_start();
include 'storage.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] !== 1) {
    die("Access denied.");
}

$carStorage = new Storage(new JsonIO('cars.json'));
$carId = $_GET['id'] ?? '';

if ($carId) {
    $carStorage->delete($carId);
}

header('Location: index.php');
exit;
?>