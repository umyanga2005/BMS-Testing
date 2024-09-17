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
    // Get the product ID from the POST data
    $productId = isset($_POST['productId']) ? trim($_POST['productId']) : '';

    // Check if the product ID is not empty
    if (!empty($productId)) {
        // Prepare the DELETE query
        if ($stmt = $mysqli->prepare("DELETE FROM tg_products WHERE productId = ?")) {
            // Bind the product ID parameter as a string
            $stmt->bind_param("s", $productId);

            // Execute the query
            if ($stmt->execute()) {
                // Redirect to the same page to reflect changes
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                // Error message if execution fails
                echo "Error deleting product: " . $stmt->error;
            }

            // Close the prepared statement
            $stmt->close();
        } else {
            // Error message if statement preparation fails
            echo "Error preparing statement: " . $mysqli->error;
        }
    } else {
        echo "Invalid product ID.";
    }
}

// Fetch product data
$sql = "SELECT `productId`, `productImage`, `productName`, `category`, `stockQuantity`, `price` FROM `tg_products`";
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

// Get the total number of products
$totalQuery = "SELECT COUNT(*) AS total FROM `tg_products`";
$totalResult = $mysqli->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalProducts = $totalRow['total'];

// Calculate total pages
$totalPages = ceil($totalProducts / $rowsPerPage);

// Fetch the products for the current page
$query = "SELECT `id`, `productId`, `productImage`, `productName`, `category`, `stockQuantity`, `price` 
          FROM `tg_products` 
          LIMIT $rowsPerPage OFFSET $offset";
$result = $mysqli->query($query);
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" type="image/x-icon">
    <title>Products - BMS</title>
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
            <a href="<?php echo ADMIN_URL; ?>admin-Customers">
                <span class="material-icons-sharp">person_outline</span>
                <h3>Customers</h3>
            </a>
            <a href="<?php echo ADMIN_URL; ?>admin-Products" class="active">
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
        <h1>Products</h1>
        <!-- Products Table -->
        <div class="customer-list">
            <div style="display: flex; align-items: center;">
                <h2>Product List</h2>
                <button class="refresh-button" onclick="window.location.reload();">
                    <span class="material-icons-sharp">refresh</span>
                </button>
                <button id="add-product-btn" class="refresh-button" onclick="openAddProductModal()">
                    <span class="material-icons-sharp">add</span>
                </button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Stock Quantity</th>
                        <th>Price (Per Item)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $productId = htmlspecialchars($row['productId']);
                            $productImage = htmlspecialchars($row['productImage']);
                            $productName = htmlspecialchars($row['productName']);
                            $category = htmlspecialchars($row['category']);
                            $stockQuantity = htmlspecialchars($row['stockQuantity']);
                            $price = htmlspecialchars($row['price']);

                            echo "<tr>";
                            echo "<td>$productId</td>";
                            echo "<td><center><img src='$productImage' alt='Product Photo' style='width: 40px; height: 40px; border-radius: 50%;'></center></td>";
                            echo "<td>$productName</td>";
                            echo "<td>$category</td>";
                            echo "<td>$stockQuantity</td>";
                            echo "<td>Rs.$price.00</td>";
                            echo "<td>
                                <button type='button' onclick='openEditModal(\"$productId\")' class='edit-button' style='background-color: #3498db; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 14px;'>Edit</button>
                                <button type='button' onclick='openDeleteModal(\"$productId\")' class='delete-button' style='background-color: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 14px;'>Delete</button>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No products found</td></tr>";
                    }
                    $result->free();
                    ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?>" class="prev-page">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $currentPage) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?>" class="next-page">Next</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- End of Products Table -->

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

<!-- Add Product Modal Popup -->
<div id="add-product-modal" class="modal" style="display: none; margin: 0 auto; overflow-y: scroll; overflow-x: hidden;">
    <div class="modal-content">
        <span class="close-add-product">&times;</span>
        <div class="content">
            <h2>Add New Product</h2>
            <form id="add-product-form" action="add_product.php" method="POST" enctype="multipart/form-data">
                <img src="../dist/src/plus.png" class="profile-logo" id="product-pic" style="border-radius: 50%; margin: 0 auto 10px; width: 100px; height: 100px;" alt="Product Image Preview">

                <label for="input-file" style="display: block; width: 180px; text-align: center; background-color: var(--color-danger); color: var(--color-dark); padding: 12px; margin: 10px auto; border-radius: 5px; cursor: pointer;">Upload Product Image</label>
                <input type="file" accept="image/jpeg, image/png, image/jpg" id="input-file" name="product_image" style="display: none;">
                <span id="product-image-msg"></span>

                <label for="product_name">Product Name:</label>
                <input type="text" id="product_name" name="productName" >
                <span id="product-name-msg"></span>

                <label for="category">Category:</label>
                <input type="text" id="category" name="category" >
                <span id="category-msg"></span>

                <label for="stockQuantity">Stock Quantity:</label>
                <input type="number" id="stockQuantity" name="stockQuantity" >
                <span id="stock-quantity-msg"></span>

                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" >
                <span id="price-msg"></span>


                <button type="button" id="cancel-add-product-btn" class="cancel-add-product-btn">Cancel</button>
                <button type="submit" class="add-product-btn">Add Product</button>
            </form>
        </div>
    </div>
