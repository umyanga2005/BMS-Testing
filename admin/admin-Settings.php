<?php
require_once(dirname(__FILE__) . '/config.php');

session_start();

if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php?error=unauthorized');
    exit();
}

// Debugging
error_log('Admin_ID: ' . $_SESSION['Admin_ID']);
error_log('Login_Type: ' . $_SESSION['Login_Type']);

$adminUsername = isset($_SESSION['Admin_Username']) ? $_SESSION['Admin_Username'] : 'Admin';

// Establish a connection to the database
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch recent customer details
$sql = "SELECT customer_username, photo FROM tg_customers ORDER BY customer_id DESC LIMIT 4"; 
$result = $conn->query($sql);

$customers = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Processing the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $current_password = addslashes($_POST['current_password']);
    $new_password = addslashes($_POST['new_password']);
    $confirm_password = addslashes($_POST['confirm_password']);

    $admin_id = $_SESSION['Admin_ID'];

    // Fetch the current password from the database
    $sql = "SELECT admin_password FROM tg_admin WHERE admin_id = '$admin_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $db_password = $row['admin_password'];

        // Check if the current password matches
        if (sha1($current_password) === $db_password) {
            // Check if new password and confirm password match
            if ($new_password === $confirm_password) {
                // Hash the new password
                $hashed_password = sha1($new_password);

                // Update the password in the database
                $update_sql = "UPDATE tg_admin SET admin_password = '$hashed_password' WHERE admin_id = '$admin_id'";

                if ($conn->query($update_sql) === TRUE) {
                    echo "<script>console.log('Password updated successfully!');</script>";
                } else {
                    echo "<script>console.log('Error updating password: " . $conn->error . "');</script>";
                }
            } else {
                echo "<script>console.log('New password and confirmation do not match!');</script>";
            }
        } else {
            echo "<script>console.log('Current password is incorrect!');</script>";
        }
    } else {
        echo "<script>console.log('Admin not found!');</script>";
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" type="image/x-icon">
    
    <title>Settings - BMS</title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/styles-admin-main.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
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
            <a href="<?php echo ADMIN_URL; ?>admin-dashboard">
                <span class="material-icons-sharp">dashboard</span>
                <h3>Dashboard</h3>
            </a>
            <a href="<?php echo ADMIN_URL; ?>admin-Customers">
                <span class="material-icons-sharp">person_outline</span>
                <h3>Customers</h3>
            </a>
            <a href="<?php echo ADMIN_URL; ?>admin-Products">
                <span class="material-icons-sharp">inventory_2</span>
                <h3>Product List</h3>
            </a>
            <a href="<?php echo ADMIN_URL; ?>admin-Suppliers">
                <span class="material-icons-sharp">supervisor_account</span>
                <h3>Supplier List</h3>
            </a>
            <a href="<?php echo ADMIN_URL; ?>admin-Orders">
                <span class="material-icons-sharp">inventory</span>
                <h3>Orders List</h3>
            </a>
            <a href="<?php echo ADMIN_URL; ?>admin-SpecialOrders">
                <span class="material-icons-sharp">hotel_class</span>
                <h3>Special Orders</h3>
            </a>
            <a href="<?php echo ADMIN_URL; ?>admin-FormSubmission">
                <span class="material-icons-sharp">connect_without_contact</span>
                <h3>Form Submission</h3>
            </a>
            <a href="<?php echo ADMIN_URL; ?>admin-Settings" class="active">
                <span class="material-icons-sharp">settings</span>
                <h3>Settings</h3>
            </a>
            <a href="<?php echo BASE_URL; ?>logout.php">
                <span class="material-icons-sharp">logout</span>
                <h3>Logout</h3>
            </a>
        </div>
    </aside>
    <!-- End of Sidebar Section -->

    <!-- Main Content -->
    <main>
        <h1>Settings</h1> 
        
        <!-- Dashboard -->
        <div class="dashboard settings">
            <div class="sales">
                <div class="status">
                    <div class="user-profile">
                        <div class="logo">
                            <img src="<?php echo BASE_URL; ?>dist/src/admin.jfif" class="profile-logo">
                            <h2><?php echo htmlspecialchars($adminUsername); ?></h2>
                            <p>Admin</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="change-password-form">
            <form id="password-form" action="" method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password">
                    <p id="current-password-msg" style="color:red;"></p> <!-- Error message for current password -->
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password">
                    <p id="new-password-msg" style="color:red;"></p> <!-- Error message for new password -->
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                    <p id="confirm-password-msg" style="color:red;"></p> <!-- Error message for confirm password -->
                </div>
                
                <div class="form-group">
                    <button type="submit" class="update-btn">Update Password</button>
                </div>
            </form>

            </div>
        </div>
        <!-- End of Dashboard -->

        <!-- New Users Section-->
        <div class="new-users">
            <h2>Recent Customers</h2>
            <div class="user-list">
                <?php foreach ($customers as $customer): ?>
                    <div class="user">
                        <img src="<?php echo BASE_URL . 'dist/uploads/' . htmlspecialchars($customer['photo']); ?>" alt="<?php echo htmlspecialchars($customer['customer_Name']); ?>">
                        <h2><?php echo htmlspecialchars($customer['customer_username']); ?></h2>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- End of New Users Section-->

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
                    <p>Hey, <b>Admin</b></p>
                    <small class="text-muted"><?php echo htmlspecialchars($adminUsername); ?></small>
                </div>
                <div class="profile-photo">
                    <img src="<?php echo BASE_URL; ?>dist/src/admin.jfif"></div>
            </div>
        </div>
        <!-- End of nav -->

        <div class="user-profile">
            <div class="logo">
                <img src="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" class="profile-logo logo">
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
                profileLogo.classList.toggle('active');
            }
            
            // Toggle dark mode on click
            darkModeToggle.addEventListener('click', () => {
                body.classList.toggle('dark-mode-variables');
                
                // Check if dark mode is enabled and save the preference
                if (body.classList.contains('dark-mode-variables')) {
                    localStorage.setItem('darkMode', 'enabled');
                    darkModeToggle.querySelector('span:nth-child(2)').classList.add('active');
                    darkModeToggle.querySelector('span:nth-child(1)').classList.remove('active');
                    profileLogo.classList.toggle('active');
                } else {
                    localStorage.setItem('darkMode', 'disabled');
                    darkModeToggle.querySelector('span:nth-child(1)').classList.add('active');
                    darkModeToggle.querySelector('span:nth-child(2)').classList.remove('active');
                    profileLogo.classList.toggle('active');
                }
            });
        });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.getElementById('password-form');

    passwordForm.addEventListener('submit', function(event) {
        // Call validation function
        if (!validatePasswordForm()) {
            event.preventDefault(); // Prevent form submission if validation fails
        }
    });

    function validatePasswordForm() {
        const currentPassword = document.getElementById('current_password').value.trim();
        const newPassword = document.getElementById('new_password').value.trim();
        const confirmPassword = document.getElementById('confirm_password').value.trim();
        const passwordPattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/; // At least one number, one lowercase, one uppercase, and 8+ characters

        let isValid = true;

        // Clear previous messages
        clearError('current-password-msg');
        clearError('new-password-msg');
        clearError('confirm-password-msg');

        // Current Password validation
        if (currentPassword === '') {
            showError('current-password-msg', 'Current password is required.');
            isValid = false;
        }

        // New Password validation
        if (newPassword === '') {
            showError('new-password-msg', 'New password is required.');
            isValid = false;
        } else if (!passwordPattern.test(newPassword)) {
            showError('new-password-msg', 'Password must be at least 8 characters long, with one uppercase letter, one lowercase letter, and one digit.');
            isValid = false;
        }

        // Confirm Password validation
        if (confirmPassword === '') {
            showError('confirm-password-msg', 'Please confirm your new password.');
            isValid = false;
        } else if (newPassword !== confirmPassword) {
            showError('confirm-password-msg', 'Passwords do not match.');
            isValid = false;
        }

        return isValid;
    }

    function showError(elementId, message) {
        document.getElementById(elementId).textContent = message;
    }

    function clearError(elementId) {
        document.getElementById(elementId).textContent = '';
    }
});
</script>


</body>
</html>
