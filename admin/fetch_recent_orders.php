<?php
require_once(dirname(__FILE__) . '/config.php');

header('Content-Type: application/json');

// Database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch the last 4 orders
$sql = "SELECT id, orderId, customerName, customerAddress, productName, quantity, totalAmount, orderDate, status
        FROM tg_orders
        ORDER BY orderDate DESC
        LIMIT 4";
$result = $conn->query($sql);

$orders = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

$conn->close();

echo json_encode($orders);
?>
