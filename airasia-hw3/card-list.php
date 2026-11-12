<?php
require_once 'includes/header.php';
require_once 'includes/nav.php';
?>

<h2 class="page-title">Available Gift Cards</h2>

            <div class="card-grid">
                <!-- Gift Card 1 -->
                <div class="card-item">
                    <h3><a href="card-details.php">Chili's</a></h3>
                    <a href="card-details.php">
                        <img src="images/chilislogo.png" alt="Chili's Gift Card">
                    </a>
                    <p>Points: 5,000</p>
                </div>

                <!-- Gift Card 2 -->
                <div class="card-item">
                    <h3><a href="card-details.php">Amazon</a></h3>
                    <a href="card-details.php">
                        <img src="images/amazonlogo.png" alt="Amazon Gift Card">
                    </a>
                    <p>Points: 3,000</p>
                </div>

                <!-- Gift Card 3 -->
                <div class="card-item">
                    <h3><a href="card-details.php">Starbucks</a></h3>
                    <a href="card-details.php">
                        <img src="images/starbuckslogo.png" alt="Starbucks Gift Card">
                    </a>
                    <p>Points: 2,500</p>
                </div>
            </div>
<?php
require_once 'includes/footer.php';
?>