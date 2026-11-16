<?php
require_once 'includes/db.php';
require_once 'services/giftcardService.php';

$pdo = openDatabase();
$cards = getAllGiftCards($pdo);

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

