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

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['supplierId'], $_POST['supplierName'], $_POST['contactName'], 
        $_POST['contactEmail'], $_POST['phoneNumber'], $_POST['address'])
    ) {
        $supplierId = $_POST['supplierId'];
        $supplierName = $_POST['supplierName'];
        $contactName = $_POST['contactName'];
        $contactEmail = $_POST['contactEmail'];
        $phoneNumber = $_POST['phoneNumber'];
        $address = $_POST['address'];

        // Prepare the SQL statement to update the supplier information
        $stmt = $mysqli->prepare("UPDATE `tg_supplier` 
                                  SET `supplier_name` = ?, 
                                      `contact_name` = ?, 
                                      `contact_email` = ?, 
                                      `phone_number` = ?, 
                                      `address` = ? 
                                  WHERE `supplier_id` = ?");
                                  
        $stmt->bind_param("ssssss", $supplierName, $contactName, $contactEmail, $phoneNumber, $address, $supplierId);

        // Execute the prepared statement
        if ($stmt->execute()) {
            // Redirect upon success
            header('Location: ' . ADMIN_URL . 'admin-Suppliers.php?success=updated');
            exit();
        } else {
            echo "Error updating supplier: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Required fields are missing.";
    }
}

// Close the database connection
$mysqli->close();
?>
