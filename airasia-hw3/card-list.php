<?php
// Include database connection
require_once 'includes/db.php';
$pdo = openDatabase();

// Fetch all gift cards from the database
$stmt = $pdo->query("SELECT * FROM GIFTCARD");
$cards = $stmt->fetchAll();

/**
 * Helper function to map card names to image filenames cleanly.
 * This avoids repetitive if-else statements across your application.
 */
function getCardImageFileName($cardName) {
    $lowerName = strtolower($cardName);
    
    // Map brand keywords to their respective image filenames
    $imageMap = [
        'starbucks' => 'starbuckslogo.png',
        'chili'     => 'chilislogo.png',
        'apple'     => 'applelogo.png',
        'target'    => 'targetlogo.png',
        'walmart'   => 'walmartlogo.png',
        'nike'      => 'nikelogo.png',
        'best buy'  => 'bestbuylogo.png',
        'bestbuy'   => 'bestbuylogo.png',
        'amazon'    => 'amazonlogo.png'
    ];

    // Check if the card name contains any of the known keywords
    foreach ($imageMap as $keyword => $filename) {
        if (strpos($lowerName, $keyword) !== false) {
            return $filename;
        }
    }

    // Default fallback image if no keyword matches
    return 'default-logo.png';
}

// Include shared header and navigation templates
require_once 'includes/header.php';
require_once 'includes/nav.php';
?>

<div class="container">
    <div class="top-nav">
    </div>

    <section>
        <h2>Available Gift Cards</h2><a href="card-add.php" class="btn">Add Gift Card</a>

        <div class="card-grid">
            <?php if (empty($cards)): ?>
                <p>No gift cards currently available in the database.</p>
            <?php else: ?>
                <?php foreach ($cards as $card): ?>
                    <div class="card-item">
                        <!-- Card Name linked to Details page with card_id parameter -->
                        <h3>
                            <a href="card-details.php?card_id=<?php echo htmlspecialchars($card['cardId']); ?>">
                                <?php echo htmlspecialchars($card['cardName']); ?>
                            </a>
                        </h3>

                        <!-- Card Image linked to Details page using the clean helper function -->
                        <a href="card-details.php?card_id=<?php echo htmlspecialchars($card['cardId']); ?>">
                            <?php 
                                $imgName = getCardImageFileName($card['cardName']);
                            ?>
                            <img src="images/<?php echo htmlspecialchars($imgName); ?>" alt="<?php echo htmlspecialchars($card['cardName']); ?> Gift Card">
                        </a>

                        <!-- Points Required -->
                        <p>Points: <?php echo htmlspecialchars(number_format($card['points'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
// Include shared footer template
require_once 'includes/footer.php';
?>

