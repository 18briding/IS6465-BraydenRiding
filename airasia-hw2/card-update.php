<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AirAsia Rewards - Update Gift Card Details</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>AirAsia Rewards Program</h1>
        </header>

        <main>
            <h2>Update Gift Card Details</h2>

            <!-- Directs user back to card-list.php on submit -->
            <form action="card-list.php" method="get" class="card-form">
                <div class="form-group">
                    <label for="cardName">Card Name:</label>
                    <input type="text" id="cardName" name="cardName" value="Chili's" required>
                </div>

                <div class="form-group">
                    <label for="cardType">Card Type:</label>
                    <select id="cardType" name="cardType" required>
                        <option value="Restaurant and Dining" selected>Restaurant and Dining</option>
                        <option value="Retail">Retail</option>
                        <option value="Travel">Travel</option>
                        <option value="Entertainment">Entertainment</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cardValue">Card Value:</label>
                    <input type="text" id="cardValue" name="cardValue" value="$50.00" required>
                </div>

                <div class="form-group">
                    <label for="points">Points Required:</label>
                    <input type="text" id="points" name="points" value="5,000" required>
                </div>

                <div class="form-actions">
                    <a href="card-list.php" class="btn">Cancel</a>
                    <button type="submit" class="btn">Submit</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>