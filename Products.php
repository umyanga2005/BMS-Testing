<?php
// Include the config file for database connection and other settings
require_once(dirname(__FILE__) . '/config.php');

// Start session to access logged-in user data
session_start();

// Check if the user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $username = $_SESSION['customer_username'];

    // Establish a connection to the database
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to get customer details using the logged-in username
    $sql = "SELECT customer_id, customer_username, customer_Name, customer_address, photo FROM tg_customers WHERE customer_username = ?";

    // Prepare statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the username to the query
        $stmt->bind_param('s', $username);
        // Execute the query
        $stmt->execute();
        // Get the result
        $result = $stmt->get_result();

        // Check if a record is found
        if ($result->num_rows > 0) {
            // Fetch the customer data
            $row = $result->fetch_assoc();
            $Customer_ID = $row['customer_id'];
            $Customer_Name = $row['customer_Name'];
            $Customer_Address = $row['customer_address'];
            $profile_pic = $row['photo'];
        } else {
            echo "No customer found with that username.";
        }

        // Close statement
        $stmt->close();
    }

    // Function to fetch products from the database with pagination
    function fetchProducts($connection, $limit, $offset) {
        $query = "SELECT `productId`, `productImage`, `productName`, `category`, `price`
                  FROM `tg_products` LIMIT $limit OFFSET $offset";
        $result = $connection->query($query);

        if ($result->num_rows > 0) {
            return $result;
        } else {
            return false;
        }
    }

    // Function to count the total number of products for pagination
    function countProducts($connection) {
        $query = "SELECT COUNT(*) AS total FROM `tg_products`";
        $result = $connection->query($query);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    // Set the number of items per page for pagination
    $items_per_page = 6; // You can adjust this number

    // Get the current page number from the URL, default to 1 if not set
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $items_per_page;

    // Fetch products and total product count
    $products = fetchProducts($conn, $items_per_page, $offset);
    $total_products = countProducts($conn);

    // Calculate total pages needed for pagination
    $total_pages = ceil($total_products / $items_per_page);

    // Fetch user data for additional info
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Prepare and execute query to fetch additional user data
        $stmt = $conn->prepare("SELECT name, address FROM customers WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($customerName, $customerAddress);
        $stmt->fetch();
        $stmt->close();
    } else {
        $customerName = "";
        $customerAddress = "";
    }

    // Prepare and execute query to get customer details
    $stmt = $conn->prepare("SELECT `customer_id`, `customer_username`, `customer_Name`, `customer_address` FROM `tg_customers` WHERE `customer_username` = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        $customerID = $customer['customer_id'];

        // Prepare and execute query to get order count for the customer
        $stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM `tg_orders` WHERE `customerID` = ?");
        $stmt->bind_param("i", $customerID);
        $stmt->execute();
        $orderResult = $stmt->get_result();
        $orderCount = $orderResult->fetch_assoc()['order_count'];
    } else {
        $orderCount = 0; // Default to 0 if no customer found
    }

    // Close connection
    $conn->close();

    // Display a welcome alert if it's the first login
    if (!isset($_SESSION['welcome_shown'])) {
        echo "<script>alert('WELCOME TO OVEN CRUST BAKERY - " . $_SESSION['customer_username'] . "');</script>";
        $_SESSION['welcome_shown'] = true;
    }
} else {
    echo "User is not logged in.";
    echo "<script>console.log('User not logged in.');</script>";
}
?>


