<?php require_once(dirname(__FILE__) . '/config.php'); ?>

<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
   <script>alert('New order placed successfully');</script>
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
   </head>
   <body>
      <!--==================== HEADER ====================-->
      <header class="header" id="header">
         <nav class="nav container">
            <img src="<?php echo BASE_URL; ?>dist/src/img/favicon1.png" alt="logoimage" class="logo_image">
            <a href="index.html" class="nav__logo">Oven Crust Bakery</a>
         </nav>
         
      </header>

      <!--==================== MAIN ====================-->
      <main class="main">
         
     <!--==================== Order Form ====================-->
     <section class="order section" style="background-image: url('<?php echo BASE_URL; ?>dist/src/img/special-order-bg.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
         <div class="order__back">
            <a href="<?php echo BASE_URL; ?>"><i class="ri-arrow-left-line"> Go Back</i></a>
         </div>
         <h2 class="section__title">Place Your Special Order</h2>
         <form id="specialOrderForm" action="specilOrders_process.php" method="POST" class="order__form container grid">
            <div class="order__inputs grid">
                  <div class="order__content">
                     <label for="order-name" class="order__label">Name</label>
                     <input type="text" id="order-name" name="order-name" class="order__input" placeholder="Your Name" required />
                     <div id="name-msg" class="validation-msg"></div>
                  </div>
                  <div class="order__content">
                     <label for="order-email" class="order__label">Email</label>
                     <input type="email" id="order-email" name="order-email" class="order__input" placeholder="Your Email" required />
                     <div id="email-msg" class="validation-msg"></div>
                  </div>
                  <div class="order__content">
                     <label for="order-phone" class="order__label">Phone Number</label>
                     <input type="tel" id="order-phone" name="order-phone" class="order__input" placeholder="Your Phone Number" required />
                     <div id="phone-msg" class="validation-msg"></div>
                  </div>
                  <div class="order__content">
                     <label for="order-date" class="order__label">Preferred Date</label>
                     <input type="date" id="order-date" name="order-date" class="order__input" required />
                     <div id="date-msg" class="validation-msg"></div>
                  </div>
                  <div class="order__content">
                     <label for="order-details" class="order__label">Order Details</label>
                     <textarea id="order-details" name="order-details" class="order__input" placeholder="Your Order Details" required></textarea>
                     <div id="details-msg" class="validation-msg"></div>
                  </div>
                  <button type="submit" class="button">Submit Order</button>
            </div>
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
         document.getElementById('specialOrderForm').addEventListener('submit', function (event) {
         let valid = true;

         // Name validation
         const name = document.getElementById('order-name');
         const nameMsg = document.getElementById('name-msg');
         if (name.value.trim() === '') {
            nameMsg.textContent = "Name is required.";
            nameMsg.style.color = 'red';
            valid = false;
         } else {
            nameMsg.textContent = "";
         }

         // Email validation
         const email = document.getElementById('order-email');
         const emailMsg = document.getElementById('email-msg');
         const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
         if (!emailPattern.test(email.value.trim())) {
            emailMsg.textContent = "Invalid email address.";
            emailMsg.style.color = 'red';
            valid = false;
         } else {
            emailMsg.textContent = "";
         }

         // Phone validation
         const phone = document.getElementById('order-phone');
         const phoneMsg = document.getElementById('phone-msg');
         const phonePattern = /^\+94[0-9]{9}$/;
         if (!phonePattern.test(phone.value.trim())) {
            phoneMsg.textContent = "Invalid phone number.";
            phoneMsg.style.color = 'red';
            valid = false;
         } else {
            phoneMsg.textContent = "";
         }

         // Date validation
         const date = document.getElementById('order-date');
         const dateMsg = document.getElementById('date-msg');
         if (date.value.trim() === '') {
            dateMsg.textContent = "Date is required.";
            dateMsg.style.color = 'red';
            valid = false;
         } else {
            dateMsg.textContent = "";
         }

         // Order Details validation
         const details = document.getElementById('order-details');
         const detailsMsg = document.getElementById('details-msg');
         if (details.value.trim() === '') {
            detailsMsg.textContent = "Order details are required.";
            detailsMsg.style.color = 'red';
            valid = false;
         } else {
            detailsMsg.textContent = "";
         }

         if (!valid) {
            event.preventDefault(); // Prevent form submission if any validation fails
         }
      });

      </script>
   </body>
</html>