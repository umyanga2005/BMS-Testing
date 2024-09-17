<?php
require_once(dirname(__FILE__) . '/config.php');

// Database connection
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['productName'];
    $category = $_POST['category'];
    $stockQuantity = (int)$_POST['stockQuantity'];
    $price = (float)$_POST['price'];
    
    // Generate unique productId
    $productQuery = "SELECT MAX(CAST(SUBSTRING(productId, 2) AS UNSIGNED)) AS max_product_id FROM tg_products";
    $maxProductIdResult = $mysqli->query($productQuery);
    $row = $maxProductIdResult->fetch_assoc();
    $maxProductId = $row['max_product_id'];
    $newProductId = 'P' . sprintf("%03d", $maxProductId + 1);
    
    // Generate a unique image name based on productId and productName
    $sanitizedProductName = preg_replace('/[^a-zA-Z0-9]/', '_', $productName); // Sanitize productName for safe file naming
    $imageExtension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
    $imageName = $newProductId . '_' . $sanitizedProductName . '.' . $imageExtension;
    $productImagePath = 'dist/uploads/products/' . $imageName; // Relative path for storage

    // Handle image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['product_image']['tmp_name'];

        // Ensure the target directory exists
        if (!is_dir(dirname($productImagePath))) {
            mkdir(dirname($productImagePath), 0755, true); // Create directory if it doesn't exist
        }

        // Move the uploaded file to the desired directory
        if (!move_uploaded_file($imageTmpName, $productImagePath)) {
            echo "Failed to move uploaded file.";
            exit();
        }
    } else {
        $productImagePath = ''; // Set to empty if no image uploaded
    }

    // Prepare and execute the INSERT query
    $stmt = $mysqli->prepare("INSERT INTO tg_products (productId, productImage, productName, category, stockQuantity, price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $newProductId, $productImagePath, $productName, $category, $stockQuantity, $price);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['HTTP_REFERER']); // Redirect back to the previous page
        exit();
    } else {
        echo "Error adding product: " . $stmt->error;
    }

    $stmt->close();
}

$mysqli->close();
?>
