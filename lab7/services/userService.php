<?php
// Business rules and transactions for user management (UM-01 .. UM-09).
// Pages call these functions; these functions call repositories. No HTML, no session code here.
require_once dirname(__DIR__) . '/includes/userValidation.php';
require_once dirname(__DIR__) . '/repositories/studentPDO.php';
require_once dirname(__DIR__) . '/repositories/userPDO.php';

// Validation problems use PHP's InvalidArgumentException (HTTP 422).
class AccessDeniedException extends RuntimeException {}      // HTTP 403: actor not allowed
class UserNotFoundException extends RuntimeException {}      // HTTP 404
class BlockedOperationException extends RuntimeException {}  // HTTP 409: rule blocks the change

// Loads the actor fresh from the database and requires an Active account.
// Never trust a role that arrived from a form, cookie or stale session (handout section 20).
function loadActiveActor($pdo, $actor) {
    $id = is_array($actor) && isset($actor['user_id']) ? (int)$actor['user_id'] : 0;
    $fresh = $id > 0 ? findUserById($pdo, $id) : false;
    if (!$fresh || $fresh['status'] !== 'Active') {
        throw new AccessDeniedException('You must be signed in with an active account.');
    }
    return $fresh;
}
function requireAdministratorActor($pdo, $actor) {
    $fresh = loadActiveActor($pdo, $actor);
    if ($fresh['role'] !== 'Administrator') {
        throw new AccessDeniedException('Administrator access is required.');
    }
    return $fresh;
}
function isUniqueViolation($e) {
    return $e instanceof PDOException && strpos($e->getMessage(), 'UNIQUE') !== false;
}

// Student accounts own a row in students (linked by user_id) so tutoring requests can attach to them.
// A pre-existing unlinked students row with the same email is adopted, keeping its request history.
function ensureStudentRecord($pdo, $user) {
    $linked = findStudentByUserId($pdo, (int)$user['user_id']);
    if ($linked) { return (int)$linked['student_id']; }
    $byEmail = findStudentRowByEmail($pdo, $user['email']);
    if ($byEmail && $byEmail['user_id'] === null) {
        linkStudentToUser($pdo, (int)$byEmail['student_id'], $user['full_name'], (int)$user['user_id']);
        return (int)$byEmail['student_id'];
    }
    if ($byEmail) { throw new RuntimeException('Student record already linked to another account.'); }
    return insertLinkedStudent($pdo, $user['full_name'], $user['email'], (int)$user['user_id']);
}

