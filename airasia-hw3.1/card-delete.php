<?php
require_once 'includes/db.php';
require_once 'includes/validation.php';
require_once 'services/giftcardService.php';

$pdo = openDatabase();
$cardId = isset($_REQUEST['card_id']) ? validateId($_REQUEST['card_id']) : 0;
if (!$cardId) {
    die("Invalid or missing Gift Card ID.");
}

$card = handleDeleteGiftCard($pdo, $cardId);
if (!$card) {
    die("Gift Card not found.");
}

require_once 'includes/header.php';
require_once 'includes/nav.php';
?>

<div class="top-action-bar">
    <a href="card-details.php?card_id=<?php echo $cardId; ?>" class="btn">&larr; Cancel / Back to Details</a>
</div>

<section>
    <h2 class="page-title">Confirm Gift Card Deletion</h2>

    <?php if (!empty($error)): ?>
        <div class="error-msg">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Clearly identify the Gift Card that will be deleted -->
    <div class="card-delete-warning">
        <p class="warning-title">Are you sure you want to delete this gift card? This action cannot be undone.</p>
        <p><strong>Card Name:</strong> <?php echo htmlspecialchars($card['cardName']); ?></p>
        <p><strong>Card Type:</strong> <?php echo htmlspecialchars($card['cardType']); ?></p>
        <p><strong>Card Value:</strong> $<?php echo htmlspecialchars(number_format($card['cardValue'], 2)); ?></p>
        <p><strong>Points Required:</strong> <?php echo htmlspecialchars(number_format($card['points'])); ?> points</p>
    </div>

    <!-- Confirmation form requiring POST submission -->
    <form action="card-delete.php?card_id=<?php echo $cardId; ?>" method="POST">
        <div class="form-actions">
            <button type="submit" class="btn btn-danger">Yes, Confirm Deletion</button>
            <a href="card-details.php?card_id=<?php echo $cardId; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<?php
require_once 'includes/footer.php';
?>