<?php
// Command-line only: create an Administrator account (public registration can never do this).
//   php scripts/create-admin.php "Full Name" admin@school.edu 'a-strong-password'
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
require_once $root . '/config/db.php';
require_once $root . '/services/userService.php';
if ($argc !== 4) { fwrite(STDERR, "Usage: php scripts/create-admin.php \"Full Name\" email@email.com password\n"); exit(2); }
try {
    $id = createAdministratorAccount(openDatabase(), $argv[1], $argv[2], $argv[3]);
    echo "Administrator created (user_id $id).\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
