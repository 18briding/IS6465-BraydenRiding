<?php
// Include database connection and validation functions
require_once 'includes/db.php';
require_once 'includes/validation.php';

$pdo = openDatabase();

// 1. Get and validate the card_id using the reusable validation function from Part 5
$cardId = isset($_GET['card_id']) ? validateId($_GET['card_id']) : 0;

$card = null;
if ($cardId > 0) {
    // 2. Use a prepared statement to fetch the specific gift card securely
    $stmt = $pdo->prepare("SELECT * FROM GIFTCARD WHERE cardId = ?");
    $stmt->execute([$cardId]);
    $card = $stmt->fetch();
}

// Include shared header and navigation templates
require_once 'includes/header.php';
require_once 'includes/nav.php';
?>

<div class="container">
    <div class="top-action-bar" style="display: flex; margin-bottom: 20px;">
        <a href="card-list.php" class="btn">&larr; Back to Gift Card List</a>
    </div>

    <section>
        <?php if (!$card): ?>
            <h2>Gift Card Not Found</h2>
            <p>The requested gift card does not exist or an invalid ID was provided.</p>
        <?php else: ?>
            <h2>Gift Card Details</h2>

            <div class="card-details-box">
                <?php 
                    // Reusable image mapping logic
                    $lowerName = strtolower($card['cardName']);
                    $imgName = 'default-logo.png';
                    if (strpos($lowerName, 'starbucks') !== false) {
                        $imgName = 'starbuckslogo.png';
                    } elseif (strpos($lowerName, 'chili') !== false) {
                        $imgName = 'chilislogo.png';
                     } elseif (strpos($lowerName, 'amazon') !== false) {
                        $imgName = 'amazonlogo.png';
                    } elseif (strpos($lowerName, 'apple') !== false) {
                        $imgName = 'applelogo.png';
                    } elseif (strpos($lowerName, 'target') !== false) {
                        $imgName = 'targetlogo.png';
                    } elseif (strpos($lowerName, 'walmart') !== false) {
                        $imgName = 'walmartlogo.png';
                    } elseif (strpos($lowerName, 'uber') !== false) {
                        $imgName = 'uberlogo.png';
                    } elseif (strpos($lowerName, 'nike') !== false) {
                        $imgName = 'nikelogo.png';
                    } elseif (strpos($lowerName, 'best buy') !== false || strpos($lowerName, 'bestbuy') !== false) {
                        $imgName = 'bestbuylogo.png';
                    }
                ?>
                <img src="images/<?php echo htmlspecialchars($imgName); ?>" alt="<?php echo htmlspecialchars($card['cardName']); ?> Gift Card" class="detail-img">

                <div class="card-info">
                    <h3><?php echo htmlspecialchars($card['cardName']); ?></h3>
                    <p><strong>Card Type:</strong> <?php echo htmlspecialchars($card['cardType']); ?></p>
                    <p><strong>Card Value:</strong> $<?php echo htmlspecialchars(number_format($card['cardValue'], 2)); ?></p>
                    <p><strong>Points Required:</strong> <?php echo htmlspecialchars(number_format($card['points'])); ?> points</p>
                </div>
            </div>

            <!-- Action buttons required by workflow -->
            <div class="action-buttons" style="margin-top: 20px;">
                <a href="card-update.php?card_id=<?php echo $card['cardId']; ?>" class="btn btn-update">Update Gift Card</a>
                <a href="card-delete.php?card_id=<?php echo $card['cardId']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this gift card?');">Delete Gift Card</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
// Include shared footer template
require_once 'includes/footer.php';
?>