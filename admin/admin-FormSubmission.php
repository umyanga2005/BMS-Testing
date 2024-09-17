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

// Handle contact form submission deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $submissionId = isset($_POST['submissionId']) ? (int)$_POST['submissionId'] : 0;

    if ($submissionId > 0) {
        // Prepare and execute the DELETE query
        $stmt = $mysqli->prepare("DELETE FROM tg_contact_form WHERE id = ?");
        $stmt->bind_param("i", $submissionId);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page to reflect changes
            exit();
        } else {
            echo "Error deleting submission: " . $stmt->error;
        }

        $stmt->close();
    }
}



// Pagination setup
$limit = 5; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total number of contact form submissions for pagination
$totalSubmissionsSql = "SELECT COUNT(*) AS total FROM tg_contact_form";
$totalSubmissionsResult = $mysqli->query($totalSubmissionsSql);
$totalSubmissions = $totalSubmissionsResult->fetch_assoc()['total'];
$totalPages = ceil($totalSubmissions / $limit);

// Fetch contact form submissions with pagination
$sql = "SELECT `id`, `name`, `email`, `subject`, `message`, `submitted_at`
        FROM `tg_contact_form`
        LIMIT $limit OFFSET $offset";
$result = $mysqli->query($sql);

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
    <title>Form Submission - BMS</title>
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
            <a href="<?php echo ADMIN_URL; ?>admin-FormSubmission" class="active">
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
        <h1>Form Submissions</h1>
        <!-- Orders Table -->
        <div class="customer-list">
            <div style="display: flex; align-items: center;">
                <h2>Form Submission List</h2>
                <button class="refresh-button" onclick="window.location.reload();">
                    <span class="material-icons-sharp">refresh</span>
                </button>
            </div>
            <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Submitted At</th>
                    <th>Actions</th>
                </tr>>
            </thead>
            <tbody>
                <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $id = htmlspecialchars($row['id']);
                            $name = htmlspecialchars($row['name']);
                            $email = htmlspecialchars($row['email']);
                            $subject = htmlspecialchars($row['subject']);
                            $message = htmlspecialchars($row['message']);
                            $submittedAt = htmlspecialchars($row['submitted_at']);

                            echo "<tr>";
                            echo "<td>$id</td>";
                            echo "<td>$name</td>";
                            echo "<td>$email</td>";
                            echo "<td>$subject</td>";
                            echo "<td>$message</td>";
                            echo "<td>$submittedAt</td>";
                            echo "<td>
                                <button type='button' onclick='openDeleteModal(\"$id\")' class='delete-button' style='background-color: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 14px;'>Delete</button>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No submissions found</td></tr>";
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
            <p>Are you sure you want to delete this submission?</p>
            <form id="delete-form" action="" method="POST">
                <input type="hidden" name="submissionId" id="modal-order-id">
                <input type="hidden" name="action" value="delete">
                <button type="submit" style="background-color: #e74c3c;">Yes, Delete</button>
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

        window.openDeleteModal = function(submissionId) {
            document.getElementById('modal-order-id').value = submissionId;
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
