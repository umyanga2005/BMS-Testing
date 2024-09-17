<?php
// Include the config file for database connection and other settings
require_once(dirname(__FILE__) . '/config.php');

// Start session to access logged-in user data
session_start();

// Check if the user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Get the logged-in user's username from the session
    $username = $_SESSION['customer_username'];

    // Establish a connection to the database
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute query to get customer details
    $stmt = $conn->prepare("SELECT customer_id, customer_username, customer_Name, customer_address FROM tg_customers WHERE customer_username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the customer data
        $row = $result->fetch_assoc();
        $Customer_ID = $row['customer_id'];
        $Customer_Name = $row['customer_Name'];
        $Customer_Address = $row['customer_address'];
    } else {
        echo "No customer found with that username.";
    }

    // Close the statement
    $stmt->close();

    // Check if welcome message should be shown
    if (!isset($_SESSION['welcome_shown'])) {
        echo "<script>alert('WELCOME TO OVEN CRUST BAKERY - " . $_SESSION['customer_username'] . "');</script>";
        $_SESSION['welcome_shown'] = true;
    }

    // Prepare and execute query to get order count for the customer
    $stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM tg_orders WHERE customerID = ?");
    $stmt->bind_param("i", $Customer_ID);
    $stmt->execute();
    $orderResult = $stmt->get_result();
    $orderCount = $orderResult->fetch_assoc()['order_count'];

    // Close the statement and connection
    $stmt->close();
    $conn->close();
    
}

// Set the number of items per page for pagination
$items_per_page = 6; // You can adjust this number

