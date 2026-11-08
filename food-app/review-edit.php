<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Your Review - FoodieFind</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <header>
    <a href="home.php" class="logo">FoodieFind</a>
  </header>

  <main class="container" style="max-width: 600px;">
    <h2>Update Your Review</h2>
    
    <form action="reviews.php" method="POST" style="margin-top: 1.5rem;">
      <div class="form-group">
        <label for="foodItem">Food Item</label>
        <input type="text" id="foodItem" value="Garlic Shrimp - Island Grill" readonly style="background-color: #eee;">
      </div>

      <div class="form-group">
        <label for="rating">Your Rating</label>
        <select id="rating" name="rating">
          <option value="5" selected>5 / 5</option>
          <option value="4">4 / 5</option>
          <option value="3">3 / 5</option>
          <option value="2">2 / 5</option>
          <option value="1">1 / 5</option>
        </select>
      </div>

      <div class="form-group">
        <label for="reviewText">Your Review</label>
        <textarea id="reviewText" name="review_text" required>The shrimp was delicious, I would definitely order again!</textarea>
      </div>

      <div class="actions" style="margin-top: 2rem;">
        <button type="submit" class="btn">Save Changes</button>
        <a href="reviews.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </main>
</body>
</html>