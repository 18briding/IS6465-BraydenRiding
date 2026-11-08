<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Food Item - FoodieFind</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <header>
    <a href="home.php" class="logo">FoodieFind</a>
  </header>

  <main class="container" style="max-width: 600px;">
    <h2>Add New Food Item</h2>
    
    <form action="food-items.php" method="POST" enctype="multipart/form-data" style="margin-top: 1.5rem;">
      <div class="form-group">
        <label for="foodName">Food Name</label>
        <input type="text" id="foodName" name="food_name" placeholder="e.g. Garlic Shrimp" required>
      </div>

      <div class="form-group">
        <label for="restaurant">Restaurant</label>
        <input type="text" id="restaurant" name="restaurant" placeholder="e.g. Island Grill" required>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Garlic Shrimp with Butter"></textarea>
      </div>

      <div class="form-group">
        <label for="price">Price ($)</label>
        <input type="number" step="1.0" id="price" name="price" placeholder="18.00" required>
      </div>

      <div class="form-group">
        <label for="image">Food Image</label>
        <input type="file" id="image" name="image">
      </div>

      <div class="actions" style="margin-top: 2rem;">
        <button type="submit" class="btn">Save Food Item</button>
        <a href="restaurant-detail.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </main>
</body>
</html>