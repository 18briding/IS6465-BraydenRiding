<?php
// Service/repository-level tests for UM-01 .. UM-09 (UM-10 and cookie/session behavior: tests/http-tests.php).
// Run from the project root:  php tests/user-management-tests.php
// Expected results come from docs/Peer_Tutoring_Hub_User_Management design (use cases, business rules,
// acceptance tests) and docs/implementation-notes.md (assumptions for the open decisions).
require_once __DIR__ . '/test-helpers.php';
require_once dirname(__DIR__) . '/services/requestService.php';
require_once dirname(__DIR__) . '/services/userService.php';

try { $pdo = createTestDatabase(); } catch (Throwable $e) { abortTests($e->getMessage()); }

// ---- Fixtures: known starting state -------------------------------------------------------------
$adminId  = createAdministratorAccount($pdo, 'Casey Admin', 'admin@example.edu', 'AdminPass123');
$alexId   = registerUser($pdo, regInput('Alex Morgan', 'alex@example.edu', 'Student', 'AlexPass123'));
$taylorId = registerUser($pdo, regInput('Taylor Lee', 'taylor@example.edu', 'Tutor', 'TaylorPass1'));
$jordanId = registerUser($pdo, regInput('Jordan Kim', 'jordan@example.edu', 'Student', 'JordanPass1'));
$admin  = findUserById($pdo, $adminId);
$alex   = findUserById($pdo, $alexId);
$taylor = findUserById($pdo, $taylorId);
$jordan = findUserById($pdo, $jordanId);
submitRequest($pdo, requestInput('Alex Morgan', 'alex@example.edu'));   // Alex now has 1 tutoring request

