<?php
require_once(dirname(__FILE__) . '/config.php');

// Database connection
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplierName = $_POST['supplierName'];
    $contactName = $_POST['contactName'];
    $contactEmail = $_POST['contactEmail'];
    $phoneNumber = $_POST['phoneNumber'];
    $address = $_POST['address'];

    // Generate unique supplier_id
    $supplierQuery = "SELECT MAX(CAST(SUBSTRING(supplier_id, 2) AS UNSIGNED)) AS max_supplier_id FROM tg_supplier";
    $maxSupplierIdResult = $mysqli->query($supplierQuery);

    if ($maxSupplierIdResult) {
        $row = $maxSupplierIdResult->fetch_assoc();
        $maxSupplierId = $row['max_supplier_id'] ?? 0;
        $newSupplierId = 'S' . sprintf("%03d", $maxSupplierId + 1);

        // Prepare and execute the INSERT query
        $stmt = $mysqli->prepare("INSERT INTO tg_supplier (supplier_id, supplier_name, contact_name, contact_email, phone_number, address) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("ssssss", $newSupplierId, $supplierName, $contactName, $contactEmail, $phoneNumber, $address);
            if ($stmt->execute()) {
                echo "Supplier added successfully.<br>";
                header("Location: " . $_SERVER['HTTP_REFERER']); // Redirect back to the previous page
                 exit();
            } else {
                echo "Error adding supplier: " . $stmt->error . "<br>";
            }
            $stmt->close();
        } else {
            echo "Prepare statement failed: " . $mysqli->error . "<br>";
        }
    } else {
        echo "Query failed: " . $mysqli->error . "<br>";
    }
}

$mysqli->close();
?>
