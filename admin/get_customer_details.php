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

if (isset($_GET['id'])) {
    $customer_id = $mysqli->real_escape_string($_GET['id']);

    $sql = "SELECT `customer_Name`, `customer_email`, `customer_username`, `customer_address` FROM `tg_customers` WHERE `customer_id` = '$customer_id'";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No customer found']);
    }
    
    $result->free();
}

$mysqli->close();
?>
