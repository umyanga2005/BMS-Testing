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

// Check if the request is a POST request and contains the 'action' field with the value 'delete'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Get the supplier ID from the POST data
    $supplierId = isset($_POST['supplierId']) ? trim($_POST['supplierId']) : '';

    // Check if the supplier ID is not empty
    if (!empty($supplierId)) {
        // Prepare the DELETE query
        if ($stmt = $mysqli->prepare("DELETE FROM tg_supplier WHERE supplier_id = ?")) {
            // Bind the supplier ID parameter as an integer or string depending on its data type
            $stmt->bind_param("s", $supplierId);

            // Execute the query
            if ($stmt->execute()) {
                // Redirect to the same page to reflect changes
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=deleted");
                exit();
            } else {
                // Error message if execution fails
                echo "Error deleting supplier: " . $stmt->error;
            }

            // Close the prepared statement
            $stmt->close();
        } else {
            // Error message if statement preparation fails
            echo "Error preparing statement: " . $mysqli->error;
        }
    } else {
        echo "Invalid supplier ID.";
    }
}


// Fetch supplier data
$sql = "SELECT `id`, `supplier_id`, `supplier_name`, `contact_name`, `contact_email`, `phone_number`, `address` FROM `tg_supplier`";
$result = $mysqli->query($sql);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}

// Number of rows to show per page
$rowsPerPage = 6;

// Get the current page number from the URL, default to page 1 if not present
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $currentPage = (int) $_GET['page'];
} else {
    $currentPage = 1;
}

// Calculate the offset for the SQL query
$offset = ($currentPage - 1) * $rowsPerPage;

// Get the total number of suppliers
$totalQuery = "SELECT COUNT(*) AS total FROM `tg_supplier`";
$totalResult = $mysqli->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalSuppliers = $totalRow['total'];

// Calculate total pages
$totalPages = ceil($totalSuppliers / $rowsPerPage);

// Fetch the suppliers for the current page
$query = "SELECT `id`, `supplier_id`, `supplier_name`, `contact_name`, `contact_email`, `phone_number`, `address` 
          FROM `tg_supplier` 
          LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $rowsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" type="image/x-icon">
    <title>Suppliers - BMS</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/styles-admin-main.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">

</head>
<body>

<div class="container customers supplier">
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
            <a href="<?php echo ADMIN_URL; ?>admin-Customers">
                <span class="material-icons-sharp">person_outline</span>
                <h3>Customers</h3>
            </a>
            <a href="<?php echo ADMIN_URL; ?>admin-Products">
                <span class="material-icons-sharp">inventory_2</span>
                <h3>Product List</h3>
            </a>
            <a href="<?php echo ADMIN_URL; ?>admin-Suppliers" class="active">
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
        <h1>Suppliers</h1>
        <!-- Supplier Table -->
        <div class="customer-list">
            <div style="display: flex; align-items: center;">
                <h2>Suppliers List</h2>
                <button class="refresh-button" onclick="window.location.reload();">
                    <span class="material-icons-sharp">refresh</span>
                </button>
                <button id="add-supplier-btn" class="refresh-button" onclick="openAddSupplierModal()">
                    <span class="material-icons-sharp">add</span>
                </button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Supplier ID</th>
                        <th>Supplier Name</th>
                        <th>Contact Name</th>
                        <th>Contact Email</th>
                        <th>Phone Number</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $id = htmlspecialchars($row['id']);
                            $supplierId = htmlspecialchars($row['supplier_id']);
                            $supplierName = htmlspecialchars($row['supplier_name']);
                            $contactName = htmlspecialchars($row['contact_name']);
                            $contactEmail = htmlspecialchars($row['contact_email']);
                            $phoneNumber = htmlspecialchars($row['phone_number']);
                            $address = htmlspecialchars($row['address']);

                            echo "<tr>";
                            echo "<td>$supplierId</td>";
                            echo "<td>$supplierName</td>";
                            echo "<td>$contactName</td>";
                            echo "<td>$contactEmail</td>";
                            echo "<td>$phoneNumber</td>";
                            echo "<td>$address</td>";
                            echo "<td>
                                <button type='button' onclick='openEditModal(\"$supplierId\")' class='edit-button' style='background-color: #3498db; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 14px;'>Edit</button>
                                <button type='button' onclick='openDeleteModal(\"$supplierId\")' class='delete-button' style='background-color: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 14px;'>Delete</button>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No suppliers found</td></tr>";
                    }
                    $result->free();
                    ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?>">&laquo; Previous</a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- End of Supplier Table -->

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

    <!-- Add Supplier Modal -->
    <div id="add-supplier-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeAddSupplierModal()">&times;</span>
            <h2>Add Supplier</h2>
            <form id="add-supplier-form" method="post" action="add_supplier.php" onsubmit="return validateAddSupplierForm()">
                <div class="form-group">
                    <label for="supplier_name">Supplier Name:</label>
                    <input type="text" id="supplier_name" name="supplierName">
                    <div id="supplier-name-msg"></div>
                </div>
                <div class="form-group">
                    <label for="contact_name">Contact Name:</label>
                    <input type="text" id="contact_name" name="contactName" >
                    <div id="contact-name-msg"></div>
                </div>
                <div class="form-group">
                    <label for="contact_email">Contact Email:</label>
                    <input type="email" id="contact_email" name="contactEmail">
                    <div id="contact-email-msg"></div>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number:</label>
                    <input type="text" id="phone_number" name="phoneNumber">
                    <div id="phone-number-msg"></div>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input id="address" name="address"></input>
                    <div id="address-msg"></div>
                </div>
                <button type="submit" class="add-btn">Add Supplier</button>
            </form>

        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div id="edit-supplier-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Supplier</h2>
            <form id="edit-supplier-form" method="post" action="update_supplier.php" onsubmit="return validateEditSupplierForm()">
                <input type="hidden" id="edit_supplier_id" name="supplierId">
                <div class="form-group">
                    <label for="edit_supplier_name">Supplier Name:</label>
                    <input type="text" id="edit_supplier_name" name="supplierName">
                    <div id="edit-supplier-name-msg"></div>
                </div>
                <div class="form-group">
                    <label for="edit_contact_name">Contact Name:</label>
                    <input type="text" id="edit_contact_name" name="contactName">
                    <div id="edit-contact-name-msg"></div>
                </div>
                <div class="form-group">
                    <label for="edit_contact_email">Contact Email:</label>
                    <input type="email" id="edit_contact_email" name="contactEmail">
                    <div id="edit-contact-email-msg"></div>
                </div>
                <div class="form-group">
                    <label for="edit_phone_number">Phone Number:</label>
                    <input type="text" id="edit_phone_number" name="phoneNumber" >
                    <div id="edit-phone-number-msg"></div>
                </div>
                <div class="form-group">
                    <label for="edit_address">Address:</label>
                    <input id="edit_address" name="address"></input>
                    <div id="edit-address-msg"></div>
                </div>
                <button type="submit" class="update-btn">Update Supplier</button>
            </form>

        </div>
    </div>

