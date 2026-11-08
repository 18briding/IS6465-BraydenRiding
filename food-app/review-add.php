<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Write a Review - FoodieFind</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <header>
    <a href="home.php" class="logo">FoodieFind</a>
  </header>

  <main class="container" style="max-width: 600px;">
    <h2>Write a Review</h2>
    
    <form action="reviews.php" method="POST" style="margin-top: 1.5rem;">
      <div class="form-group">
        <label for="foodItem">Food Name</label>
        <select id="foodItem" name="food_id" required>
          <option value="">Select food item</option>
          <option value="1">Garlic Shrimp - Island Grill</option>
          <option value="2">Chicken Katsu - Ocean Cafe</option>
          <option value="3">Cheeseburger - Mountain Eats</option>
        </select>
      </div>

      <div class="form-group">
        <label for="rating">Your Rating</label>
        <select id="rating" name="rating" required>
          <option value="5">5 / 5</option>
          <option value="4">4 / 5</option>
          <option value="3">3 / 5</option>
          <option value="2">2 / 5</option>
          <option value="1">1 / 5</option>
        </select>
      </div>

      <div class="form-group">
        <label for="reviewText">Your Review</label>
        <textarea id="reviewText" name="review_text" placeholder="Write a review..." required></textarea>
      </div>

      <div class="actions" style="margin-top: 2rem;">
        <button type="submit" class="btn">Submit Review</button>
        <a href="food-detail.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </main>
</body>
</html>