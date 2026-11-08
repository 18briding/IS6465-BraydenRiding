<?php require_once 'includes/header.php'; ?>

    <div style="margin-bottom: 2rem;">
      <a href="restaurant-detail.php" class="btn btn-secondary" style="margin-bottom: 1rem; display: inline-block;">&larr; Back to Restaurant</a>
      
      <!-- Food Item Header Info -->
      <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
          <h2>Garlic Shrimp</h2>
          <p style="color: var(--text-muted);">Restaurant: Island Grill</p>
          <p style="font-size: 1.1rem; font-weight: bold; color: var(--primary-color); margin-top: 0.3rem;">$18.00</p>
          <p style="margin-top: 0.5rem;">Description: Garlic Shrimp with Butter</p>
          <p style="margin-top: 0.3rem;">Average Rating: <strong>4.9 / 5</strong></p>
        </div>
        <div>
          <a href="review-add.php" class="btn">+ Write a Review for this Item</a>
        </div>
      </div>
    </div>

    <!-- Section: Reviews for this Food Item -->
    <section style="margin-top: 3rem;">
      <h3>Reviews for Garlic Shrimp</h3>

      <div class="card" style="margin-top: 1rem;">
        <div style="display: flex; justify-content: space-between;">
          <strong>Sarah Brown</strong>
          <span style="color: var(--text-muted); font-size: 0.85rem;">November 2, 2026</span>
        </div>
        <p style="color: var(--primary-color); margin: 0.3rem 0;">Rating: 5/5</p>
        <p style="margin-bottom: 1rem;">"The shrimp was delicious, I would definitely order again!"</p>
        <div class="actions">
         
        </div>
      </div>

      <div class="card" style="margin-top: 1rem;">
        <div style="display: flex; justify-content: space-between;">
          <strong>Devin Baker</strong>
          <span style="color: var(--text-muted); font-size: 0.85rem;">November 3, 2026</span>
        </div>
        <p style="color: var(--primary-color); margin: 0.3rem 0;">Rating: 4/5</p>
        <p style="margin-bottom: 1rem;">"Great flavor, small portion"</p>
        <div class="actions">

        </div>
      </div>
    </section>

<?php require_once 'includes/footer.php'; ?>