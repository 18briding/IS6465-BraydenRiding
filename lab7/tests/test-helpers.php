<?php
// Shared harness pieces for the CLI tests. Tests never touch database/tutoring.db.
$passed = 0;
$failed = 0;

function check($condition, $label) {
    global $passed, $failed;
    if ($condition) { $passed++; echo "PASS: $label" . PHP_EOL; }
    else { $failed++; echo "FAIL: $label" . PHP_EOL; }
}
function finishTests() {
    global $passed, $failed;
    echo "Results: $passed passed, $failed failed" . PHP_EOL;
    exit($failed > 0 ? 1 : 0);
}
function abortTests($message) {
    fwrite(STDERR, "SETUP ERROR: $message" . PHP_EOL);
    echo "Results: setup failed" . PHP_EOL;
    exit(2);
}

// Fresh in-memory SQLite with the approved schema. Seed INSERT lines are dropped so every test
// starts from a known empty state (the sample seed row in setup-database.sql would skew counts).
function createTestDatabase() {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $sql = file_get_contents(dirname(__DIR__) . '/database/setup-database.sql');
    $ddl = [];
    foreach (explode("\n", $sql) as $line) {
        if (stripos(ltrim($line), 'INSERT') === 0) { continue; }
        $ddl[] = $line;
    }
    $pdo->exec(implode("\n", $ddl));
    return $pdo;
}

// Runs $fn and returns the exception class it threw (or '' if none).
function thrownBy($fn) {
    try { $fn(); } catch (Throwable $e) { return get_class($e); }
    return '';
}
function tableCount($pdo, $table) { return (int)$pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn(); }
function rowSnapshot($pdo, $sql) { return json_encode($pdo->query($sql)->fetchAll()); }

function regInput($name, $email, $role = 'Student', $pw = 'Password123') {
    return ['full_name' => $name, 'email' => $email, 'password' => $pw, 'confirm_password' => $pw, 'role' => $role];
}
function requestInput($name, $email) {
    return ['student_name' => $name, 'student_email' => $email, 'course' => 'IS 6465', 'reason' => 'Please help with PHP form validation.',
        'topics' => ['Exam review'], 'preferreddate' => (new DateTimeImmutable('+10 days'))->format('Y-m-d'), 'preferredtime' => 'Morning'];
}
