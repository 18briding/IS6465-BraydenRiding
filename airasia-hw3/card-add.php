<?php
require_once 'includes/header.php';
require_once 'includes/nav.php';
?>

            <h2>Add New Gift Card</h2>

            <!-- Directs user back to card-list.php on submit -->
            <form action="card-list.php" method="get" class="card-form">
                <div class="form-group">
                    <label for="cardName">Card Name:</label>
                    <input type="text" id="cardName" name="cardName" required>
                </div>

                <div class="form-group">
                    <label for="cardType">Card Type:</label>
                    <select id="cardType" name="cardType" required>
                        <option value="">-- Select Type --</option>
                        <option value="Restaurant and Dining">Restaurant and Dining</option>
                        <option value="Retail">Retail</option>
                        <option value="Travel">Travel</option>
                        <option value="Entertainment">Entertainment</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cardValue">Card Value:</label>
                    <input type="text" id="cardValue" name="cardValue" placeholder="$0.00" required>
                </div>

                <div class="form-group">
                    <label for="points">Points Required:</label>
                    <input type="number" id="points" name="points" required>
                </div>

                <div class="form-actions">
                    <a href="card-list.php" class="btn">Cancel</a>
                    <button type="submit" class="btn">Submit</button>
                </div>
            </form>

<?php require_once 'includes/footer.php'; ?>