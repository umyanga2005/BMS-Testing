<?php
require_once(dirname(__FILE__) . '/config.php');

session_start();

if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php?error=unauthorized');
    exit();
}

$adminUsername = $_SESSION['Admin_Username']; // Assuming this is set on login

// Database connection
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Set up pagination variables
$items_per_page = 7; // Number of items per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Get current page number from query string
$offset = ($page - 1) * $items_per_page;

// Handle form submission for delete and edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['customer_id']) && isset($_POST['action'])) {
        $customer_id = $mysqli->real_escape_string($_POST['customer_id']);
        $action = $_POST['action'];

        if ($action === 'delete') {
            // Delete the customer from the database
            $sql = "DELETE FROM `tg_customers` WHERE `customer_id` = '$customer_id'";
            if ($mysqli->query($sql) === TRUE) {
                // Record deleted successfully
                header('Location: ' . ADMIN_URL . 'admin-Customers.php?success=deleted');
                exit();
            } else {
                echo "Error deleting record: " . $mysqli->error;
            }
        } elseif ($action === 'edit') {
            // Redirect to the edit page
            header('Location: ' . ADMIN_URL . 'edit_customer.php?id=' . $customer_id);
            exit();
        }
    }
}

// Get total number of customers
$result_total = $mysqli->query("SELECT COUNT(*) AS total FROM `tg_customers`");
$total_row = $result_total->fetch_assoc();
$total_customers = $total_row['total'];
$total_pages = ceil($total_customers / $items_per_page);

// Fetch customers for the current page
$sql = "SELECT `customer_id`, `customer_username`, `customer_Name`, `customer_email`, `customer_address`, `photo` FROM `tg_customers` LIMIT $offset, $items_per_page";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" type="image/x-icon">
    <title>Customers - BMS</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/styles-admin-main.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
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
            <a href="<?php echo ADMIN_URL; ?>admin-dashboard">
                <span class="material-icons-sharp">dashboard</span>
                <h3>Dashboard</h3>
            </a>
            <a href="<?php echo ADMIN_URL; ?>admin-Customers" class="active">
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
        <h1>Customers</h1>
        <!-- Recent Customer Table -->
        <div class="customer-list">
            <div style="display: flex; align-items: center;">
                <h2>Customer List</h2>
                <button class="refresh-button" onclick="window.location.reload();">
                    <span class="material-icons-sharp">refresh</span>
                </button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $customer_id = htmlspecialchars($row['customer_id']);
                            $photo = htmlspecialchars($row['photo']);
                            $customer_name = htmlspecialchars($row['customer_Name']);
                            $customer_email = htmlspecialchars($row['customer_email']);
                            $customer_username = htmlspecialchars($row['customer_username']);
                            $customer_address = htmlspecialchars($row['customer_address']);

                            echo "<tr>";
                            echo "<td>$customer_id</td>";
                            echo "<td><center><img src='" . BASE_URL . "dist/uploads/$photo' alt='Customer Photo' style='width: 40px; height: 40px; border-radius: 50%;'></center></td>";
                            echo "<td>$customer_name</td>";
                            echo "<td>$customer_email</td>";
                            echo "<td>$customer_username</td>";
                            echo "<td>$customer_address</td>";
                            echo "<td>
                                <button type='button' onclick='openEditModal(\"$customer_id\")' class='edit-button' style='background-color: #3498db; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 14px;'>Edit</button>
                                <button type='button' onclick='openDeleteModal(\"$customer_id\")' class='delete-button' style='background-color: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 14px;'>Delete</button>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No customers found</td></tr>";
                    }
                    $result->free();
                    $mysqli->close();
                    ?>
                </tbody>

            </table>
            <!-- Pagination Controls -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="page-link">Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="page-link <?php if ($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="page-link">Next</a>
                <?php endif; ?>
            </div>
        </div>
        </div>
        <!-- End of Recent Customer Table -->
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
                    <p>Hey, <b>Admin</b></p>
                    <small class="text-muted"><?php echo htmlspecialchars($adminUsername); ?></small>
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
            <p>Are you sure you want to delete this customer?</p>
            <form id="delete-form" action="" method="POST">
                <input type="hidden" name="customer_id" id="modal-customer-id">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="delete-button" style="background-color: #e74c3c; color: white; border: none; padding: 0.8rem 1.5rem; border-radius: 4px; cursor: pointer; font-size: 14px;">Delete</button>
                <button type="button" id="cancel-btn" >Cancel</button>
            </form>
        </div>
    </div>
</div>
<!-- End of Delete Modal Popup -->

<!-- Edit Modal Popup -->
<div id="edit-modal" class="modal">
    <div class="modal-content">
        <span class="close-edit"></span>
        <div class="content">
            <h2>Edit Customer</h2>
            <form id="edit-form" action="update_customer.php" method="POST">
                <input type="hidden" name="customer_id" id="edit-customer-id">
                <label for="edit-customer-name">Name:</label>
                <input type="text" id="edit-customer-name" name="customer_name">
                <span id="name-msg"></span>

                <label for="edit-customer-email">Email:</label>
                <input type="email" id="edit-customer-email" name="customer_email">
                <span id="email-msg"></span>

                <label for="edit-customer-username">Username:</label>
                <input type="text" id="edit-customer-username" name="customer_username" >
                <span id="username-msg"></span>

                <label for="edit-customer-address">Address:</label>
                <input type="text" id="edit-customer-address" name="customer_address" >
                <span id="address-msg"></span>

                <button type="submit">Update</button>
                <button type="button" id="cancel-edit-btn">Cancel</button>
            </form>
        </div>
    </div>
</div>
<!-- End of Edit Modal Popup -->

<!-- Js Code -->
<script src="<?php echo BASE_URL ?>dist/js/form-validations.js"></script>

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
        const modalCustomerId = document.getElementById('modal-customer-id');

        const editModal = document.getElementById('edit-modal');
        const closeEditModal = document.querySelector('.modal .close-edit');
        const cancelEditBtn = document.getElementById('cancel-edit-btn');
        const editCustomerId = document.getElementById('edit-customer-id');

        // Function to open delete modal and set customer ID
        window.openDeleteModal = function(customerId) {
            modalCustomerId.value = customerId;
            deleteModal.style.display = 'block';
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

        // Show edit modal
        window.openEditModal = function(customerId) {
            fetch('get_customer_details.php?id=' + customerId)
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        editCustomerId.value = customerId;
                        document.getElementById('edit-customer-name').value = data.customer_Name;
                        document.getElementById('edit-customer-email').value = data.customer_email;
                        document.getElementById('edit-customer-username').value = data.customer_username;
                        document.getElementById('edit-customer-address').value = data.customer_address;
                        editModal.style.display = 'block';
                    } else {
                        console.error('Error fetching customer details:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
        };

        // Close edit modal
        closeEditModal.addEventListener('click', function() {
            editModal.style.display = 'none';
        });

        cancelEditBtn.addEventListener('click', function() {
            editModal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
        });
    });

</script>


</body>
</html>
