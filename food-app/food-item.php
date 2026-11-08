<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Food Items - FoodieFind</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <header>
    <a href="home.php" class="logo">FoodieFind</a>
    <nav>
      <a href="home.php">Home</a>
      <a href="food-items.php">Food Items</a>
      <a href="reviews.php">Reviews</a>
      <a href="login.php">Logout</a>
    </nav>
  </header>

  <main class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
      <h2>Food Items</h2>
      <a href="food-add.php" class="btn">+ Add Food Item</a>
    </div>

    <input type="text" class="search-bar" placeholder="Search for food items...">

    <table>
      <thead>
        <tr>
          <th>Food Item</th>
          <th>Restaurant</th>
          <th>Price</th>
          <th>Rating</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Garlic Shrimp</td>
          <td>Island Grill</td>
          <td>$18.00</td>
          <td>4.9/5</td>
          <td class="actions">
            <a href="food-edit.php" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Edit</a>
            <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Delete</button>
          </td>
        </tr>
        <tr>
          <td>Chicken Katsu</td>
          <td>Ocean Cafe</td>
          <td>$15.00</td>
          <td>4.7/5</td>
          <td class="actions">
            <a href="food-edit.php" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Edit</a>
            <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Delete</button>
          </td>
        </tr>
        <tr>
          <td>Cheeseburger</td>
          <td>Mountain Eats</td>
          <td>$12.00</td>
          <td>4.5/5</td>
          <td class="actions">
            <a href="food-edit.php" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Edit</a>
            <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </main>
</body>
</html>