<?php
require_once(dirname(__FILE__) . '/config.php');



header('Content-Type: application/json');

if (isset($_GET['productName'])) {
    $productName = $_GET['productName'];

    // Database connection
    $mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("SELECT price FROM tg_products WHERE productName = ?");
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $stmt->bind_result($price);

    $response = array('price' => null);
    if ($stmt->fetch()) {
        $response['price'] = $price;
    }

    $stmt->close();
    $conn->close();

    echo json_encode($response);
} else {
    echo json_encode(array('price' => null));
}
?>
