<?php
// Include database connection and validation rules
require_once 'includes/db.php';
require_once 'includes/validation.php';

$pdo = openDatabase();

// 1. Retrieve and validate card_id from request parameters
$cardId = isset($_REQUEST['card_id']) ? validateId($_REQUEST['card_id']) : 0;

if (!$cardId) {
    die("Invalid or missing Gift Card ID.");
}

// 2. Fetch the existing gift card record using a prepared statement
$stmt = $pdo->prepare("SELECT * FROM GIFTCARD WHERE cardId = ?");
$stmt->execute([$cardId]);
$card = $stmt->fetch();

if (!$card) {
    die("Gift Card not found.");
}

$error = '';

// 3. Require confirmation through a POST form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 4. Delete the record only after confirmation using a prepared statement
        $deleteStmt = $pdo->prepare("DELETE FROM GIFTCARD WHERE cardId = ?");
        $deleteStmt->execute([$cardId]);

        // 5. Return the user to card-list.php after successful deletion
        header("Location: card-list.php");
        exit;
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Include shared header and navigation templates
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