<?php require_once 'includes/header.php'; ?>

    <div style="margin-bottom: 2rem;">
      <a href="home.php" class="btn btn-secondary" style="margin-bottom: 1rem; display: inline-block;">&larr; Back to Home</a>
      <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
          <h2>Ocean Cafe</h2>
          <p style="color: var(--text-muted);">123 Coastal Highway, Beachville</p>
          <p style="margin-top: 0.5rem;">Rating: 4.8 / 5 &bull; Followers: 320</p>
        </div>
        <div>
          <button class="btn" style="margin-right: 0.5rem;">Follow Restaurant</button>
          <a href="food-add.php" class="btn">+ Add Food Item</a>
        </div>
      </div>
    </div>

    <section style="margin-top: 2rem;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>Available Food Items</h3>
        <a href="review-add.php" class="btn">+ Write a Review</a>
      </div>
      <table>
        <thead>
          <tr>
            <th>Food Item</th>
            <th>Description</th>
            <th>Price</th>
            <th>Rating</th>
            <th>Actions (Owner)</th>
          </tr>
        </thead>
        <tbody>
          <tr style="cursor: pointer;" onclick="window.location.href='food-detail.php'">
            <td>
              <a href="food-detail.php" style="text-decoration: underline; color: var(--primary-color); font-weight: 600;">
                Garlic Shrimp
              </a>
            </td>
            <td>Garlic Shrimp with Butter</td>
            <td>$18.00</td>
            <td>4.9/5</td>
            <td class="actions" onclick="event.stopPropagation();">
              <a href="food-edit.php" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Edit</a>
              <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Delete</button>
            </td>
          </tr>
          <tr style="cursor: pointer;" onclick="window.location.href='food-detail.php'">
            <td>
              <a href="food-detail.php" style="text-decoration:  underline; color: var(--primary-color); font-weight: 600;">
                Chicken Katsu
              </a>
            </td>
            <td>Crispy chicken cutlet served with katsu sauce and rice.</td>
            <td>$15.00</td>
            <td>4.7/5</td>
            <td class="actions" onclick="event.stopPropagation();">
              <a href="food-edit.php" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Edit</a>
              <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </section>

<?php require_once 'includes/footer.php'; ?>