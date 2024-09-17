<?php
require_once(dirname(__FILE__) . '/config.php');

// Database connection
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate unique orderId
    $orderQuery = "SELECT MAX(CAST(SUBSTRING(orderId, 2) AS UNSIGNED)) AS max_order_id FROM tg_orders";
    $maxOrderIdResult = $mysqli->query($orderQuery);

    if ($maxOrderIdResult === false) {
        die("Query failed: " . $mysqli->error);
    }

    $row = $maxOrderIdResult->fetch_assoc();
    $maxOrderId = $row['max_order_id'] ?? 0; // Handle case when no rows are returned
    $newOrderId = 'O' . sprintf("%03d", $maxOrderId + 1);

    // Gather form data
    $customerName = $_POST['customerName'];
    $customerAddress = $_POST['customerAddress'];
    $productName = $_POST['productName'];
    $quantity = (int)$_POST['quantity'];
    $totalAmount = (float)$_POST['totalAmount'];
    $status = $_POST['status'];
    $orderDate = $_POST['orderDateTime']; // Add this line to get the date and time from the form

    // Prepare and execute the INSERT query
    $stmt = $mysqli->prepare("INSERT INTO tg_orders (orderId, customerName, customerAddress, productName, quantity, totalAmount, orderDate, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        die("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("ssssdsss", $newOrderId, $customerName, $customerAddress, $productName, $quantity, $totalAmount, $orderDate, $status);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['HTTP_REFERER']); // Redirect back to the previous page
        exit();
    } else {
        echo "Error adding order: " . $stmt->error;
    }

    $stmt->close();
}

$mysqli->close();
?>