echo "== UM-01 Create User Account ==" . PHP_EOL;
$u = findUserWithHashById($pdo, $alexId);
check($u['status'] === 'Active' && $u['role'] === 'Student', 'UM-01-T01 Student registration creates an Active Student user');
check($u['password_hash'] !== 'AlexPass123' && password_verify('AlexPass123', $u['password_hash']), 'UM-01-T02 Only a password hash is stored (not plaintext)');
$st = findStudentByUserId($pdo, $alexId);
check($st && $st['student_email'] === 'alex@example.edu' && $st['student_name'] === 'Alex Morgan', 'UM-01-T03 Student account is linked to a students row (user_id FK)');
check(findUserById($pdo, $taylorId)['role'] === 'Tutor' && findUserById($pdo, $taylorId)['status'] === 'Active', 'UM-01-T04 Tutor registration creates an Active Tutor user');
check(findStudentByUserId($pdo, $taylorId) === false, 'UM-01-T05 Tutor registration creates no students row');
$before = tableCount($pdo, 'users');
check(thrownBy(function () use ($pdo) { registerUser($pdo, regInput('Dup', 'alex@example.edu', 'Student')); }) === 'InvalidArgumentException', 'UM-01-T06 Duplicate email is rejected');
check(thrownBy(function () use ($pdo) { registerUser($pdo, regInput('Dup', ' ALEX@Example.edu ', 'Tutor')); }) === 'InvalidArgumentException', 'UM-01-T07 Duplicate email is rejected regardless of case/whitespace');
check(tableCount($pdo, 'users') === $before, 'UM-01-T08 Duplicate attempts add no users');
check(thrownBy(function () use ($pdo) { registerUser($pdo, regInput('Eve Root', 'eve@example.edu', 'Administrator')); }) === 'AccessDeniedException', 'UM-01-T09 Registering as Administrator is denied');
check(findUserByEmail($pdo, 'eve@example.edu') === false && tableCount($pdo, 'users') === $before, 'UM-01-T10 Denied Administrator registration changes nothing');
check(thrownBy(function () use ($pdo) { registerUser($pdo, regInput('No Role', 'norole@example.edu', '')); }) === 'InvalidArgumentException', 'UM-01-T11 Missing role is a validation error');
check(thrownBy(function () use ($pdo) { registerUser($pdo, regInput('Bad Role', 'badrole@example.edu', 'Superuser')); }) === 'InvalidArgumentException', 'UM-01-T12 Unknown role is a validation error');
$mm = regInput('Mismatch', 'mm@example.edu'); $mm['confirm_password'] = 'Different123';
check(thrownBy(function () use ($pdo, $mm) { registerUser($pdo, $mm); }) === 'InvalidArgumentException', 'UM-01-T13 Password/confirmation mismatch is rejected');
check(thrownBy(function () use ($pdo) { registerUser($pdo, regInput('Short', 'short@example.edu', 'Student', 'abc123')); }) === 'InvalidArgumentException', 'UM-01-T14 Password shorter than 8 characters is rejected');
check(thrownBy(function () use ($pdo) { registerUser($pdo, regInput('Bad Email', 'not-an-email', 'Student')); }) === 'InvalidArgumentException', 'UM-01-T15 Invalid email is rejected');
check(thrownBy(function () use ($pdo) { registerUser($pdo, regInput('   ', 'blank@example.edu', 'Student')); }) === 'InvalidArgumentException', 'UM-01-T16 Blank name is rejected');
check(findUserByEmail($pdo, 'mm@example.edu') === false && findUserByEmail($pdo, 'blank@example.edu') === false, 'UM-01-T17 Rejected registrations leave no user row');
$odd = registerUser($pdo, regInput("Sam O'Brien \"<b>X</b>\"", 'sam@example.edu', 'Tutor'));
check(findUserById($pdo, $odd)['full_name'] === "Sam O'Brien \"<b>X</b>\"", 'UM-01-T18 Quotes and markup in the name are stored verbatim (encoded only on output)');
$pdo->exec("INSERT INTO students(student_name,student_email) VALUES('Maria Lopez','maria@example.edu')");
$mariaStudent = (int)$pdo->lastInsertId();
$pdo->exec("INSERT INTO tutoring_requests(student_id,course,topic,preferred_date,preferred_time) VALUES($mariaStudent,'IS 6465','x','2030-01-01','Morning')");
$mariaId = registerUser($pdo, regInput('Maria Lopez', 'maria@example.edu', 'Student'));
check((int)findStudentByUserId($pdo, $mariaId)['student_id'] === $mariaStudent && countRequestsForUser($pdo, $mariaId) === 1, 'UM-01-T19 Registering adopts an existing unlinked student row and keeps its requests');
// Transaction: a students row already linked to another account makes the whole registration roll back.
$pdo->exec("INSERT INTO students(student_name,student_email,user_id) VALUES('Taken','taken@example.edu',$taylorId)");
$beforeRollback = tableCount($pdo, 'users');
$threw = thrownBy(function () use ($pdo) { registerUser($pdo, regInput('Taken Person', 'taken@example.edu', 'Student')); });
check($threw !== '' && tableCount($pdo, 'users') === $beforeRollback && findUserByEmail($pdo, 'taken@example.edu') === false, 'UM-01-T20 Failure after the user insert rolls back (no partial write)');
$pdo->exec("DELETE FROM students WHERE student_email='taken@example.edu'");

echo "== UM-02 View Own Account ==" . PHP_EOL;
$me = getOwnAccount($pdo, $alex);
check($me['user_id'] == $alexId && $me['full_name'] === 'Alex Morgan' && $me['email'] === 'alex@example.edu' && $me['role'] === 'Student' && $me['status'] === 'Active', 'UM-02-T01 Own profile shows name, email, role and status');
check(!array_key_exists('password_hash', $me), 'UM-02-T02 Profile data never includes the password hash');
check(getOwnAccount($pdo, $taylor)['email'] === 'taylor@example.edu' && getOwnAccount($pdo, $taylor)['user_id'] == $taylorId, 'UM-02-T03 Each user sees only their own record');
check(thrownBy(function () use ($pdo, $alex, $jordanId) { getUserAccountDetails($pdo, $alex, $jordanId); }) === 'AccessDeniedException', 'UM-02-T04 A Student cannot open another user\'s account');
check(thrownBy(function () use ($pdo) { getOwnAccount($pdo, ['user_id' => 99999]); }) === 'AccessDeniedException', 'UM-02-T05 Unknown/expired identity is denied');
check(thrownBy(function () use ($pdo) { getOwnAccount($pdo, []); }) === 'AccessDeniedException', 'UM-02-T06 Missing identity is denied');

