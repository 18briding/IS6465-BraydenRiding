<?php
// services/giftcardService.php
require_once __DIR__ . '/../includes/validation.php';

function getAllGiftCards($pdo) {
    $stmt = $pdo->query("SELECT * FROM GIFTCARD");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getGiftCardById($pdo, $cardId) {
    $stmt = $pdo->prepare("SELECT * FROM GIFTCARD WHERE cardId = ?");
    $stmt->execute([$cardId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Helper function to map card names to image filenames cleanly.
 */
function getCardImageFileName($cardName) {
    $lowerName = strtolower($cardName);
    
    // Default fallback image if no keyword matches
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

    return $imgName;
}

function handleUpdateGiftCard($pdo, $cardId) {
    $card = getGiftCardById($pdo, $cardId);
    if (!$card) {
        return null;
    }

    $errors = [];
    $cardName  = $card['cardName'];
    $cardType  = $card['cardType'];
    $cardValue = $card['cardValue'];
    $points    = $card['points'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cardName  = $_POST['cardName'] ?? '';
        $cardType  = $_POST['cardType'] ?? '';
        $cardValue = $_POST['cardValue'] ?? '';
        $points    = $_POST['points'] ?? '';

        if ($err = validateCardName($cardName))   $errors['cardName'] = $err;
        if ($err = validateCardType($cardType))   $errors['cardType'] = $err;
        if ($err = validateCardValue($cardValue)) $errors['cardValue'] = $err;
        if ($err = validatePoints($points))       $errors['points'] = $err;

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE GIFTCARD SET cardName = ?, cardType = ?, cardValue = ?, points = ? WHERE cardId = ?");
            $stmt->execute([
                trim($cardName),
                trim($cardType),
                (float)$cardValue,
                (int)$points,
                $cardId
            ]);
            header("Location: card-details.php?card_id=" . $cardId);
            exit;
        }
    }

    return [
        'card'   => $card,
        'errors' => $errors,
        'data'   => [
            'cardName'  => $cardName,
            'cardType'  => $cardType,
            'cardValue' => $cardValue,
            'points'    => $points
        ]
    ];
}

function handleDeleteGiftCard($pdo, $cardId) {
    $card = getGiftCardById($pdo, $cardId);
    if (!$card) {
        return false;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("DELETE FROM GIFTCARD WHERE cardId = ?");
        $stmt->execute([$cardId]);
        header("Location: card-list.php");
        exit;
    }

    return $card;
}
function handleAddGiftCard($pdo) {
    $errors = [];
    $formData = [
        'cardName' => '',
        'cardType' => '',
        'cardValue' => '',
        'points' => ''
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formData['cardName']  = $_POST['cardName'] ?? '';
        $formData['cardType']  = $_POST['cardType'] ?? '';
        $formData['cardValue'] = $_POST['cardValue'] ?? '';
        $formData['points']    = $_POST['points'] ?? '';

        if ($err = validateCardName($formData['cardName']))   $errors['cardName'] = $err;
        if ($err = validateCardType($formData['cardType']))   $errors['cardType'] = $err;
        if ($err = validateCardValue($formData['cardValue'])) $errors['cardValue'] = $err;
        if ($err = validatePoints($formData['points']))       $errors['points'] = $err;

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO GIFTCARD (cardName, cardType, cardValue, points) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                trim($formData['cardName']),
                trim($formData['cardType']),
                (float)$formData['cardValue'],
                (int)$formData['points']
            ]);
            header("Location: card-list.php");
            exit;
        }
    }

    return ['errors' => $errors, 'data' => $formData];
}


?>