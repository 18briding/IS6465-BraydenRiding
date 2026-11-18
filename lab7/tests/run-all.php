<?php
// Runs every automated suite and exits non-zero if any fail.   php tests/run-all.php
$root = dirname(__DIR__);
$suites = [
    'Lab 4 validation (tests/validation-tests.php)' => 'tests/validation-tests.php',
    'UC01/UC02 regression (tests/integration-tests.php)' => 'tests/integration-tests.php',
    'UM-01..UM-09 services (tests/user-management-tests.php)' => 'tests/user-management-tests.php',
    'Database upgrade (tests/migration-tests.php)' => 'tests/migration-tests.php',
    'UM-01..UM-10 over HTTP (tests/http-tests.php)' => 'tests/http-tests.php',
];
$bad = 0;
foreach ($suites as $label => $file) {
    echo "=== $label" . PHP_EOL;
    $out = [];
    exec('cd ' . escapeshellarg($root) . ' && ' . escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($file) . ' 2>&1', $out, $code);
    $text = implode(PHP_EOL, $out);
    if ($file === 'tests/validation-tests.php') {   // legacy HTML-style output, no exit code of its own
        $pass = substr_count($text, 'PASS'); $fail = substr_count($text, 'FAIL');
        $code = $fail > 0 || $pass === 0 ? 1 : 0;
        echo "Results: $pass passed, $fail failed" . PHP_EOL;
    } else {
        echo $text . PHP_EOL;
    }
    if ($code !== 0) { $bad++; echo "SUITE FAILED (exit $code)" . PHP_EOL; }
}
echo ($bad ? "$bad suite(s) FAILED" : 'All suites passed') . PHP_EOL;
exit($bad ? 1 : 0);