echo "== UM-03 View and Search User Accounts ==" . PHP_EOL;
check(count(searchUserAccounts($pdo, $admin, [])) === tableCount($pdo, 'users'), 'UM-03-T01 Admin with no criteria sees all accounts');
$r = searchUserAccounts($pdo, $admin, ['q' => 'alex']);
check(count($r) === 1 && $r[0]['email'] === 'alex@example.edu', 'UM-03-T02 Search by name fragment returns matching account');
$r = searchUserAccounts($pdo, $admin, ['q' => 'taylor@']);
check(count($r) === 1 && $r[0]['full_name'] === 'Taylor Lee', 'UM-03-T03 Search by email fragment returns matching account');
$tutors = searchUserAccounts($pdo, $admin, ['role' => 'Tutor']);
$allTutorsOk = true; foreach ($tutors as $t) { if ($t['role'] !== 'Tutor') { $allTutorsOk = false; } }
check(count($tutors) === 2 && $allTutorsOk, 'UM-03-T04 Role filter returns only that role (Taylor, Sam)');
check(count(searchUserAccounts($pdo, $admin, ['status' => 'Inactive'])) === 0, 'UM-03-T05 Status filter with no matches returns an empty list');
check(count(searchUserAccounts($pdo, $admin, ['q' => 'a', 'role' => 'Student', 'status' => 'Active'])) === 3, 'UM-03-T06 Combined search+role+status filters (Alex, Jordan, Maria)');
check(searchUserAccounts($pdo, $admin, ['q' => 'zzz-nobody']) === [], 'UM-03-T07 No matches returns an empty result');
check(searchUserAccounts($pdo, $admin, ['q' => '%']) === [], 'UM-03-T08 A literal % does not act as a wildcard');
check(searchUserAccounts($pdo, $admin, ['q' => "' OR 1=1 --"]) === [] && tableCount($pdo, 'users') === 6, 'UM-03-T09 SQL injection text is treated as data');
foreach ([['Student', $alex], ['Tutor', $taylor]] as $pair) {
    $who = $pair[1];
    check(thrownBy(function () use ($pdo, $who) { searchUserAccounts($pdo, $who, []); }) === 'AccessDeniedException', 'UM-03-T10 ' . $pair[0] . ' cannot search accounts (unauthorized denied)');
}
check(thrownBy(function () use ($pdo, $admin) { searchUserAccounts($pdo, $admin, ['role' => 'Root']); }) === 'InvalidArgumentException', 'UM-03-T11 Invalid role filter is rejected');
check(!array_key_exists('password_hash', searchUserAccounts($pdo, $admin, [])[0]), 'UM-03-T12 Search results never include password hashes');
$d = getUserAccountDetails($pdo, $admin, $alexId);
check($d['email'] === 'alex@example.edu' && $d['request_count'] === 1, 'UM-03-T13 Account details include the related request count');
check(thrownBy(function () use ($pdo, $admin) { getUserAccountDetails($pdo, $admin, 424242); }) === 'UserNotFoundException', 'UM-03-T14 Unknown account id is Not Found');

