<?php
// Include the configuration file for database connection
require_once(dirname(__FILE__) . '/config.php');

// Start the session
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ' . BASE_URL . 'userLogin.php');
    exit;
}

// Get the logged-in user's username from the session
$username = $_SESSION['customer_username'];

// Establish a connection to the database
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details from the database based on the username
$sql = "SELECT customer_id, customer_username, customer_Name, customer_Email, customer_address, photo FROM tg_customers WHERE customer_username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get user ID from the fetched data
$user_id = $user['customer_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Update name if provided
    if (!empty($name)) {
        $stmt = $conn->prepare('UPDATE tg_customers SET customer_Name = ? WHERE customer_id = ?');
        $stmt->bind_param("si", $name, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Name updated successfully.';
        } else {
            $_SESSION['error_message'] = 'Error updating name: ' . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }

    // Update email if provided
    if (!empty($email)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare('UPDATE tg_customers SET customer_Email = ? WHERE customer_id = ?');
            $stmt->bind_param("si", $email, $user_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Email updated successfully.';
            } else {
                $_SESSION['error_message'] = 'Error updating email: ' . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = 'Invalid email format.';
        }
    }

    // Update address if provided
    if (!empty($address)) {
        $stmt = $conn->prepare('UPDATE tg_customers SET customer_address = ? WHERE customer_id = ?');
        $stmt->bind_param("si", $address, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Address updated successfully.';
        } else {
            $_SESSION['error_message'] = 'Error updating address: ' . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }

    // Update password if provided
    if (!empty($new_password) || !empty($confirm_password)) {
        if (empty($new_password) || empty($confirm_password)) {
            $_SESSION['error_message'] = 'All password fields are required.';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['error_message'] = 'New passwords do not match.';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        // Hash new password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password in the database
        $stmt = $conn->prepare('UPDATE tg_customers SET password = ? WHERE customer_id = ?');
        $stmt->bind_param("si", $new_password_hash, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Password updated successfully.';
        } else {
            $_SESSION['error_message'] = 'Error updating password: ' . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }

    // Handle profile picture upload if a file was provided
    if (!empty($_FILES['profile_picture']['name'])) {
        $file = $_FILES['profile_picture'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($file['type'], $allowed_types)) {
                $upload_dir = 'dist/uploads/';
                $file_name = $user_id . '-' . basename($file['name']);
                $file_path = $upload_dir . $file_name;

                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    // Update the photo in the database
                    $stmt = $conn->prepare('UPDATE tg_customers SET photo = ? WHERE customer_id = ?');
                    $stmt->bind_param("si", $file_name, $user_id);
                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = 'Profile picture updated successfully.';
                    } else {
                        $_SESSION['error_message'] = 'Error updating profile picture: ' . htmlspecialchars($stmt->error);
                    }
                    $stmt->close();
                } else {
                    $_SESSION['error_message'] = 'Error moving uploaded file.';
                }
            } else {
                $_SESSION['error_message'] = 'Invalid file type.';
            }
        } else {
            $_SESSION['error_message'] = 'Error uploading file.';
        }
    }

    // Redirect to the same page to refresh profile details
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" type="image/x-icon">
    
    <title>User Profile - BMS</title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/styles-admin-main.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">

    <script>
    document.getElementById('password-form').addEventListener('submit', function(event) {
        event.preventDefault();
        
        var formData = new FormData(this);

        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            // Update the page with result
            document.querySelector('body').innerHTML = result;
        })
        .catch(error => console.error('Error:', error));
    });
    </script>

</head>
<body>

<div class="container">
    <!-- Sidebar Section -->
    <aside>
        <div class="toggle">
            <div class="logo">
                <img src="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" alt="Oven Crust Logo">
                <h2>Oven<span class="danger">Crust</span></h2>
            </div>
            <div class="close" id="close-btn">
                <span class="material-icons-sharp">close</span>
            </div>
        </div>

        <div class="sidebar">
            <a href="<?php echo BASE_URL; ?>Profile" class="active-dashboard">
                <span class="material-icons-sharp">person_outline</span>
                <h3>Profile</h3>
            </a>
            <a href="<?php echo BASE_URL; ?>user-Orders">
                <span class="material-icons-sharp">inventory</span>
                <h3>Order List</h3>
            </a>
            <a href="<?php echo BASE_URL; ?>logout-user.php">
                <span class="material-icons-sharp">logout</span>
                <h3>Logout</h3>
            </a>
        </div>
    </aside>
    <!-- End of Sidebar Section -->

    <!-- Main Content -->
    <main class="user-profile">
        <h1>User Profile</h1> 
        
        <!-- Dashboard -->
        <div class="dashboard settings">
            <div class="sales">
                <div class="status">
                    <div class="user-profile">
                        <div class="logo">
                            <!-- Display user photo -->
                            <img src="<?php echo BASE_URL . 'dist/uploads/' . htmlspecialchars($user['photo']); ?>" class="profile-logo" id="profile-pic">
                            <h2><?php echo htmlspecialchars($username); ?></h2>
                            <p>User</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="change-password-form">
            <form id="password-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['customer_Name']); ?>">
                    <div id="name-msg" class="error-msg"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['customer_Email']); ?>">
                    <div id="email-msg" class="error-msg"></div>
                </div>

                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['customer_address']); ?>">
                    <div id="address-msg" class="error-msg"></div>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password">
                    <div id="new-password-msg" class="error-msg"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                    <div id="confirm-password-msg" class="error-msg"></div>
                </div>

                <div class="form-group">
                    <label for="input-file" class="input-file-label">Update Profile Picture:</label>
                    <input type="file" accept="image/jpeg, image/png, image/jpg" id="input-file" name="profile_picture">
                    <div id="profile-picture-msg" class="error-msg"></div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="update-btn">Update</button>
                </div>
            </form>

            </div>
        </div>
        <!-- End of Dashboard -->
    </main>
    <!-- End of Main Content -->

    <!-- Right Section -->
    <div class="right-section">
        <div class="nav">
            <button id="menu-btn">
                <span class="material-icons-sharp">menu</span>
            </button>
            <div class="dark-mode">
                <span class="material-icons-sharp active">light_mode</span>
                <span class="material-icons-sharp">dark_mode</span>
            </div>

            <div class="profile">
                <div class="info">
                    <p>Hey, <b><?php echo htmlspecialchars($username); ?></b></p>
                    <small class="text-muted">Admin</small>
                </div>
                <div class="profile-photo">
                    <img src="<?php echo BASE_URL . 'dist/uploads/' . htmlspecialchars($user['photo']); ?>" class="profile-logo" id="profile-pic">
                </div>
            </div>
        </div>
        <!-- End of nav -->

        <div class="user-profile">
            <div class="logo">
                <img src="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" class="profile-logo logo" alt="Oven Crust Logo">
                <h2>Oven Crust</h2>
                <p>Bakery Shop</p>
            </div>
        </div>
    </div>
