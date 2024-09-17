<?php
require_once(dirname(__FILE__) . '/config.php');

// Database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

session_start();

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login form submission
if (isset($_POST['login'])) {
    // Prepare the query to find the user by email or username
    $user_exist_query = "SELECT `customer_id`, `customer_username`, `customer_password`, `customer_Name`, `customer_Email`, `customer_address`, `photo`, `created` 
                         FROM `tg_customers` WHERE `customer_Email` = ? OR `customer_username` = ?";
    $stmt = $conn->prepare($user_exist_query);
    
    // Bind parameters (email or username)
    $stmt->bind_param('ss', $_POST['email_username'], $_POST['email_username']);
    
    // Execute the query
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $result_fetch = $result->fetch_assoc();
        
        // Verify the password using password_verify() if the passwords are hashed with password_hash()
        if (sha1($_POST['password']) === $result_fetch['customer_password']) {
            // Set session variables on successful login
            $_SESSION['logged_in'] = true;
            $_SESSION['customer_id'] = $result_fetch['customer_id'];
            $_SESSION['customer_username'] = $result_fetch['customer_username'];
            $_SESSION['customer_name'] = $result_fetch['customer_Name'];
            $_SESSION['customer_email'] = $result_fetch['customer_Email'];
            $_SESSION['customer_photo'] = $result_fetch['photo']; // Store photo if needed
            
            // Redirect to the home or dashboard page
            header("Location:" . BASE_URL . "index.php");
            exit();
        } else {
            echo "<script>alert('Incorrect Password');
            window.location.href='". BASE_URL . "userLogin.php';</script>";   
        }
    } else {
        echo "<script>alert('Email or Username not registered');
        window.location.href='". BASE_URL . "userLogin.php';</script>";  
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" type="image/x-icon">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" crossorigin="">
   <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/styles_login.css">
   <title>Login Form</title>
</head>
<body style="background-image: url('<?php echo BASE_URL; ?>dist/src/login-bg.png'); ">
   <div class="login">
      <form method="POST" action="">
         <div class="login_inputs">
            <h1 class="login_title">Login</h1>
            <div class="login_box">
               <input type="text" name="email_username" placeholder="Email or User Name" required>
               <i class="ri-mail-fill"></i>
            </div>
            <div class="login_box">
               <input type="password" name="password" placeholder="Password" required>
               <i class="ri-lock-2-fill"></i>
            </div>
            <button type="submit" name="login">LOGIN</button>
            <div class="login_check">
               <a href="#" class="login_forgot">Forgot Password?</a>
            </div>
      </form>
   </div>
</body>
</html>