echo "== UM-04 Update Own Account ==" . PHP_EOL;
$others = rowSnapshot($pdo, "SELECT user_id,full_name,email,role,status FROM users WHERE user_id <> $alexId ORDER BY user_id");
$upd = updateOwnAccount($pdo, $alex, ['full_name' => 'Alexandra Morgan', 'email' => 'Alexandra@Example.edu']);
check($upd['full_name'] === 'Alexandra Morgan' && $upd['email'] === 'alexandra@example.edu', 'UM-04-T01 Name and email update succeeds (email normalized)');
check(findStudentByUserId($pdo, $alexId)['student_email'] === 'alexandra@example.edu', 'UM-04-T02 The linked students row is kept in step');
check(rowSnapshot($pdo, "SELECT user_id,full_name,email,role,status FROM users WHERE user_id <> $alexId ORDER BY user_id") === $others, 'UM-04-T03 No other account changed');
$alex = findUserById($pdo, $alexId);
check(thrownBy(function () use ($pdo, $alex) { updateOwnAccount($pdo, $alex, ['full_name' => 'Alexandra Morgan', 'email' => 'alexandra@example.edu', 'role' => 'Administrator']); }) === 'AccessDeniedException', 'UM-04-T04 Role modification through the profile is denied');
check(findUserById($pdo, $alexId)['role'] === 'Student', 'UM-04-T05 Role is unchanged after the denied attempt');
check(thrownBy(function () use ($pdo, $taylor) { updateOwnAccount($pdo, $taylor, ['full_name' => 'Taylor Lee', 'email' => 'taylor@example.edu', 'status' => 'Inactive']); }) === 'AccessDeniedException' && findUserById($pdo, $taylorId)['status'] === 'Active', 'UM-04-T06 Status modification through the profile is denied');
check(thrownBy(function () use ($pdo, $alex) { updateOwnAccount($pdo, $alex, ['full_name' => 'Alexandra', 'email' => 'taylor@example.edu']); }) === 'InvalidArgumentException' && findUserById($pdo, $alexId)['full_name'] === 'Alexandra Morgan', 'UM-04-T07 Duplicate email is rejected and nothing changes');
check(thrownBy(function () use ($pdo, $alex) { updateOwnAccount($pdo, $alex, ['full_name' => 'Alexandra', 'email' => 'bad']); }) === 'InvalidArgumentException', 'UM-04-T08 Invalid email is rejected');
check(thrownBy(function () use ($pdo, $alex) { updateOwnAccount($pdo, $alex, ['full_name' => '', 'email' => 'alexandra@example.edu']); }) === 'InvalidArgumentException', 'UM-04-T09 Blank name is rejected');
check(updateOwnAccount($pdo, $alex, ['full_name' => 'Alexandra Morgan', 'email' => 'alexandra@example.edu'])['email'] === 'alexandra@example.edu', 'UM-04-T10 Re-saving your own current email is not a duplicate');
check(thrownBy(function () use ($pdo) { updateOwnAccount($pdo, ['user_id' => 99999], ['full_name' => 'X', 'email' => 'x@example.edu']); }) === 'AccessDeniedException', 'UM-04-T11 Update without a valid identity is denied');
$alex = findUserById($pdo, $alexId);

echo "== UM-05 Change Password ==" . PHP_EOL;
$oldHash = findUserWithHashById($pdo, $jordanId)['password_hash'];
check(changePassword($pdo, $jordan, ['current_password' => 'JordanPass1', 'new_password' => 'NewJordan456', 'confirm_password' => 'NewJordan456']) === true, 'UM-05-T01 Correct current password and valid new password succeeds');
check(authenticateUser($pdo, 'jordan@example.edu', 'NewJordan456') !== null, 'UM-05-T02 The new password works for login');
check(authenticateUser($pdo, 'jordan@example.edu', 'JordanPass1') === null, 'UM-05-T03 The old password no longer works');
$newHash = findUserWithHashById($pdo, $jordanId)['password_hash'];
check($newHash !== $oldHash && $newHash !== 'NewJordan456', 'UM-05-T04 A new hash (not plaintext) is stored');
check(thrownBy(function () use ($pdo, $jordan) { changePassword($pdo, $jordan, ['current_password' => 'WRONG', 'new_password' => 'Another789!', 'confirm_password' => 'Another789!']); }) === 'InvalidArgumentException' && findUserWithHashById($pdo, $jordanId)['password_hash'] === $newHash, 'UM-05-T05 Incorrect current password is rejected, hash unchanged');
check(thrownBy(function () use ($pdo, $jordan) { changePassword($pdo, $jordan, ['current_password' => 'NewJordan456', 'new_password' => 'Another789!', 'confirm_password' => 'Mismatch789!']); }) === 'InvalidArgumentException' && findUserWithHashById($pdo, $jordanId)['password_hash'] === $newHash, 'UM-05-T06 New/confirm mismatch is rejected, hash unchanged');
check(thrownBy(function () use ($pdo, $jordan) { changePassword($pdo, $jordan, ['current_password' => 'NewJordan456', 'new_password' => 'short', 'confirm_password' => 'short']); }) === 'InvalidArgumentException', 'UM-05-T07 A too-short new password is rejected');
check(authenticateUser($pdo, 'alexandra@example.edu', 'AlexPass123') !== null, 'UM-05-T08 Another user\'s password is unaffected');

