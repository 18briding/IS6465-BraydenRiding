<?php
// Presentation helper shared by the administrator action pages.
function renderMessagePage($title, $message, $backHref, $backLabel) {
    $user = isset($GLOBALS['user']) ? $GLOBALS['user'] : null;
    require __DIR__ . '/header.php';
    require __DIR__ . '/nav.php';
    echo '<main><h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1><div class="account-card"><p>'
        . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p><a class="btn btn-primary" href="'
        . htmlspecialchars($backHref, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8') . '</a></div></main>';
    require __DIR__ . '/footer.php';
}