// Get the current page number from the URL, default to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Function to fetch products from the database with pagination
function fetchProducts($connection, $limit, $offset) {
    $query = "SELECT `productId`, `productImage`, `productName`, `category`, `price` FROM `tg_products` LIMIT $limit OFFSET $offset";
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

// Establish the database connection using settings from the config file
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch products and total product count
$products = fetchProducts($mysqli, $items_per_page, $offset);
$total_products = countProducts($mysqli);

// Calculate total pages needed for pagination
$total_pages = ceil($total_products / $items_per_page);

// Close the mysqli connection
$mysqli->close();
?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
   <script>alert('Message sent successfully!');</script>
<?php endif; ?>

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
         /* Hover effect for the profile link */
         .profile-link:hover, 
         .logout-link:hover {
            color: hsl(28, 84%, 58%);
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
                     <a href="#home" class="nav__link active-link">Home</a>
                  </li>
                  <li class="nav__item">
                     <a href="#new" class="nav__link">New</a>
                  </li>
                  <li class="nav__item">
                     <a href="#about" class="nav__link">About</a>
                  </li>
                  <li class="nav__item">
                     <a href="#favorite" class="nav__link">Favorites</a>
                  </li>
                  <li class="nav__item">
                     <a href="#visit" class="nav__link">Visit Us</a>
                  </li>
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
         <!--==================== HOME ====================-->
         <section class="home section" id="home">
            <img src="<?php echo BASE_URL; ?>dist/src/img/home-bg.jpg" alt="" class="home__bg">
            <div class="home__shadow"></div>

            <div class="home__container container grid">
               <div class="home__data">
                  <h1 class="home__title">
                     Select The Best <br> Bread
                  </h1>

                  <a href="#new" class="button">New Products</a>
                  
                  <a href="<?php echo BASE_URL; ?>OrderForm" class="button" target="_blank">Special Order</a>

                  <img src="<?php echo BASE_URL; ?>dist/src/img/bread-1.png" alt="" class="home__bread">
               </div>

               <div class="home__image">
                  <img src="<?php echo BASE_URL; ?>dist/src/img/home-bread2.png" alt="image" class="home__img">
               </div>

               <footer class="home__footer">
                  <div class="home__location">
                     <i class="ri-map-pin-line"></i>
                     <span class="home__text">Kaduwela Road <br> Colombo, Sri Lanka</span>
                  </div>

                  <div class="home__social">
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
         </section>

         <!--==================== NEW ====================-->
         <section class="new section" id="new">
            <h2 class="section__title">New Products</h2>

            <div class="new__container container grid">
               <div class="new__content grid">
                  <article class="new__card">
                     <div class="new__data">
                        <h2 class="new__title">Chocolate Cake</h2>
                        <p class="new__description">
                           Rich and moist, made with the finest cocoa 
                           and topped with creamy chocolate frosting.
                        </p>
                     </div>

                     <img src="<?php echo BASE_URL; ?>dist/src/img/new-bread-5.png" alt="" class="new__img">
                  </article>

                  <article class="new__card">
                     <div class="new__data">
                        <h2 class="new__title">Whole Grain Bread</h2>
                        <p class="new__description">
                           Crispy and homemade prepared 
                           from organic yeast-free flour.
                        </p>
                     </div>

                     <img src="<?php echo BASE_URL; ?>dist/src/img/new-bread-2.png" alt="" class="new__img">
                  </article>

                  <article class="new__card">
                     <div class="new__data">
                        <h2 class="new__title">Butter Pastry</h2>
                        <p class="new__description">
                           Light and flaky, made with real butter 
                           for that perfect taste.
                        </p>
                     </div>

                     <img src="<?php echo BASE_URL; ?>dist/src/img/new-bread-4.png" alt="" class="new__img">
                  </article>
               </div>

               <a href="<?php echo BASE_URL; ?>Products" class="new__button button">See More</a>
               
            </div>
         </section>

         <!--==================== ABOUT ====================-->
         <section class="about section" id="about">
            <div class="about__container container grid">
               <div class="about__data">
                  <h2 class="section__title">About Us</h2>

                  <p class="about__description">
                     At Oven Crust Bakery, we craft all our breads, cakes, 
                     and pastries with a deep respect for tradition. We use only the 
                     finest local ingredients, ensuring that every bite is fresh, flavorful, 
                     and true to Sri Lankan heritage. Whether you're in the mood for a hearty loaf, 
                     a sweet treat, or something in between, we've got you covered.
                  </p>

                  <a href="<?php echo BASE_URL; ?>AboutUs" class="button">Know More</a>

                  <img src="<?php echo BASE_URL; ?>dist/src/img/bread-2.png" alt="image" class="about__bread">
               </div>

               <img src="<?php echo BASE_URL; ?>dist/src/img/about-bread.png" alt="about__image" class="about__img">
            </div>
            
         </section>

         <!--==================== VISIT ====================-->
         <section class="visit section" id="visit">
               <img src="<?php echo BASE_URL; ?>dist/src/img/visit-bg.jpg" alt="image" class="visit__bg">
               <div class="visit__shadow"></div>
                  <div class="visit__content container grid">
                     <div class="visit__data">
                        <h2 class="section__title">Visit Us</h2>

                        <p class="visit__description">
                           Experience the taste of freshly baked goods.
                           We look forward to welcoming you to our bakery in Colombo.
                        </p>
   
                        <a href="https://maps.app.goo.gl/bsPo5dmRfh7bW7q87" target="_blank" class="button">See Location</a>
                     </div>
                  </div>
         </section>
      </main>

      <!--==================== Contact Form ====================-->
      <section class="contact section" id="contact">
         <h2 class="section__title">Contact Us</h2>
         <form id="contactForm" action="submit_contact.php" method="POST" class="contact__form container grid">
            <div class="contact__inputs grid">
               <div class="contact__content">
                  <label for="name" class="contact__label">Name</label>
                  <input type="text" id="name" name="name" class="contact__input" placeholder="Your Name" />
                  <div id="nameMsg" class="validation-message"></div>
               </div>
               <div class="contact__content">
                  <label for="email" class="contact__label">Email</label>
                  <input type="email" id="email" name="email" class="contact__input" placeholder="Your Email" />
                  <div id="emailMsg" class="validation-message"></div>
               </div>
               <div class="contact__content">
                  <label for="subject" class="contact__label">Subject</label>
                  <input type="text" id="subject" name="subject" class="contact__input" placeholder="Subject" />
                  <div id="subjectMsg" class="validation-message"></div>
               </div>
               <div class="contact__content">
                  <label for="phone" class="contact__label">Phone</label>
                  <input type="text" id="phone" name="phone" class="contact__input" placeholder="Your Phone Number" />
                  <div id="phoneMsg" class="validation-message"></div>
               </div>
            </div>
            <div class="contact__content">
               <label for="message" class="contact__label">Message</label>
               <textarea id="message" name="message" class="contact__input" placeholder="Your Message"></textarea>
               <div id="messageMsg" class="validation-message"></div>
            </div>
            <button type="submit" class="button">Send Message</button>
         </form>
      </section>

      <!--==================== FOOTER ====================-->
      <footer class="footer">
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

      <!--========== SCROLL UP ==========-->
      <a href="#" class="scrollup" id="scroll-up">
         <i class="ri-arrow-up-line"></i>
      </a>

      <!--=============== SCROLLREVEAL ===============-->
      <script src="<?php echo BASE_URL; ?>dist/js/scrollreveal.min.js"></script>

      <!--=============== MAIN JS ===============-->
      <script src="<?php echo BASE_URL; ?>dist/js/main.js"></script>      
      
      <!--=============== Form Validation JS ===============-->
      <script>
         // validate_contact.js
         document.addEventListener('DOMContentLoaded', () => {
         const form = document.getElementById('contactForm');

         form.addEventListener('submit', (event) => {
            let valid = true;

            // Name validation
            const nameInput = document.getElementById('name');
            const nameMsg = document.getElementById('nameMsg');
            if (nameInput.value.trim() === '') {
               nameMsg.innerHTML = 'Name is required.';
               nameMsg.style.color = 'red';
               valid = false;
            } else {
               nameMsg.innerHTML = '';
            }

            // Email validation
            const emailInput = document.getElementById('email');
            const emailMsg = document.getElementById('emailMsg');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailInput.value)) {
               emailMsg.innerHTML = 'Invalid email address.';
               emailMsg.style.color = 'red';
               valid = false;
            } else {
               emailMsg.innerHTML = '';
            }

            // Subject validation
            const subjectInput = document.getElementById('subject');
            const subjectMsg = document.getElementById('subjectMsg');
            if (subjectInput.value.trim() === '') {
               subjectMsg.innerHTML = 'Subject is required.';
               subjectMsg.style.color = 'red';
               valid = false;
            } else {
               subjectMsg.innerHTML = '';
            }

            // Phone validation
            const phoneInput = document.getElementById('phone');
            const phoneMsg = document.getElementById('phoneMsg');
            const phonePattern = /^\+94[0-9]{9}$/;
            if (!phonePattern.test(phoneInput.value)) {
               phoneMsg.innerHTML = 'Invalid phone number. Use +94 followed by 9 digits.';
               phoneMsg.style.color = 'red';
               valid = false;
            } else {
               phoneMsg.innerHTML = '';
            }

            // Message validation
            const messageInput = document.getElementById('message');
            const messageMsg = document.getElementById('messageMsg');
            if (messageInput.value.trim() === '') {
               messageMsg.innerHTML = 'Message is required.';
               messageMsg.style.color = 'red';
               valid = false;
            } else {
               messageMsg.innerHTML = '';
            }

            // Prevent form submission if validation fails
            if (!valid) {
               event.preventDefault();
            }
         });
         });

      </script>

   </body>
</html>