echo "== UM-06 Update User Role ==" . PHP_EOL;
$r = changeUserRole($pdo, $admin, $taylorId, 'Student', true);
check($r['role'] === 'Student' && findStudentByUserId($pdo, $taylorId) !== false, 'UM-06-T01 Admin changes Tutor to Student (and a students row exists)');
check(changeUserRole($pdo, $admin, $taylorId, 'Tutor', true)['role'] === 'Tutor', 'UM-06-T02 Admin changes Student back to Tutor (students row and history retained)');
$snap = rowSnapshot($pdo, 'SELECT user_id,role FROM users ORDER BY user_id');
check(thrownBy(function () use ($pdo, $alex, $taylorId) { changeUserRole($pdo, $alex, $taylorId, 'Administrator', true); }) === 'AccessDeniedException', 'UM-06-T03 A Student cannot change a role (unauthorized change denied)');
check(thrownBy(function () use ($pdo, $alex, $alexId) { changeUserRole($pdo, $alex, $alexId, 'Administrator', true); }) === 'AccessDeniedException', 'UM-06-T04 A Student cannot promote themselves');
check(thrownBy(function () use ($pdo, $taylor, $taylorId) { changeUserRole($pdo, $taylor, $taylorId, 'Administrator', true); }) === 'AccessDeniedException', 'UM-06-T05 A Tutor cannot promote themselves');
check(rowSnapshot($pdo, 'SELECT user_id,role FROM users ORDER BY user_id') === $snap, 'UM-06-T06 Denied attempts changed no roles');
check(thrownBy(function () use ($pdo, $admin, $taylorId) { changeUserRole($pdo, $admin, $taylorId, 'Administrator', false); }) === 'InvalidArgumentException' && findUserById($pdo, $taylorId)['role'] === 'Tutor', 'UM-06-T07 An unconfirmed change is not applied');
check(thrownBy(function () use ($pdo, $admin, $taylorId) { changeUserRole($pdo, $admin, $taylorId, 'Owner', true); }) === 'InvalidArgumentException', 'UM-06-T08 An unknown role value is rejected');
check(thrownBy(function () use ($pdo, $admin, $taylorId) { changeUserRole($pdo, $admin, $taylorId, 'Tutor', true); }) === 'BlockedOperationException', 'UM-06-T09 Setting the role it already has is a prohibited no-op');
check(thrownBy(function () use ($pdo, $admin, $adminId) { changeUserRole($pdo, $admin, $adminId, 'Tutor', true); }) === 'BlockedOperationException' && findUserById($pdo, $adminId)['role'] === 'Administrator', 'UM-06-T10 The last active administrator cannot be demoted');
check(thrownBy(function () use ($pdo, $admin) { changeUserRole($pdo, $admin, 424242, 'Tutor', true); }) === 'UserNotFoundException', 'UM-06-T11 Unknown account is Not Found');
changeUserRole($pdo, $admin, $taylorId, 'Administrator', true);
check(findUserById($pdo, $taylorId)['role'] === 'Administrator', 'UM-06-T12 Admin can promote a user to Administrator');
check(changeUserRole($pdo, $admin, $taylorId, 'Tutor', true)['role'] === 'Tutor', 'UM-06-T13 With two active administrators one may be demoted');
$pdo->exec("UPDATE users SET status='Inactive' WHERE user_id=$jordanId");
check(thrownBy(function () use ($pdo, $jordan, $taylorId) { changeUserRole($pdo, $jordan, $taylorId, 'Student', true); }) === 'AccessDeniedException', 'UM-06-T14 An inactive actor is denied (fresh status check)');
$pdo->exec("UPDATE users SET status='Active' WHERE user_id=$jordanId");

