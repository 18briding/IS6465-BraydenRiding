<?php
// Upgrade path: a Lab 6 database (no users table, no students.user_id) keeps its data after scripts/init-db.php.
require_once __DIR__ . '/test-helpers.php';
require_once dirname(__DIR__) . '/config/db.php';
$root = dirname(__DIR__);
$file = tempnam(sys_get_temp_dir(), 'pth-mig-') . '.db';
register_shutdown_function(function () use ($file) { @unlink($file); @unlink(substr($file, 0, -3)); });

$legacy = openDatabase($file);   // the ORIGINAL Lab 6 schema, written out here as the fixture
$legacy->exec("CREATE TABLE students (student_id INTEGER PRIMARY KEY, student_name TEXT NOT NULL, student_email TEXT NOT NULL UNIQUE);
CREATE TABLE tutoring_requests (request_id INTEGER PRIMARY KEY, student_id INTEGER NOT NULL, course TEXT NOT NULL, topic TEXT NOT NULL, preferred_date TEXT NOT NULL, preferred_time TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'Open' CHECK(status IN ('Open','Cancelled','Assigned','Completed')), created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(student_id) REFERENCES students(student_id));
INSERT INTO students(student_name,student_email) VALUES('Maria Lopez','maria.lopez@example.com');
INSERT INTO tutoring_requests(student_id,course,topic,preferred_date,preferred_time) VALUES(1,'IS 6465','PHP arrays','2026-11-03','Morning');");
$legacy = null;

exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/scripts/init-db.php') . ' --db=' . escapeshellarg($file) . ' 2>&1', $out, $code);
check($code === 0 && strpos(implode(' ', $out), 'Upgraded') !== false, 'MIG-01 init-db upgrades a Lab 6 database without error');
$pdo = openDatabase($file);
$cols = []; foreach ($pdo->query('PRAGMA table_info(students)') as $c) { $cols[] = $c['name']; }
check(in_array('user_id', $cols, true) && (int)$pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE name='users'")->fetchColumn() === 1, 'MIG-02 users table and students.user_id now exist');
check((int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn() === 1 && (int)$pdo->query('SELECT COUNT(*) FROM tutoring_requests')->fetchColumn() === 1, 'MIG-03 Existing students and requests are preserved');
exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/scripts/init-db.php') . ' --db=' . escapeshellarg($file) . ' 2>&1', $out2, $code2);
check($code2 === 0 && strpos(implode(' ', $out2), 'up to date') !== false, 'MIG-04 Running init-db again is a safe no-op');
$dupIdx = false; try { $pdo->exec("INSERT INTO users(full_name,email,password_hash,role) VALUES('A','a@x.edu','h','Student'),('B','b@x.edu','h','Student')"); $pdo->exec('UPDATE students SET user_id=1'); $pdo->exec("INSERT INTO students(student_name,student_email,user_id) VALUES('Dup','dup@x.edu',1)"); } catch (PDOException $e) { $dupIdx = true; }
check($dupIdx, 'MIG-05 students.user_id is unique after the upgrade (one account per student row)');
finishTests();
