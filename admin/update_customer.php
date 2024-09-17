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
    if (isset($_POST['customer_id'])) {
        $customer_id = $mysqli->real_escape_string($_POST['customer_id']);
        $customer_name = $mysqli->real_escape_string($_POST['customer_name']);
        $customer_email = $mysqli->real_escape_string($_POST['customer_email']);
        $customer_username = $mysqli->real_escape_string($_POST['customer_username']);
        $customer_address = $mysqli->real_escape_string($_POST['customer_address']);

        // Update customer details
        $sql = "UPDATE `tg_customers` SET 
                `customer_Name` = '$customer_name', 
                `customer_email` = '$customer_email', 
                `customer_username` = '$customer_username', 
                `customer_address` = '$customer_address' 
                WHERE `customer_id` = '$customer_id'";

        if ($mysqli->query($sql) === TRUE) {
            header('Location: ' . ADMIN_URL . 'admin-Customers?success=updated');
            exit();
        } else {
            echo "Error updating record: " . $mysqli->error;
        }
    }
}

$mysqli->close();
?>
