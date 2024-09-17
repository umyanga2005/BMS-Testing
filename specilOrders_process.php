<?php
// Include the config file for database connection and other settings
require_once(dirname(__FILE__) . '/config.php');

// Establish a connection to the database
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $name = $conn->real_escape_string(trim($_POST['order-name']));
    $email = $conn->real_escape_string(trim($_POST['order-email']));
    $phone = $conn->real_escape_string(trim($_POST['order-phone']));
    $preferred_date = $conn->real_escape_string(trim($_POST['order-date']));
    $details = $conn->real_escape_string(trim($_POST['order-details']));

    // Prepare an SQL statement
    $sql = "INSERT INTO tg_special_orders (name, email, phone, preferred_date, details) 
            VALUES ('$name', '$email', '$phone', '$preferred_date', '$details')";

    // Execute the SQL statement
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=success");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>
