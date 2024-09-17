<?php
require_once(dirname(__FILE__) . '/config.php');

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
   </head>
   <body>
      <!--==================== HEADER ====================-->
      <header class="header" id="header">
         <nav class="nav container">
            <img src="<?php echo BASE_URL; ?>dist/src/img/favicon1.png" alt="logoimage" class="logo_image">
            <a href="index.html" class="nav__logo">Oven Crust Bakery</a>
         </nav>
         
      </header>

      <!--==================== ABOUT US MAIN ====================-->
      <main class="main">
         <section class="section" style="background-image:url(assets/img/about-us.jpeg)">
            <h2 class="section__title">About Us</h2>
         
               <div class="about__container container">
                  <div class="about__data" id="about-page">
                     <h3>Our Story</h3>
                     <p class="about__description">
                        Oven Crust Bakery started with a simple mission: to bring the taste of freshly baked goods to the heart of Sri Lanka. What began as a small family-owned bakery has grown into a beloved destination for bread, cakes, and pastries, where tradition meets innovation. Our journey has been one of passion, dedication, and a commitment to quality that has kept our customers coming back for more.
                     </p>
                  </div>

                  <div class="about__data" id="about-page">
                     <h3>Our Ingredients</h3>
                     <p class="about__description">
                        We believe that great taste starts with great ingredients. That’s why we source only the finest local produce, ensuring that every product we create is as fresh and flavorful as possible. From the grains in our breads to the cocoa in our cakes, every ingredient is carefully selected to meet our high standards of quality.
                     </p>
                  </div>

                  <div class="about__data" id="about-page">
                     <h3>Our Craft</h3>
                     <p class="about__description">
                        Baking is an art, and at Oven Crust Bakery, we take it seriously. Each loaf of bread, each cake, and every pastry is crafted with care and expertise. We blend traditional techniques with modern innovations, ensuring that every item is not only delicious but also uniquely ours.
                     </p>
                  </div>

                  <div class="about__data" id="about-page">
                     <h3>Our Commitment to the Community</h3>
                     <p class="about__description">
                        As a local bakery, we’re proud to be part of the community. We work closely with local farmers and suppliers, supporting the local economy and ensuring that our products are as fresh as possible. Our commitment to sustainability means that we also take care to minimize our environmental impact, from reducing waste to using eco-friendly packaging.
                     </p>
                  </div>

                  <div class="about__data" id="about-page">
                     <h3>Our Products</h3>
                        <p class="about__description">
                           At Oven Crust Bakery, we offer a wide range of products to suit every taste. Whether you're looking for a rustic loaf of bread, a decadent cake for a special occasion, or a flaky pastry for your morning coffee, you'll find it here. Our menu includes:
                        <ul>
                           <li><strong>Breads:</strong> Whole Grain, Multigrain, Rye, Sourdough, and more.</li>
                           <li><strong>Cakes:</strong> Chocolate Cake, Vanilla Cake, Fruit Cake, and custom-made cakes for special occasions.</li>
                           <li><strong>Pastries:</strong> Butter Pastries, Croissants, Danishes, and seasonal specialties.</li>
                        </ul>
                        
                        </p>
                  </div>

                  <br><br>

                  <div class="about__data" id="about-page">
                     <h3>Why Choose Us?</h3>
                     <p class="about__description">
                        When you choose Oven Crust Bakery, you’re choosing more than just a place to buy bread or cake. You’re choosing a tradition of quality, a dedication to flavor, and a commitment to community. We’re passionate about what we do, and we’re proud to share our love of baking with you.
                     </p>
                  </div>

                  <div class="about__data" id="about-page">
                     <h3>Visit Us</h3>
                    <p class="about__description">
                        Come and experience the warmth of Oven Crust Bakery. Whether you’re stopping by for a quick treat or ordering for a special event, we look forward to welcoming you to our bakery.
                    </p>
                  </div>
               </div>
            </section>
         </main>
      </body>         
 
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
   </body>
</html>