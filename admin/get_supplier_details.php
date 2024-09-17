<?php
require_once(dirname(__FILE__) . '/config.php');

session_start();

// Check if the user is authorized
if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php?error=unauthorized');
    exit();
}

// Database connection
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the supplierId is set in the URL parameters
if (isset($_GET['supplierId'])) {
    $supplierId = $mysqli->real_escape_string($_GET['supplierId']);

    // Prepare SQL query to prevent SQL injection
    $stmt = $mysqli->prepare("SELECT * FROM `tg_supplier` WHERE `supplier_id` = ?");
    $stmt->bind_param("s", $supplierId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Return the data as JSON
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No Supplier found']);
    }

    // Free result and close statement
    $result->free();
    $stmt->close();
}

// Close database connection
$mysqli->close();
?>
