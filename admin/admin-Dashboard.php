<?php
require_once(dirname(__FILE__) . '/config.php');


session_start();

if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php?error=unauthorized');
    exit();
}

error_log('Admin_ID: ' . $_SESSION['Admin_ID']);
error_log('Login_Type: ' . $_SESSION['Login_Type']);

$adminUsername = isset($_SESSION['Admin_Username']) ? $_SESSION['Admin_Username'] : 'Admin';

// Establish a connection to the database
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch customer details
$sql = "SELECT customer_username, photo FROM tg_customers ORDER BY customer_id DESC LIMIT 3"; 
$result = $conn->query($sql);

// Initialize an empty array to store customer data
$customers = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Query to get the total number of customers
$sqlTotalCustomers = "SELECT COUNT(*) AS total FROM tg_customers";
$resultTotal = $conn->query($sqlTotalCustomers);
$totalCustomers = 0;

if ($resultTotal->num_rows > 0) {
    $rowTotal = $resultTotal->fetch_assoc();
    $totalCustomers = $rowTotal['total'];
}

// Query to get the total number of orders
$sqlTotalOrders = "SELECT COUNT(*) AS totalOrders FROM tg_orders";
$resultTotalOrders = $conn->query($sqlTotalOrders);
$totalOrders = 0;

if ($resultTotalOrders->num_rows > 0) {
    $rowTotalOrders = $resultTotalOrders->fetch_assoc();
    $totalOrders = $rowTotalOrders['totalOrders'];
}

// SQL query to calculate the sum of totalAmount
$sql = "SELECT SUM(totalAmount) AS totalSales FROM tg_orders";
$result = $conn->query($sql);

// Initialize the variable for total sales
$totalSales = 0;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalSales = $row['totalSales'];
}

// Close the database connection
$conn->close()

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" type="image/x-icon">
    
    <title>Dashboard - BMS</title>

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
            <a href="<?php echo ADMIN_URL; ?>admin-dashboard" class="active-dashboard">
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
            <a href="<?php echo ADMIN_URL; ?>admin-Settings">
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
        <h1>Dashboard</h1>
        <!-- Dashboard -->
        <div class="dashboard">
            <div class="sales">
                <div class="status">
                    <div class="info">
                        <h3>Total Sales</h3>
                        <h1>Rs.<?php echo number_format($totalSales, 0); ?></h1>
                    </div>
                    <div class="progress">
                        <svg>
                            <circle cx="38" cy="38" r="36"></circle>
                        </svg>
                        <div class="percentage">
                            <p>+85%</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="orders">
                <div class="status">
                    <div class="info">
                        <h3>Total Orders</h3>
                        <h1><?php echo htmlspecialchars($totalOrders); ?></h1>
                    </div>
                    <div class="progress">
                        <svg>
                            <circle cx="38" cy="38" r="36"></circle>
                        </svg>
                        <div class="percentage">
                            <p>+85%</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="searches">
                <div class="status">
                    <div class="info">
                        <h3>Total Members</h3>
                        <h1><?php echo htmlspecialchars($totalCustomers); ?></h1>
                    </div>
                    <div class="progress">
                        <svg>
                            <circle cx="38" cy="38" r="36"></circle>
                        </svg>
                        <div class="percentage">
                            <p>+85%</p>
                        </div>
                    </div>
                </div>
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
                <div class="user">
                    <a href="<?php echo ADMIN_URL; ?>admin-Customers"><img src="<?php echo BASE_URL; ?>dist/src/All-Members.png" alt="All Customers"></a>
                    <h2>All Customers</h2>
                </div>
            </div>
        </div>
        <!-- End of New Users Section-->

        <!-- Recent Orders Table -->
        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Order Status</th>
                    </tr>
                </thead>
                <tbody id="recent-orders-body">
                </tbody>
            </table>
            <a href="<?php echo ADMIN_URL; ?>admin-Orders">Show All</a>
        </div>
        <!-- End of Recent Orders Table -->

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
                <img src="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" class="profile-logo">
                <h2>Oven Crust</h2>
                <p>Bakery Shop</p>
            </div>
        </div>
    </div>
</div>

<!-- Js Code -->
<script>

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

    const sideMenu = document.querySelector('aside');
    const menuBtn = document.getElementById('menu-btn');
    const closeBtn = document.getElementById('close-btn');

    const darkMode = document.querySelector('.dark-mode');
    const profileLogo = document.querySelector('.profile-logo');

    menuBtn.addEventListener('click', () => {
        sideMenu.style.display = 'block';
    });

    closeBtn.addEventListener('click', () => {
        sideMenu.style.display = 'none';
    });

    // Function to fetch recent orders and populate the table
    async function fetchRecentOrders() {
        try {
            const response = await fetch('fetch_recent_orders.php');
            const orders = await response.json();

            const tableBody = document.getElementById('recent-orders-body');
            tableBody.innerHTML = '';

            orders.forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${order.orderId}</td>
                    <td>${order.customerName}</td>
                    <td>${order.orderDate}</td>
                    <td>Rs. ${order.totalAmount}.00</td>
                    <td class="${order.status === 'Completed' ? 'success' : 'primary'}">
                        ${order.status}</td>
                `;
                tableBody.appendChild(row);
            });
        } catch (error) {
            console.error('Error fetching recent orders:', error);
        }
    }

    // Fetch recent orders when the page loads
    document.addEventListener('DOMContentLoaded', fetchRecentOrders);
</script>


</body>
</html>
