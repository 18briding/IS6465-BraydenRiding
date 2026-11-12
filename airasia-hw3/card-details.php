<?php
require_once 'includes/header.php';
require_once 'includes/nav.php';
?>

    <div class="container">
        <header>
            <h1>Gift Card Details</h1>
        </header>

        <main>

            <div class="details-wrapper">
                <div class="details-info">
                    <p><strong>Card Name:</strong> Chili's</p>
                    <p><strong>Card Type:</strong> Restaurant and Dining</p>
                    <p><strong>Card Value:</strong> $50.00</p>
                    <p><strong>Points Required:</strong> 5,000</p>
                </div>

                <div class="details-image">
                    <img src="images/chilislogo.png" alt="Gift Card Image">
                </div>
            </div>

            <div class="form-actions">
                <a href="card-list.php" class="btn">Back to List</a>
                <!-- Link to Card Update Page -->
                <a href="card-update.php" class="btn">Update Gift Card</a>
                <!-- Non-functional placeholder delete link for workflow layout -->
                <a href="card-list.php" class="btn">Delete Gift Card</a>
            </div>
        </main>
    </div>

    <?php require_once 'includes/footer.php'; ?>