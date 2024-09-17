<?php
// Include the config file for database connection and other settings
require_once(dirname(__FILE__) . '/config.php');

// Establish a connection to the database
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $subject = $conn->real_escape_string(trim($_POST['subject']));
    $message = $conn->real_escape_string(trim($_POST['message']));
    
    // Get the current date and time
    $submitted_at = date("Y-m-d H:i:s");

    // Prepare the SQL statement
    $sql = "INSERT INTO tg_contact_form (name, email, subject, message, submitted_at) VALUES (?, ?, ?, ?, ?)";

    // Create a prepared statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("sssss", $name, $email, $subject, $message, $submitted_at);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect back to the previous page with a success message
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=success");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>