echo "== UM-07 Activate or Deactivate Account ==" . PHP_EOL;
check(setUserStatus($pdo, $admin, $jordanId, 'Inactive', true)['status'] === 'Inactive', 'UM-07-T01 Admin deactivates an account');
check(authenticateUser($pdo, 'jordan@example.edu', 'NewJordan456') === null, 'UM-07-T02 An inactive user cannot log in');
check(thrownBy(function () use ($pdo, $jordan) { getOwnAccount($pdo, $jordan); }) === 'AccessDeniedException', 'UM-07-T03 An inactive user\'s existing identity is refused by the services');
check(setUserStatus($pdo, $admin, $jordanId, 'Active', true)['status'] === 'Active' && authenticateUser($pdo, 'jordan@example.edu', 'NewJordan456') !== null, 'UM-07-T04 Reactivation restores login');
check(thrownBy(function () use ($pdo, $alex, $jordanId) { setUserStatus($pdo, $alex, $jordanId, 'Inactive', true); }) === 'AccessDeniedException' && findUserById($pdo, $jordanId)['status'] === 'Active', 'UM-07-T05 A non-administrator cannot change status');
check(thrownBy(function () use ($pdo, $admin, $jordanId) { setUserStatus($pdo, $admin, $jordanId, 'Active', true); }) === 'BlockedOperationException', 'UM-07-T06 Activating an Active account is an invalid transition');
check(thrownBy(function () use ($pdo, $admin, $jordanId) { setUserStatus($pdo, $admin, $jordanId, 'Suspended', true); }) === 'InvalidArgumentException', 'UM-07-T07 An unknown status value is rejected');
check(thrownBy(function () use ($pdo, $admin, $jordanId) { setUserStatus($pdo, $admin, $jordanId, 'Inactive', false); }) === 'InvalidArgumentException' && findUserById($pdo, $jordanId)['status'] === 'Active', 'UM-07-T08 An unconfirmed status change is not applied');
check(thrownBy(function () use ($pdo, $admin, $adminId) { setUserStatus($pdo, $admin, $adminId, 'Inactive', true); }) === 'BlockedOperationException' && findUserById($pdo, $adminId)['status'] === 'Active', 'UM-07-T09 The last active administrator cannot be deactivated');
check(thrownBy(function () use ($pdo, $admin) { setUserStatus($pdo, $admin, 424242, 'Inactive', true); }) === 'UserNotFoundException', 'UM-07-T10 Unknown account is Not Found');