</div>

<!-- Js Code -->
<script>
    const sideMenu = document.querySelector('aside');
    const menuBtn = document.getElementById('menu-btn');
    const closeBtn = document.getElementById('close-btn');

    const darkMode = document.querySelector('.dark-mode');
    const profileLogo = document.querySelector('.profile-logo.logo');

    menuBtn.addEventListener('click', () => {
        sideMenu.style.display = 'block';
    });

    closeBtn.addEventListener('click', () => {
        sideMenu.style.display = 'none';
    });

    // JavaScript for Dark Mode Toggle
    document.addEventListener("DOMContentLoaded", function() {
        const darkModeToggle = document.querySelector('.dark-mode');
        const body = document.body;
        
        // Load the user's preference if previously saved
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode-variables');
            darkModeToggle.querySelector('span:nth-child(2)').classList.add('active');
            darkModeToggle.querySelector('span:nth-child(1)').classList.remove('active');
        }

        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode-variables');
            const isDarkMode = body.classList.contains('dark-mode-variables');
            
            if (isDarkMode) {
                localStorage.setItem('darkMode', 'enabled');
                darkModeToggle.querySelector('span:nth-child(2)').classList.add('active');
                darkModeToggle.querySelector('span:nth-child(1)').classList.remove('active');
            } else {
                localStorage.setItem('darkMode', 'disabled');
                darkModeToggle.querySelector('span:nth-child(1)').classList.add('active');
                darkModeToggle.querySelector('span:nth-child(2)').classList.remove('active');
            }
        });
    });

    let profilePic = document.getElementById("profile-pic");
    let inputFile = document.getElementById("input-file");

    inputFile.onchange = function() {
        profilePic.src = URL.createObjectURL(inputFile.files[0]);
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function validatePasswordForm() {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const address = document.getElementById('address').value.trim();
            const newPassword = document.getElementById('new_password').value.trim();
            const confirmPassword = document.getElementById('confirm_password').value.trim();
            const profilePicture = document.getElementById('input-file').files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            const minPasswordLength = 8;

            let isValid = true;

            // Name validation
            if (name === '') {
                showError('name-msg', 'Name is required.');
                isValid = false;
            } else {
                clearError('name-msg');
            }

            // Email validation
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email === '') {
                showError('email-msg', 'Email is required.');
                isValid = false;
            } else if (!emailPattern.test(email)) {
                showError('email-msg', 'Invalid email format.');
                isValid = false;
            } else {
                clearError('email-msg');
            }

            // Address validation
            if (address === '') {
                showError('address-msg', 'Address is required.');
                isValid = false;
            } else {
                clearError('address-msg');
            }

            // Confirm Password validation
            if (confirmPassword !== newPassword) {
                showError('confirm-password-msg', 'Passwords do not match.');
                isValid = false;
            } else {
                clearError('confirm-password-msg');
            }
            return isValid;
        }

        const passwordForm = document.getElementById('password-form');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(event) {
                if (!validatePasswordForm()) {
                    event.preventDefault(); // Prevent form submission if validation fails
                }
            });
        }
    });

    function showError(elementId, message) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = message;
            element.style.color = '#e74c3c'; // Red color for error messages
            element.style.fontSize = '0.875rem'; // Slightly smaller font size
            element.style.marginTop = '0.25rem'; // Space above the message
            element.style.fontWeight = '400'; // Normal font weight
        }
    }

    function clearError(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = '';
            element.style.color = ''; // Remove color
            element.style.fontSize = ''; // Reset font size
            element.style.marginTop = ''; // Remove margin
            element.style.fontWeight = ''; // Reset font weight
        }
    }


</script>

</body>
</html>
