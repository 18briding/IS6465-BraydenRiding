<?php
// SQL for the USERS table only (architecture rule 2). No HTML, no business rules.
// Every function takes the PDO connection and uses prepared statements.

define('USER_PUBLIC_COLUMNS', 'user_id, full_name, email, role, status, created_at');

// Includes password_hash: only for login / password-change code paths.
function findUserWithHashByEmail($pdo, $email) {
    $stmt = $pdo->prepare('SELECT user_id, full_name, email, password_hash, role, status, created_at FROM users WHERE email = ?');
    $stmt->execute([$email]);
    return $stmt->fetch();
}
function findUserWithHashById($pdo, $id) {
    $stmt = $pdo->prepare('SELECT user_id, full_name, email, password_hash, role, status, created_at FROM users WHERE user_id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}
// Public lookups never return password_hash.
function findUserById($pdo, $id) {
    $stmt = $pdo->prepare('SELECT ' . USER_PUBLIC_COLUMNS . ' FROM users WHERE user_id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}
function findUserByEmail($pdo, $email) {
    $stmt = $pdo->prepare('SELECT ' . USER_PUBLIC_COLUMNS . ' FROM users WHERE email = ?');
    $stmt->execute([$email]);
    return $stmt->fetch();
}
function insertUser($pdo, $fullName, $email, $passwordHash, $role) {
    $stmt = $pdo->prepare("INSERT INTO users(full_name,email,password_hash,role,status) VALUES(?,?,?,?,'Active')");
    $stmt->execute([$fullName, $email, $passwordHash, $role]);
    return (int)$pdo->lastInsertId();
}
function updateUserProfile($pdo, $id, $fullName, $email) {
    $stmt = $pdo->prepare('UPDATE users SET full_name = ?, email = ? WHERE user_id = ?');
    $stmt->execute([$fullName, $email, $id]);
    return $stmt->rowCount() === 1;
}
function updateUserPasswordHash($pdo, $id, $hash) {
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
    $stmt->execute([$hash, $id]);
    return $stmt->rowCount() === 1;
}
function updateUserRole($pdo, $id, $role) {
    $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE user_id = ?');
    $stmt->execute([$role, $id]);
    return $stmt->rowCount() === 1;
}
function updateUserStatus($pdo, $id, $status) {
    $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE user_id = ?');
    $stmt->execute([$status, $id]);
    return $stmt->rowCount() === 1;
}
function deleteUserRow($pdo, $id) {
    $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() === 1;
}
// Escapes LIKE wildcards so a search for "%" or "_" matches literally.
function searchUsers($pdo, $term = '', $role = '', $status = '') {
    $sql = 'SELECT ' . USER_PUBLIC_COLUMNS . ' FROM users WHERE 1=1';
    $args = [];
    if ($term !== '') {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';
        $sql .= " AND (full_name LIKE ? ESCAPE '\\' OR email LIKE ? ESCAPE '\\')";
        $args[] = $like;
        $args[] = $like;
    }
    if ($role !== '') { $sql .= ' AND role = ?'; $args[] = $role; }
    if ($status !== '') { $sql .= ' AND status = ?'; $args[] = $status; }
    $stmt = $pdo->prepare($sql . ' ORDER BY full_name, user_id');
    $stmt->execute($args);
    return $stmt->fetchAll();
}
function countActiveAdministrators($pdo) {
    return (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Administrator' AND status = 'Active'")->fetchColumn();
}
// Tutoring requests that belong to this user through students.user_id.
function countRequestsForUser($pdo, $userId) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM tutoring_requests r JOIN students s ON r.student_id = s.student_id WHERE s.user_id = ?');
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}