// ---------------------------------------------------------------- UM-01 Create User Account
function registerUser($pdo, $input) {
    $name = trim((string)(isset($input['full_name']) ? $input['full_name'] : ''));
    $email = normalizeEmail(isset($input['email']) ? $input['email'] : '');
    $password = (string)(isset($input['password']) ? $input['password'] : '');
    $confirm = (string)(isset($input['confirm_password']) ? $input['confirm_password'] : '');
    $role = trim((string)(isset($input['role']) ? $input['role'] : ''));

    // Unauthorized role first: public registration can never create an Administrator.
    if (in_array($role, allRoles(), true) && !in_array($role, selfRegistrationRoles(), true)) {
        throw new AccessDeniedException('That account type cannot be selected during registration.');
    }
    $errors = [];
    if ($msg = validateFullName($name)) { $errors[] = $msg; }
    if ($msg = validateEmailAddress($email)) { $errors[] = $msg; }
    if ($msg = validateNewPassword($password)) { $errors[] = $msg; }
    if ($password !== $confirm) { $errors[] = 'Password and confirmation do not match.'; }
    if (!in_array($role, selfRegistrationRoles(), true)) { $errors[] = 'Choose Student or Tutor.'; }
    if ($errors) { throw new InvalidArgumentException(implode(' ', $errors)); }
    if (findUserByEmail($pdo, $email)) {
        throw new InvalidArgumentException('An account with that email already exists.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);   // only the hash is ever stored
    $pdo->beginTransaction();
    try {
        $userId = insertUser($pdo, $name, $email, $hash, $role);   // status is always Active
        if ($role === 'Student') {
            ensureStudentRecord($pdo, ['user_id' => $userId, 'full_name' => $name, 'email' => $email]);
        }
        $pdo->commit();
        return $userId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        if (isUniqueViolation($e)) { throw new InvalidArgumentException('An account with that email already exists.'); }
        throw $e;
    }
}

// CLI-only bootstrap for the first Administrator (see scripts/create-admin.php). Not reachable from the web.
function createAdministratorAccount($pdo, $name, $email, $password) {
    $email = normalizeEmail($email);
    $errors = [];
    if ($msg = validateFullName($name)) { $errors[] = $msg; }
    if ($msg = validateEmailAddress($email)) { $errors[] = $msg; }
    if ($msg = validateNewPassword($password)) { $errors[] = $msg; }
    if ($errors) { throw new InvalidArgumentException(implode(' ', $errors)); }
    return insertUser($pdo, trim($name), $email, password_hash($password, PASSWORD_DEFAULT), 'Administrator');
}

// ---------------------------------------------------------------- UM-09 Log In (credential check)
// Returns the public user record, or null. One result for unknown email, wrong password and
// inactive account so the response never reveals which emails exist (handout section 7).
function authenticateUser($pdo, $email, $password) {
    $row = findUserWithHashByEmail($pdo, normalizeEmail($email));
    $hash = $row ? $row['password_hash'] : password_hash('placeholder-for-timing', PASSWORD_DEFAULT);
    $ok = password_verify((string)$password, $hash);
    if (!$row || !$ok || $row['status'] !== 'Active') { return null; }
    if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
        updateUserPasswordHash($pdo, (int)$row['user_id'], password_hash((string)$password, PASSWORD_DEFAULT));
    }
    unset($row['password_hash']);
    return $row;
}

// ---------------------------------------------------------------- UM-02 View Own Account
// Takes no target id: it can only ever return the signed-in user's own record.
function getOwnAccount($pdo, $actor) {
    $actor = loadActiveActor($pdo, $actor);
    return $actor;   // user_id, full_name, email, role, status, created_at (no hash)
}

// ---------------------------------------------------------------- UM-03 View and Search User Accounts
function searchUserAccounts($pdo, $actor, $filters = []) {
    requireAdministratorActor($pdo, $actor);
    $term = trim((string)(isset($filters['q']) ? $filters['q'] : ''));
    $role = (string)(isset($filters['role']) ? $filters['role'] : '');
    $status = (string)(isset($filters['status']) ? $filters['status'] : '');
    if ($role !== '' && !in_array($role, allRoles(), true)) { throw new InvalidArgumentException('Invalid role filter.'); }
    if ($status !== '' && !in_array($status, allStatuses(), true)) { throw new InvalidArgumentException('Invalid status filter.'); }
    return searchUsers($pdo, $term, $role, $status);
}
function getUserAccountDetails($pdo, $actor, $targetId) {
    requireAdministratorActor($pdo, $actor);
    $user = findUserById($pdo, (int)$targetId);
    if (!$user) { throw new UserNotFoundException('User not found.'); }
    $user['request_count'] = countRequestsForUser($pdo, (int)$targetId);
    return $user;
}

// ---------------------------------------------------------------- UM-04 Update Own Account
// Name and email only. Any attempt to send a different role or status is denied outright.
function updateOwnAccount($pdo, $actor, $input) {
    $actor = loadActiveActor($pdo, $actor);
    foreach (['role', 'status'] as $protected) {
        if (isset($input[$protected]) && (string)$input[$protected] !== $actor[$protected]) {
            throw new AccessDeniedException('Role and status cannot be changed from your profile.');
        }
    }
    $name = trim((string)(isset($input['full_name']) ? $input['full_name'] : ''));
    $email = normalizeEmail(isset($input['email']) ? $input['email'] : '');
    $errors = [];
    if ($msg = validateFullName($name)) { $errors[] = $msg; }
    if ($msg = validateEmailAddress($email)) { $errors[] = $msg; }
    if ($errors) { throw new InvalidArgumentException(implode(' ', $errors)); }
    $other = findUserByEmail($pdo, $email);
    if ($other && (int)$other['user_id'] !== (int)$actor['user_id']) {
        throw new InvalidArgumentException('That email is already used by another account.');
    }
    $pdo->beginTransaction();
    try {
        updateUserProfile($pdo, (int)$actor['user_id'], $name, $email);
        updateStudentIdentityByUserId($pdo, (int)$actor['user_id'], $name, $email);   // keep students row in step
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        if (isUniqueViolation($e)) { throw new InvalidArgumentException('That email is already used by another account.'); }
        throw $e;
    }
    return findUserById($pdo, (int)$actor['user_id']);
}

// ---------------------------------------------------------------- UM-05 Change Password
function changePassword($pdo, $actor, $input) {
    $actor = loadActiveActor($pdo, $actor);
    $row = findUserWithHashById($pdo, (int)$actor['user_id']);
    $current = (string)(isset($input['current_password']) ? $input['current_password'] : '');
    $new = (string)(isset($input['new_password']) ? $input['new_password'] : '');
    $confirm = (string)(isset($input['confirm_password']) ? $input['confirm_password'] : '');
    if (!$row || !password_verify($current, $row['password_hash'])) {
        throw new InvalidArgumentException('Current password is incorrect.');
    }
    $errors = [];
    if ($msg = validateNewPassword($new)) { $errors[] = $msg; }
    if ($new !== $confirm) { $errors[] = 'New password and confirmation do not match.'; }
    if ($errors) { throw new InvalidArgumentException(implode(' ', $errors)); }
    updateUserPasswordHash($pdo, (int)$actor['user_id'], password_hash($new, PASSWORD_DEFAULT));
    return true;
}

// ---------------------------------------------------------------- UM-06 Update User Role
function changeUserRole($pdo, $actor, $targetId, $newRole, $confirmed) {
    $actor = requireAdministratorActor($pdo, $actor);
    if (!$confirmed) { throw new InvalidArgumentException('Confirm the role change to continue.'); }
    if (!in_array($newRole, allRoles(), true)) { throw new InvalidArgumentException('Choose a valid role.'); }
    $pdo->beginTransaction();
    try {
        $target = findUserById($pdo, (int)$targetId);
        if (!$target) { throw new UserNotFoundException('User not found.'); }
        if ($target['role'] === $newRole) { throw new BlockedOperationException('The account already has that role.'); }
        if ($target['role'] === 'Administrator' && $target['status'] === 'Active' && countActiveAdministrators($pdo) <= 1) {
            throw new BlockedOperationException('The last active administrator cannot be changed to another role.');
        }
        updateUserRole($pdo, (int)$target['user_id'], $newRole);
        if ($newRole === 'Student') { ensureStudentRecord($pdo, $target); }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        throw $e;
    }
    return findUserById($pdo, (int)$targetId);
}

// ---------------------------------------------------------------- UM-07 Activate or Deactivate Account
function setUserStatus($pdo, $actor, $targetId, $newStatus, $confirmed) {
    $actor = requireAdministratorActor($pdo, $actor);
    if (!$confirmed) { throw new InvalidArgumentException('Confirm the status change to continue.'); }
    if (!in_array($newStatus, allStatuses(), true)) { throw new InvalidArgumentException('Choose Active or Inactive.'); }
    $pdo->beginTransaction();
    try {
        $target = findUserById($pdo, (int)$targetId);
        if (!$target) { throw new UserNotFoundException('User not found.'); }
        if ($target['status'] === $newStatus) { throw new BlockedOperationException('The account is already ' . $newStatus . '.'); }
        if ($newStatus === 'Inactive' && $target['role'] === 'Administrator' && countActiveAdministrators($pdo) <= 1) {
            throw new BlockedOperationException('The last active administrator cannot be deactivated.');
        }
        updateUserStatus($pdo, (int)$target['user_id'], $newStatus);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        throw $e;
    }
    return findUserById($pdo, (int)$targetId);
}

// ---------------------------------------------------------------- UM-08 Delete User Account
// The permanent-deletion policy was OPEN in the design. Conservative interim policy:
// delete only when the account has NO tutoring requests; never silently remove dependent records.
function describeDeletionImpact($pdo, $actor, $targetId) {
    return getUserAccountDetails($pdo, $actor, $targetId);   // includes request_count for the warning screen
}
function deleteUserAccount($pdo, $actor, $targetId, $confirmed) {
    $actor = requireAdministratorActor($pdo, $actor);
    if (!$confirmed) { throw new BlockedOperationException('Deletion was not confirmed. Nothing was changed.'); }
    $pdo->beginTransaction();
    try {
        $target = findUserById($pdo, (int)$targetId);   // recheck relationships inside the transaction
        if (!$target) { throw new UserNotFoundException('User not found.'); }
        if ((int)$target['user_id'] === (int)$actor['user_id']) {
            throw new BlockedOperationException('You cannot delete your own account.');
        }
        if ($target['role'] === 'Administrator' && $target['status'] === 'Active' && countActiveAdministrators($pdo) <= 1) {
            throw new BlockedOperationException('The last active administrator cannot be deleted.');
        }
        $n = countRequestsForUser($pdo, (int)$target['user_id']);
        if ($n > 0) {
            throw new BlockedOperationException('Deletion blocked: this account has ' . $n . ' related tutoring request(s). Deactivate it instead.');
        }
        deleteStudentByUserId($pdo, (int)$target['user_id']);   // empty students row (no requests)
        deleteUserRow($pdo, (int)$target['user_id']);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        throw $e;
    }
    return true;
}