<!DOCTYPE html>
   <html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">

      <!--=============== FAVICON ===============-->
      <link rel="shortcut icon" href="<?php echo BASE_URL; ?>dist/src/OCBSLogo.png" type="image/x-icon">

      <!--=============== REMIXICONS ===============-->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.7.0/remixicon.css">

      <!--=============== CSS ===============-->
      <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/main.css">

      <title>Oven Crust Bakery Shop</title>

      <style>
         .pagination {
            margin: 20px 0;
            display: flex;
            justify-content: flex-end; /* Align to the right */
            }

            .pagination a {
            margin: 0 5px !important;
            padding: 8px 12px;
            color: white !important;
            text-decoration: none;
            border-radius: 4px;
            }

            .pagination a.active {
            background-color: hsl(28, 84%, 58%);
            }

            .pagination a:hover {
            background-color: hsl(28, 84%, 58%);
            }

            .pagination a.prev-page, .pagination a.next-page {
            font-weight: bold;
            }

            .icon-container {
            position: relative;
            display: inline-block;
         }

         .icon-container i {
            font-size: 24px !important;
            margin: 0;
         }

         .icon-container .custom-number {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: red; /* Customize the background color as needed */
            color: white; /* Customize the text color as needed */
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px; /* Customize the font size as needed */
            font-weight: bold;
         }

         .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 500;
            display: none; /* Initially hidden */
         }
         
         .popup {
            position: fixed;
            display: none;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            width: 450px;
            z-index: 1000;

        }
        .popup h2 {
            margin: 1rem;
            text-align: center;
            margin-top: 0;
        }
        .form-group {
         margin-top: 1rem;
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #aaa;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        #orderForm select {
            width: 100%;
            padding: 8px;
            border: 1px solid #444;
            background-color: #333;
            color: #fff;
            border-radius: 4px;
        }
        .button-group {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .submit-button {
            background-color: hsl(28, 84%, 58%);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            margin-right: 10px;
            cursor: pointer;
        }
        .close-button {
            background-color: hsla(347, 100%, 37%, 1);
            padding: 10px 20px;
            border-radius: 10px;
            margin-right: 10px;
            cursor: pointer;
        }
        .total-price-container {
            text-align: right;
            margin-top: 20px;
            padding: 10px;
            background-color: #3a3a3a;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .total-price-label {
            font-size: 14px;
            color: #aaa;
        }
        .total-price-value {
            font-size: 24px;
            font-weight: bold;
            color: #00ff7f;
        }

      </style>
   </head>
   <body>
      <!--==================== HEADER ====================-->
      <header class="header" id="header">
         <nav class="nav container">
            <img src="<?php echo BASE_URL; ?>dist/src/img/favicon1.png" alt="logoimage" class="logo_image">
            <a href="#" class="nav__logo">Oven Crust Bakery</a>

            <div class="nav__menu" id="nav-menu">
               <ul class="nav__list grid">
                  <li class="nav__item">
                     <a href="<?php echo BASE_URL ?>#home" class="nav__link">Home</a>
                  </li>
                  <li class="nav__item">
                     <a href="<?php echo BASE_URL; ?>#about" class="nav__link">About</a>
                  <li class="nav__item">
                     <a href="#contact" class="nav__link">Contact Us</a>
                  </li>
                 <!-- Display login/register or logout based on session -->
                  <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true): ?>
                     <li class="nav__item">
                        <div class='user'>
                        <a href="<?php echo BASE_URL; ?>Profile" class="profile-link" style="font-weight: 600; text-decoration: none; color: #fff; border-radius: 5px;"><?php echo htmlspecialchars($username); ?></a>                       </div>
                     </li>
                     <li class="nav__item">
                        <a href="" style="font-weight: 600; text-decoration: none; color: #fff; border-radius: 5px;">
                        <div class="icon-container">
                           <i class="ri-shopping-cart-2-fill"></i>
                           <span class="custom-number"><?php echo htmlspecialchars($orderCount); ?></span>
                           </div>
                        </a>
                     </li>
                  <?php else: ?>
                     <li class="nav__item">
                        <a href="userLogin" class="nav__link"><b>Login</b></a>
                     </li>
                     <li class="nav__item">
                        <a href="userRegister" class="nav__link"><b>Register</b></a>
                     </li>
                  <?php endif; ?>

               </ul>

               <!-- Close button-->
               <div class="nav__close" id="nav-close">
                  <i class="ri-close-line"></i>
               </div>

               <img src="<?php echo BASE_URL; ?>dist/src/img/bread-4.png" alt="image" class="nav__img-1">
               <img src="<?php echo BASE_URL; ?>dist/src/img/bread-1.png" alt="image" class="nav__img-2">
            </div>

            <!-- Toggle button -->
            <div class="nav__toggle" id="nav-toggle">
               <i class="ri-menu-fill"></i>
         </nav>
         
      </header>

      <!--==================== MAIN ====================-->
      <main class="main">
         <!--==================== FAVORITES ====================-->
         <section class="favorite section" id="favorite">
            <h2 class="section__title">All Products</h2>

            <div class="Favourite__container container grid">
               <?php
               // Check if products were fetched and display them
               if ($products) {
                  while ($row = $products->fetch_assoc()) {
                     $productId = htmlspecialchars($row['productId']);
                     $productImage = htmlspecialchars($row['productImage']);
                     $productName = htmlspecialchars($row['productName']);
                     $category = htmlspecialchars($row['category']);
                     $price = htmlspecialchars($row['price']);

                     echo "
                     <article class='favorite__card'>
                        <img src='admin/$productImage' alt='Product Image' class='favorite__img'>
                        <div class='favorite__data'>
                           <h2 class='favorite__title'>$productName</h2>
                           <span class='favorite__subtitle'>$category</span>
                           <h3 class='favorite__price'>Rs. $price.00</h3>
                        </div>
                        <!-- Button -->
                        <button class='favorite__button button' data-product-name=' $productName' data-product-price='$price'>
                           <i class='ri-add-line'></i>
                        </button>
                     </article>";
                  }
               } else {
                  echo "<p>No products found</p>";
               }
               ?>
            </div>
            
            <!-- Pagination Links -->
            <div class="pagination">
               <?php if ($total_pages > 1): ?>
                  <?php if ($page > 1): ?>
                     <a href="?page=<?php echo $page - 1; ?>" class="prev-page">« Previous</a>
                  <?php endif; ?>

                  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                     <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                           <?php echo $i; ?>
                     </a>
                  <?php endfor; ?>

                  <?php if ($page < $total_pages): ?>
                     <a href="?page=<?php echo $page + 1; ?>" class="next-page">Next »</a>
                  <?php endif; ?>
               <?php endif; ?>

            </div>

         </section>

      </main>

      <!--==================== FOOTER ====================-->
      <footer class="footer" id="footer">
         <div class="footer__container container grid">
            <div>
               <img src="<?php echo BASE_URL; ?>dist/src/img/favicon1.png" alt="logoimage" class="logo_image">
            </div>
            <div>
               <a href="#" class="footer__logo">Oven Crust Bakery</a>
               <p>
                  We bake the best breads, cakes, and pastries <br> in the city.
               </p>
            </div>

            <div class="footer__content grid">
               <div>
                  <h3 class="footer__title">Address</h3>

                  <ul class="footer__list">
                     <li>
                        <address class="footer__info">Kaduwela Road<br> Colombo, Sri Lanka</address>
                     </li>

                     <li>
                        <address class="footer__info">9AM - 11PM</address>
                     </li>
                  </ul>
               </div>

               <div>
                  <h3 class="footer__title">Contact Us</h3>

                  <ul class="footer__list">
                     <li>
                        <address class="footer__info"> bakery@ovencrust.lk</address>
                     </li>
                     
                     <li>
                        <address class="footer__info">+94 11 234 5678</address>
                     </li>
                  </ul>
               </div>

               <div>
                  <h3 class="footer__title">Follow Us</h3>

                  <div>
                     <a href="https://www.facebook.com/" target="_blank">
                        <i class="ri-facebook-circle-line"></i>
                     </a>

                     <a href="https://www.instagram.com/" target="_blank">
                        <i class="ri-instagram-line"></i>
                     </a>

                     <a href="https://www.youtube.com/" target="_blank">
                        <i class="ri-youtube-line"></i>
                     </a>
                  </div>
               </div>
            </div>

            <img src="<?php echo BASE_URL; ?>dist/src/img/bread-4.png" alt="image" class="footer__img-1">
            <img src="<?php echo BASE_URL; ?>dist/src/img/bread-3.png" alt="image" class="footer__img-2">
         </div>

         <span class="footer__copy">
            &#169; All Rights Reserved by Oven Crust Bakery
         </span>
      </footer>

      <!-- Order Popup -->
      <div id="popup" class="popup hidden">
         <div class="popup-content">
            <h2>Order Item</h2>
            <p>Fill in the details below to order this item:</p>
            
            <form id="orderForm" action="process_order.php" method="POST">
               <div class="form-group">
                  <label for="customerName">Customer Name:</label>
                  <input type="text" id="customerName" name="customerName" value="<?php echo isset($Customer_Name) ? htmlspecialchars($Customer_Name) : ''; ?>" required>
                  <input type="hidden" id="customerID" name="customerID" value="<?php echo isset($Customer_ID) ? htmlspecialchars($Customer_ID) : ''; ?>" required>
               </div>

               <div class="form-group">
                  <label for="customerAddress">Customer Address:</label>
                  <input type="text" id="customerAddress" name="customerAddress" value="<?php echo isset($Customer_Address) ? htmlspecialchars($Customer_Address) : ''; ?>" required>
               </div>

               <div class="form-group">
                  <label for="productName">Product Name:</label>
                  <input type="text" id="productName" name="productName" readonly>
               </div>

               <div class="form-group">
                  <label for="quantity">Quantity:</label>
                  <input type="number" id="quantity" name="quantity" required>                  
               </div>

               <div class='total-price-container'>
                  <span class='total-price-label'>Total Price:</span><br>
                  <span class='total-price-value' id='totalPrice'>Rs 0.00</span>
                  <input type="hidden" id="totalAmount" name="totalAmount" value="0.00">
               </div>

               <div class="form-group">
                  <label for="status">Status:</label>
                  <select id="status" name="status" required>
                        <option value="Completed">Completed</option>
                  </select>
               </div>

               <!-- Hidden field for date and time -->
               <input type="hidden" id="orderDateTime" name="orderDateTime">

               <button type="submit" class="submit-button">Submit</button>
               <button type="button" id="closePopup" class="close-button">Close</button>
            </form>

         </div>
      </div>

      <!-- Background Overlay -->
      <div id="overlay" class="overlay hidden"></div>

      <script>
      // Function to show the popup
      function showPopup(productName) {
         // Set the product details in the popup
         document.getElementById('productName').value = productName; // or fetch product details based on productId
         document.getElementById('popup').style.display = 'block';
         document.getElementById('overlay').style.display = 'block';
         document.getElementById('perPrice').value = perPrice;
      }

      // Attach event listeners to all favorite buttons
      document.querySelectorAll('.favorite__button').forEach(button => {
         button.addEventListener('click', function() {
            const productName = this.getAttribute('data-product-name');
            showPopup(productName);
         });
      });

      // Close the popup
      document.getElementById('closePopup').addEventListener('click', function() {
         document.getElementById('popup').style.display = 'none';
         document.getElementById('overlay').style.display = 'none';
      });

      // Variables to store product price and quantity
      let productPrice = 0;

      // Attach event listeners to all favorite buttons
      document.querySelectorAll('.favorite__button').forEach(button => {
         button.addEventListener('click', function() {
            productPrice = parseFloat(this.getAttribute('data-product-price'));
            updateTotalPrice(); // Update the total price when a favorite button is clicked
         });
      });

      // Attach event listener to the quantity input field
      document.getElementById('quantity').addEventListener('input', updateTotalPrice);

      // Function to update total price
      function updateTotalPrice() {
         const quantityInput = document.getElementById('quantity');
         const quantity = parseInt(quantityInput.value) || 0; // Default to 0 if invalid

         // Calculate total price
         const totalPrice = productPrice * quantity;

         // Update the total price display
         document.getElementById('totalPrice').textContent = `Rs ${totalPrice.toFixed(2)}`;
         document.getElementById('totalAmount').value = totalPrice.toFixed(2);

      }

      document.addEventListener('DOMContentLoaded', function() {
        const orderForm = document.getElementById('orderForm');
        const orderDateTimeInput = document.getElementById('orderDateTime');

        function updateDateTime() {
            const now = new Date();
            const formattedDate = now.toLocaleDateString(); 
            const formattedTime = now.toLocaleTimeString();
            return `${formattedDate} ${formattedTime}`;
        }

        orderForm.addEventListener('submit', function() {
            orderDateTimeInput.value = updateDateTime();
        });
    });

   </script>

      <!--========== SCROLL UP ==========-->
      <a href="#" class="scrollup" id="scroll-up">
         <i class="ri-arrow-up-line"></i>
      </a>

      <!--=============== SCROLLREVEAL ===============-->
      <script src="<?php echo BASE_URL; ?>dist/js/scrollreveal.min.js"></script>

      <!--=============== MAIN JS ===============-->
      <script src="<?php echo BASE_URL; ?>dist/js/main.js"></script>
   </body>
</html>