<!-- Delete Modal Popup -->
<div id="delete-supplier-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeDeleteModal()">&times;</span>
        <div class="content">
            <p>Are you sure you want to delete this supplier?</p>
            <form id="delete-supplier-form" method="post">
                <input type="hidden" id="delete_supplier_id" name="supplierId">
                <button type="submit" name="action" value="delete" class="update-btn" style="background-color: #e74c3c;">Delete</button>
                <button type="button" id="cancel-btn">Cancel</button>
            </form>
        </div>
    </div>
</div>
<!-- End of Delete Modal Popup -->

<!-- Js Code -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script> <!-- Add datepicker JS -->

<script src="<?php echo BASE_URL; ?>dist/js/form-validations.js"></script>

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

    function openAddSupplierModal() {
        document.getElementById('add-supplier-modal').style.display = 'block';
    }

    function closeAddSupplierModal() {
        document.getElementById('add-supplier-modal').style.display = 'none';
    }

    // Close the modal if the user clicks outside of it
    window.onclick = function(event) {
        const modal = document.getElementById('add-supplier-modal');
        if (event.target === modal) {
            closeAddSupplierModal();
        }
    };  

    document.addEventListener('DOMContentLoaded', function() {
        const deleteSupplierModal = document.getElementById('delete-supplier-modal');
        const closeDeleteModalBtn = document.querySelector('#delete-supplier-modal .close');
        const cancelDeleteBtn = document.querySelector('#delete-supplier-modal #cancel-btn');
        const deleteSupplierForm = document.getElementById('delete-supplier-form');
        const modalSupplierId = document.getElementById('delete_supplier_id');

        const editSupplierModal = document.getElementById('edit-supplier-modal');
        const closeEditModalBtn = document.querySelector('#edit-supplier-modal .close');
        const editSupplierForm = document.getElementById('edit-supplier-form');
        const editSupplierId = document.getElementById('edit_supplier_id');

        // Open delete modal
        window.openDeleteModal = function(supplierId) {
            modalSupplierId.value = supplierId;
            deleteSupplierModal.style.display = 'block';
        };

        // Close delete modal
        closeDeleteModalBtn.addEventListener('click', function() {
            deleteSupplierModal.style.display = 'none';
        });

        cancelDeleteBtn.addEventListener('click', function() {
            deleteSupplierModal.style.display = 'none';
        });

        // Close edit modal
        closeEditModalBtn.addEventListener('click', function() {
            editSupplierModal.style.display = 'none';
        });

        // Open edit modal
        window.openEditModal = function(supplierId) {
            // Load supplier data into the form
            fetch(`get_supplier_details.php?supplierId=${supplierId}`)
                .then(response => response.json())
                .then(data => {
                    editSupplierId.value = data.supplier_id;
                    document.getElementById('edit_supplier_name').value = data.supplier_name;
                    document.getElementById('edit_contact_name').value = data.contact_name;
                    document.getElementById('edit_contact_email').value = data.contact_email;
                    document.getElementById('edit_phone_number').value = data.phone_number;
                    document.getElementById('edit_address').value = data.address;
                    editSupplierModal.style.display = 'block';
                })
                .catch(error => console.error('Error fetching supplier data:', error));
        };

        // Close add modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target === document.getElementById('add-supplier-modal')) {
                closeAddSupplierModal();
            }
        };
    });


    // Function to open the delete modal and set the supplier ID in the hidden field
    function openDeleteModal(supplierId) {
        // Display the modal
        document.getElementById('delete-supplier-modal').style.display = 'block';

        // Set the supplier ID in the hidden input field
        document.getElementById('delete_supplier_id').value = supplierId;
    }

    // Function to close the modal
    function closeDeleteModal() {
        // Hide the modal
        document.getElementById('delete-supplier-modal').style.display = 'none';
    }

    // Handle cancel button inside the form to close the modal
    document.getElementById('cancel-btn').addEventListener('click', function() {
        closeDeleteModal();
    });     


</script>
</body>
</html>