echo "== UM-08 Delete User Account ==" . PHP_EOL;
$reqBefore = tableCount($pdo, 'tutoring_requests');
check(thrownBy(function () use ($pdo, $admin, $alexId) { deleteUserAccount($pdo, $admin, $alexId, true); }) === 'BlockedOperationException', 'UM-08-T01 Deletion is blocked when the account has tutoring requests');
check(findUserById($pdo, $alexId) !== false && findStudentByUserId($pdo, $alexId) !== false && tableCount($pdo, 'tutoring_requests') === $reqBefore, 'UM-08-T02 Blocked deletion removes no user, student or request rows');
check(thrownBy(function () use ($pdo, $admin, $jordanId) { deleteUserAccount($pdo, $admin, $jordanId, false); }) === 'BlockedOperationException' && findUserById($pdo, $jordanId) !== false, 'UM-08-T03 An unconfirmed (cancelled) deletion changes nothing');
check(thrownBy(function () use ($pdo, $alex, $jordanId) { deleteUserAccount($pdo, $alex, $jordanId, true); }) === 'AccessDeniedException' && findUserById($pdo, $jordanId) !== false, 'UM-08-T04 A non-administrator cannot delete accounts');
check(thrownBy(function () use ($pdo, $admin, $adminId) { deleteUserAccount($pdo, $admin, $adminId, true); }) === 'BlockedOperationException' && findUserById($pdo, $adminId) !== false, 'UM-08-T05 An administrator cannot delete their own account');
check(deleteUserAccount($pdo, $admin, $jordanId, true) === true && findUserById($pdo, $jordanId) === false, 'UM-08-T06 An account with no related requests can be deleted');
check(findStudentByUserId($pdo, $jordanId) === false && tableCount($pdo, 'tutoring_requests') === $reqBefore, 'UM-08-T07 The empty students row goes with it; no requests are removed');
$tutorOnly = registerUser($pdo, regInput('Pat Tutor', 'pat@example.edu', 'Tutor'));
check(deleteUserAccount($pdo, $admin, $tutorOnly, true) === true && findUserById($pdo, $tutorOnly) === false, 'UM-08-T08 A Tutor account with no records can be deleted');
check(findUserById($pdo, $alexId) !== false && findUserById($pdo, $taylorId) !== false, 'UM-08-T09 Unrelated accounts are unchanged');
check(thrownBy(function () use ($pdo, $admin) { deleteUserAccount($pdo, $admin, 424242, true); }) === 'UserNotFoundException', 'UM-08-T10 Deleting an unknown account is Not Found');
$second = createAdministratorAccount($pdo, 'Second Admin', 'admin2@example.edu', 'AdminPass456');
$pdo->exec("UPDATE users SET status='Inactive' WHERE user_id=$second");
$onlyActiveAdmin = thrownBy(function () use ($pdo, $admin, $adminId, $second) { deleteUserAccount($pdo, findUserById($pdo, $second) ?: $admin, $adminId, true); });
check($onlyActiveAdmin === 'AccessDeniedException', 'UM-08-T11 An inactive administrator cannot act, so cannot delete the last active admin');
$pdo->exec("UPDATE users SET status='Active' WHERE user_id=$second");
check(deleteUserAccount($pdo, $admin, $second, true) === true, 'UM-08-T12 A second administrator can be deleted while another active one remains');
try {   // database-level safety net: FK stops deleting a user whose students row still exists
    $pdo->exec("DELETE FROM users WHERE user_id=$alexId");
    check(false, 'UM-08-T13 Foreign key prevents orphaning students.user_id');
} catch (PDOException $e) { check(true, 'UM-08-T13 Foreign key prevents orphaning students.user_id'); }

echo "== UM-09 Log In (credential verification) ==" . PHP_EOL;
$ok = authenticateUser($pdo, 'alexandra@example.edu', 'AlexPass123');
check($ok !== null && $ok['user_id'] == $alexId && $ok['role'] === 'Student', 'UM-09-T01 Valid credentials return the account');
check(!array_key_exists('password_hash', $ok), 'UM-09-T02 The returned account has no password hash');
check(authenticateUser($pdo, 'alexandra@example.edu', 'WrongPass123') === null, 'UM-09-T03 Wrong password is rejected');
check(authenticateUser($pdo, 'nobody@example.edu', 'AlexPass123') === null, 'UM-09-T04 Unknown email is rejected');
check(authenticateUser($pdo, ' ALEXANDRA@example.EDU ', 'AlexPass123') !== null, 'UM-09-T05 Email matching ignores case and whitespace');
$pdo->exec("UPDATE users SET status='Inactive' WHERE user_id=$alexId");
check(authenticateUser($pdo, 'alexandra@example.edu', 'AlexPass123') === null, 'UM-09-T06 Inactive account cannot log in even with the right password');
$pdo->exec("UPDATE users SET status='Active' WHERE user_id=$alexId");
check(authenticateUser($pdo, "' OR '1'='1", "' OR '1'='1") === null, 'UM-09-T07 SQL injection in credentials does not authenticate');
check(authenticateUser($pdo, 'alexandra@example.edu', '') === null, 'UM-09-T08 An empty password is rejected');
$legacy = password_hash('LegacyPass1', PASSWORD_BCRYPT, ['cost' => 4]);
$pdo->exec("UPDATE users SET password_hash='" . $legacy . "' WHERE user_id=$alexId");
authenticateUser($pdo, 'alexandra@example.edu', 'LegacyPass1');
check(findUserWithHashById($pdo, $alexId)['password_hash'] !== $legacy && authenticateUser($pdo, 'alexandra@example.edu', 'LegacyPass1') !== null, 'UM-09-T09 A weaker legacy hash is upgraded after a successful login');

finishTests();
