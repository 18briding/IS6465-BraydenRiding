<?php
// Reusable PDO connection (architecture rule 4).
// PTH_DB_PATH lets automated tests point the app at a disposable database
// without touching database/tutoring.db.
function openDatabase($path = null) {
    if ($path === null) {
        $env = getenv('PTH_DB_PATH');
        $path = ($env !== false && $env !== '') ? $env : dirname(__DIR__) . '/database/tutoring.db';
    }
    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    return $pdo;
}
