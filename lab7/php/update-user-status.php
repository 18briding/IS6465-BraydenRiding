<?php
// UM-07 Activate or Deactivate Account (WF-06). POST only; CSRF token, fresh administrator check and explicit confirmation are all enforced server-side.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/pageHelpers.php';
require_once __DIR__ . '/../services/userService.php';
requirePost();
verifyCsrfToken($_POST);
$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Administrator']);
$id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid user ID'); }
$confirmed = isset($_POST['confirm']) && $_POST['confirm'] === 'yes';
try {
    setUserStatus($pdo, $user, $id, (string)(isset($_POST['status']) ? $_POST['status'] : ''), $confirmed);
    setFlash('Account status updated.');
    redirectTo('manage-user.php?user_id=' . $id);
} catch (Throwable $e) {
    $code = statusForException($e);
    if ($code === 500) { error_log($e->getMessage()); }
    http_response_code($code);
    renderMessagePage('Update status', $code === 500 ? 'Unable to complete the change.' : $e->getMessage(), 'manage-user.php?user_id=' . $id, 'Back to account');
}
