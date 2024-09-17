<?php
require_once(dirname(__FILE__) . '/config.php');

session_start();

if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php?error=unauthorized');
    exit();
}

// Database connection
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['productId'])) {
        $productId = $mysqli->real_escape_string($_POST['productId']);
        $productName = $mysqli->real_escape_string($_POST['productName']);
        $category = $mysqli->real_escape_string($_POST['category']);
        $stockQuantity = $mysqli->real_escape_string($_POST['stockQuantity']);
        $price = $mysqli->real_escape_string($_POST['price']);

        // Update product details
        $sql = "UPDATE `tg_products` SET 
                `productName` = '$productName', 
                `category` = '$category', 
                `stockQuantity` = '$stockQuantity', 
                `price` = '$price' 
                WHERE `productId` = '$productId'";

        if ($mysqli->query($sql) === TRUE) {
            header('Location: ' . ADMIN_URL . 'admin-Products?success=updated');
            exit();
        } else {
            echo "Error updating record: " . $mysqli->error;
        }
    }
}

$mysqli->close();
?>
