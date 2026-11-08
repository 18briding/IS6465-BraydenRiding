<?php require_once 'includes/header.php'; ?>

    <input type="text" class="search-bar" placeholder="Search for Restaurants or food items...">

    <section>
      <h2>Popular Restaurants</h2>
      <div class="card-grid">
        <div class="card" style="cursor: pointer;" onclick="window.location.href='restaurant-detail.php'">
          <h3><a href="restaurant-detail.php" style="text-decoration: none; color: inherit;">Ocean Cafe</a></h3>
          <p>Rating: 4.8 / 5</p>
          <p>Followers: 320</p>
        </div>
        <div class="card" style="cursor: pointer;" onclick="window.location.href='restaurant-detail.php'">
          <h3><a href="restaurant-detail.php" style="text-decoration: none; color: inherit;">Mountain Eats</a></h3>
          <p>Rating: 4.5 / 5</p>
          <p>Followers: 250</p>
        </div>
        <div class="card" style="cursor: pointer;" onclick="window.location.href='restaurant-detail.php'">
          <h3><a href="restaurant-detail.php" style="text-decoration: none; color: inherit;">Island Grill</a></h3>
          <p>Rating: 4.2 / 5</p>
          <p>Followers: 180</p>
        </div>
      </div>
    </section>

<?php require_once 'includes/footer.php'; ?>