</div>
<!-- End of Add Product Modal Popup -->



<!-- Delete Modal Popup -->
<div id="delete-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close"></span>
        <div class="content">
            <p>Are you sure you want to delete this Product?</p>
            <form id="delete-form" action="" method="POST">
                <input type="hidden" name="productId" id="modal-product-id">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class='delete-button' style='background-color: #e74c3c; color: white;'>Delete</button>
                <button type="button" id="cancel-btn">Cancel</button>
            </form>
        </div>
    </div>
</div>
<!-- End of Delete Modal Popup -->

<!-- Edit Modal Popup -->
<div id="edit-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-edit"></span>
        <div class="content">
            <h2>Edit Product</h2>
            <form id="edit-form" action="update_product.php" method="POST">
                <input type="hidden" name="productId" id="edit-product-id">
                <label for="edit-product_name">Product Name:</label>
                <input type="text" id="edit-product_name" name="productName" >

                <label for="edit-category">Category:</label>
                <input type="text" id="edit-category" name="category">

                <label for="edit-stockQuantity">Stock Quantity:</label>
                <input type="number" id="edit-stockQuantity" name="stockQuantity" >

                <label for="edit-price">Price:</label>
                <input type="number" step="0.01" id="edit-price" name="price" >

                <button type="submit">Update</button>
                <button type="button" id="cancel-edit-btn">Cancel</button>
            </form>
        </div>
    </div>
</div>
<!-- End of Edit Modal Popup -->

<!-- Js Code -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script> <!-- Add datepicker JS -->

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
        const addProductModal = document.getElementById('add-product-modal');
        const openAddProductModalBtn = document.getElementById('add-product-btn'); // Button to open modal
        const closeAddProductModal = document.querySelector('#add-product-modal .close-add-product');
        const cancelAddProductBtn = document.getElementById('cancel-add-product-btn');

        // Function to open add product modal
        window.openAddProductModal = function() {
            addProductModal.style.display = 'block';
        };

        // Close add product modal
        if (closeAddProductModal) {
            closeAddProductModal.addEventListener('click', function() {
                addProductModal.style.display = 'none';
            });
        }

        if (cancelAddProductBtn) {
            cancelAddProductBtn.addEventListener('click', function() {
                addProductModal.style.display = 'none';
            });
        }

        window.addEventListener('click', function(event) {
            if (event.target === addProductModal) {
                addProductModal.style.display = 'none';
            }
        });

        // Handle add product form submission
        const addProductForm = document.getElementById('add-product-form');
        if (addProductForm) {
            addProductForm.addEventListener('submit', function(event) {
                if (!validateAddProductForm()) {
                    event.preventDefault(); // Prevent form submission if validation fails
                }
            });
        }

        // Add click event listener to the button for opening the modal
        if (openAddProductModalBtn) {
            openAddProductModalBtn.addEventListener('click', openAddProductModal);
        }
    });



    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('delete-modal');
        const closeModal = document.querySelector('#delete-modal .close');
        const cancelBtn = document.getElementById('cancel-btn');
        const deleteForm = document.getElementById('delete-form');
        const modalProductId = document.getElementById('modal-product-id');

        const editModal = document.getElementById('edit-modal');
        const closeEditModal = document.querySelector('#edit-modal .close-edit');
        const cancelEditBtn = document.getElementById('cancel-edit-btn');
        const editProductId = document.getElementById('edit-product-id');

        // Function to open delete modal and set product ID
        window.openDeleteModal = function(productId) {
            modalProductId.value = productId;
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

        // Function to open edit modal and set product details
        window.openEditModal = function(productId) {
            fetch('get_product_details.php?id=' + productId)
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        document.getElementById('edit-product-id').value = productId;
                        document.getElementById('edit-product_name').value = data.productName;
                        document.getElementById('edit-category').value = data.category;
                        document.getElementById('edit-stockQuantity').value = data.stockQuantity;
                        document.getElementById('edit-price').value = data.price;
                        editModal.style.display = 'block';
                    } else {
                        console.error('Error fetching product details:', data.error);
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

    let profilePic = document.getElementById("product-pic");
    let inputFile = document.getElementById("input-file");

    inputFile.onchange = function() {
        profilePic.src = URL.createObjectURL(inputFile.files[0]);
    };
</script>
</body>
</html>
