<?php require_once 'includes/header.php'; ?>

  <main class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
      <h2>Food Reviews</h2>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
      <div style="display: flex; justify-content: space-between;">
        <strong>Garlic Shrimp</strong>
        <span style="color: var(--text-muted); font-size: 0.85rem;">November 2, 2026</span>
      </div>
      <p style="font-weight: 600; color: var(--primary-color); margin: 0.3rem 0;">Ocean Cafe</p>
      <p style="margin-bottom: 1rem;">"The shrimp was delicious, I would definitely order again!"</p>
      <div class="actions">
        <a href="review-edit.php" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Edit</a>
        <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Delete</button>
      </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
      <div style="display: flex; justify-content: space-between;">
        <strong>Garlic Shrimp</strong>
        <span style="color: var(--text-muted); font-size: 0.85rem;">November 3, 2026</span>
      </div>
      <p style="font-weight: 600; color: var(--primary-color); margin: 0.3rem 0;">Island Grill</p>
      <p style="margin-bottom: 1rem;">"Great flavor, small portion"</p>
      <div class="actions">
        <a href="review-edit.php" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Edit</a>
        <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Delete</button>
      </div>
    </div>
  </main>
</body>
<?php require_once 'includes/footer.php'; ?>