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

// Database connection
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch customer ID using username
$customerId = null;
$stmt = $mysqli->prepare("SELECT customer_id FROM tg_customers WHERE customer_username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($customerId);
$stmt->fetch();
$stmt->close();

// Handle order deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $orderId = isset($_POST['orderId']) ? (int)$_POST['orderId'] : 0;

    if ($orderId > 0) {
        // Prepare and execute the DELETE query
        $stmt = $mysqli->prepare("DELETE FROM tg_orders WHERE orderId = ? AND customerId = ?");
        $stmt->bind_param("ii", $orderId, $customerId);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page to reflect changes
            exit();
        } else {
            echo "Error deleting order: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Pagination setup
$limit = 7; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total number of orders for pagination
$totalOrdersSql = "SELECT COUNT(*) AS total FROM tg_orders WHERE customerId = ?";
$totalOrdersStmt = $mysqli->prepare($totalOrdersSql);
$totalOrdersStmt->bind_param("i", $customerId);
$totalOrdersStmt->execute();
$totalOrdersResult = $totalOrdersStmt->get_result();
$totalOrders = $totalOrdersResult->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);

// Fetch order data with pagination
$sql = "SELECT `id`, `orderId`, `customerName`, `customerAddress`, `productName`, `quantity`, `totalAmount`, `orderDate`, `status`
        FROM `tg_orders`
        WHERE `customerId` = ?
        LIMIT $limit OFFSET $offset";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query failed: " . $mysqli->error);
}
?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" type="image/x-icon">
    <title>Orders - BMS</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/styles-admin-main.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css"> <!-- Add datepicker CSS -->
</head>
<body>

<div class="container customers">
    <!-- Sidebar Section -->
    <aside class="customers">
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
            <a href="<?php echo BASE_URL; ?>Profile">
                <span class="material-icons-sharp">person_outline</span>
                <h3>Profile</h3>
            </a>
            <a href="<?php echo BASE_URL; ?>user-Orders" class="active">
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
    <main>
        <h1>Orders</h1>
        <!-- Orders Table -->
        <div class="customer-list">
            <div style="display: flex; align-items: center;">
                <h2>Order List</h2>
                <button class="refresh-button" onclick="window.location.reload();">
                    <span class="material-icons-sharp">refresh</span>
                </button>
            </div>
            <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Customer Address</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Order Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $orderId = htmlspecialchars($row['orderId']);
                            $customerName = htmlspecialchars($row['customerName']);
                            $customerAddress = htmlspecialchars($row['customerAddress']);
                            $productName = htmlspecialchars($row['productName']);
                            $quantity = htmlspecialchars($row['quantity']);
                            $orderDate = htmlspecialchars($row['orderDate']);
                            $totalAmount = htmlspecialchars($row['totalAmount']);
                            $orderStatus = htmlspecialchars($row['status']); // Updated field name

                            // Determine the class based on orderStatus
                            $orderStatusClass = '';
                            if ($orderStatus === 'Declined') {
                                $orderStatusClass = 'danger';
                            } elseif ($orderStatus === 'Pending') {
                                $orderStatusClass = 'warning';
                            } elseif ($orderStatus === 'Shipped') {
                                $orderStatusClass = 'success';
                            } else {
                                $orderStatusClass = 'primary';
                            }

                            echo "<tr>";
                            echo "<td>$orderId</td>";
                            echo "<td>$customerName</td>";
                            echo "<td>$customerAddress</td>";
                            echo "<td>$productName</td>";
                            echo "<td>$quantity</td>";
                            echo "<td>$orderDate</td>";
                            echo "<td>Rs.$totalAmount</td>";
                            echo "<td class=\"$orderStatusClass\">$orderStatus</td>";
                            echo "<td>
                                <button type='button' onclick='openDeleteModal(\"$orderId\")' class='delete-button' style='background-color: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 14px;'>Delete</button>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>No orders found</td></tr>";
                    }
                    $result->free();
                    $mysqli->close();
                ?>
                </tbody>
            </table>
        </div>
        <!-- End of Orders Table -->

        <!-- Pagination Controls -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="pagination-link">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="pagination-link">Next</a>
            <?php endif; ?>
        </div>
        <!-- End of Pagination Controls -->

    </main>
    <!-- End of Main Content -->

    <!-- Right Section -->
    <div class="right-section">
        <div class="nav customer">
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
                    <small class="text-muted">Customer</small>
                </div>
                <div class="profile-photo">
                    <img src="<?php echo BASE_URL; ?>dist/src/admin.jfif"></div>
            </div>
        </div>
        <!-- End of nav -->
    </div>
</div>

<!-- Delete Modal Popup -->
<div id="delete-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="content">
            <p>Are you sure you want to delete this Order?</p>
            <form id="delete-form" action="" method="POST">
                <input type="hidden" name="orderId" id="modal-order-id">
                <input type="hidden" name="action" value="delete">
                <button type="submit" style="background-color: #e74c3c;">Delete</button>
                <button type="button" id="cancel-btn">Cancel</button>
            </form>
        </div>
    </div>
</div>
<!-- End of Delete Modal Popup -->

<!-- Js Code -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script> <!-- Add datepicker JS -->
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

    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('delete-modal');
        const closeModal = document.querySelector('#delete-modal .close');
        const cancelBtn = document.getElementById('cancel-btn');
        const deleteForm = document.getElementById('delete-form');
        const modalOrderId = document.getElementById('modal-order-id');

        // Function to open delete modal and set order ID
        window.openDeleteModal = function(orderId) {
            document.getElementById('modal-order-id').value = orderId;
            document.getElementById('delete-modal').style.display = 'block';
        };

        // Close delete modal
        closeModal.addEventListener('click', function() {
            deleteModal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', function() {
            deleteModal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });

        // Handle delete form submission
        deleteForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            this.submit(); // Submit the form
        });
    });
</script>
</body>
</html>
