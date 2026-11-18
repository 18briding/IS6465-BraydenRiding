<?php
// Creates or upgrades database/tutoring.db (safe to run more than once).
//   php scripts/init-db.php          schema only
//   php scripts/init-db.php --demo   also adds FICTIONAL demo accounts (see README for passwords)
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
require_once $root . '/config/db.php';
require_once $root . '/services/userService.php';

$path = $root . '/database/tutoring.db';
foreach ($argv as $a) { if (strpos($a, '--db=') === 0) { $path = substr($a, 5); } }   // optional override (used by tests)
if (!is_dir(dirname($path))) { mkdir(dirname($path), 0775, true); }
$isNew = !file_exists($path);
$pdo = openDatabase($path);

if ($isNew) {
    $pdo->exec(file_get_contents($root . '/database/setup-database.sql'));
    echo "Created new database.\n";
} else {
    $hasUsers = (int)$pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='users'")->fetchColumn() > 0;
    $cols = [];
    foreach ($pdo->query('PRAGMA table_info(students)') as $c) { $cols[] = $c['name']; }
    if (!$hasUsers || !in_array('user_id', $cols, true)) {
        if ($hasUsers) { $pdo->exec('DROP TABLE users'); }   // only an empty, partial users table can exist here
        $pdo->exec(file_get_contents($root . '/database/migrate-add-users.sql'));
        echo "Upgraded existing database (added users table and students.user_id).\n";
    } else {
        echo "Database already up to date.\n";
    }
}

if (in_array('--demo', $argv, true)) {
    $demo = [
        ['Alex Morgan', 'student@example.edu', 'StudentPass1', 'Student'],
        ['Taylor Lee', 'tutor@example.edu', 'TutorPass123', 'Tutor'],
    ];
    foreach ($demo as $d) {
        if (!findUserByEmail($pdo, $d[1])) {
            registerUser($pdo, ['full_name' => $d[0], 'email' => $d[1], 'password' => $d[2], 'confirm_password' => $d[2], 'role' => $d[3]]);
            echo "Added demo {$d[3]}: {$d[1]}\n";
        }
    }
    if (!findUserByEmail($pdo, 'admin@example.edu')) {
        createAdministratorAccount($pdo, 'Casey Admin', 'admin@example.edu', 'AdminPass123');
        echo "Added demo Administrator: admin@example.edu\n";
    }
}
