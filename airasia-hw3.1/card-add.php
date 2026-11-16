<?php
// Include database connection, validation, and the service layer
require_once 'includes/db.php';
require_once 'includes/validation.php';
require_once 'services/giftcardService.php';

$pdo = openDatabase();

// Delegate form processing, validation, and insertion to the service layer
$result = handleAddGiftCard($pdo);
$errors = $result['errors'];
$data   = $result['data'];

// Extract variables from the $data array so the form fields and dropdowns work properly
$cardName  = $data['cardName'];
$cardType  = $data['cardType'];
$cardValue = $data['cardValue'];
$points    = $data['points'];

// Include shared header and navigation templates
require_once 'includes/header.php';
require_once 'includes/nav.php';
?>

<div class="top-action-bar">
    <a href="card-list.php" class="btn">&larr; Back to Gift Card List</a>
</div>

<section>
    <h2 class="page-title">Add a New Gift Card</h2>

    <?php if (isset($errors['database'])): ?>
        <div class="error-msg">
            <?php echo htmlspecialchars($errors['database']); ?>
        </div>
    <?php endif; ?>

    <form action="card-add.php" method="POST" class="form-card">
        <!-- Card Name -->
        <div class="form-group">
            <label for="cardName">Card Name:</label>
            <input type="text" id="cardName" name="cardName" value="<?php echo htmlspecialchars($cardName); ?>" required>
            <?php if (isset($errors['cardName'])): ?>
                <span class="field-error"><?php echo htmlspecialchars($errors['cardName']); ?></span>
            <?php endif; ?>
        </div>

        <!-- Card Type -->
        <div class="form-group">
            <label for="cardType">Card Type:</label>
            <select id="cardType" name="cardType" required>
                <option value="">-- Select Type --</option>
                <option value="Retail" <?php if ($cardType === 'Retail') echo 'selected'; ?>>Retail</option>
                <option value="Dining" <?php if ($cardType === 'Dining') echo 'selected'; ?>>Dining</option>
                <option value="Electronics" <?php if ($cardType === 'Electronics') echo 'selected'; ?>>Electronics</option>
                <option value="Travel" <?php if ($cardType === 'Travel') echo 'selected'; ?>>Travel</option>
            </select>
            <?php if (isset($errors['cardType'])): ?>
                <span class="field-error"><?php echo htmlspecialchars($errors['cardType']); ?></span>
            <?php endif; ?>
        </div>

        <!-- Card Value -->
        <div class="form-group">
            <label for="cardValue">Card Value ($):</label>
            <input type="number" step="0.01" id="cardValue" name="cardValue" value="<?php echo htmlspecialchars($cardValue); ?>" required>
            <?php if (isset($errors['cardValue'])): ?>
                <span class="field-error"><?php echo htmlspecialchars($errors['cardValue']); ?></span>
            <?php endif; ?>
        </div>

        <!-- Points Required -->
        <div class="form-group">
            <label for="points">Points Required:</label>
            <input type="number" id="points" name="points" value="<?php echo htmlspecialchars($points); ?>" required>
            <?php if (isset($errors['points'])): ?>
                <span class="field-error"><?php echo htmlspecialchars($errors['points']); ?></span>
            <?php endif; ?>
        </div>

        <!-- Submit Button -->
        <div class="form-actions">
            <button type="submit" class="btn">Add Gift Card</button>
        </div>
    </form>
</section>

<?php
// Include shared footer template
require_once 'includes/footer.php';
?>