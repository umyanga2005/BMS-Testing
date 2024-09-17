<?php
require_once(dirname(__FILE__) . '/config.php');

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = addslashes($_POST['username']);
    $password = addslashes($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $adminCheck = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "admin` WHERE `admin_code` = '$username' AND `admin_password` = '" . sha1($password) . "' LIMIT 1");

        if ($adminCheck) {
            if (mysqli_num_rows($adminCheck) == 1) {
                $adminData = mysqli_fetch_assoc($adminCheck);
                $_SESSION['Admin_ID'] = $adminData['admin_id'];
                $_SESSION['Login_Type'] = 'admin';
                $_SESSION['Admin_Username'] = $adminData['admin_code']; // Store username in session

                header('Location: ' . ADMIN_URL . 'admin-dashboard');
                exit();
            } else {
                header('Location: ' . BASE_URL . 'index.php?error=invalid_credentials');
                exit();
            }
        } else {
            die('Error: ' . mysqli_error($db));
        }
    } else {
        header('Location: ' . BASE_URL . 'index.php?error=empty_fields');
        exit();
    }
}

if (isset($_SESSION['Admin_ID']) && isset($_SESSION['Login_Type'])) {
    error_log('Admin_ID: ' . $_SESSION['Admin_ID']);
    error_log('Login_Type: ' . $_SESSION['Login_Type']);
} else {
    error_log('Session variables not set.');
}

if (isset($_SESSION['Admin_ID'])) {
    if ($_SESSION['Login_Type'] == 'admin') {
        header('Location: ' . ADMIN_URL . 'admin-dashboard');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" type="image/x-icon">
    
    <title>Login - Oven Crust - BMS</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/admin-logins-style.css">
</head>

<body class="admin-login" id="admin-login">
    <h1 class="title-text">Oven Crust - BMS</h1>
    <div class="container" id="container">
        <div class="form-container sign-in-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <h1>Admin Login</h1>
                <img src="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" class="admin-profile-logo">
                <div class="infield">
                    <input type="text" placeholder="Username" id="username" name="username" required/>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="password" placeholder="Password" id="password" name="password" required/>
                    <label></label>
                </div>
                <button type="submit">Log In</button>
            </form>
        </div>
    </div>

    <!-- Connecting JS Script -->
    <script src="<?php echo BASE_URL; ?>dist/js/main.js"></script>
</body>
</html>
