<?php
// UM-08 Delete User Account (WF-07). GET shows the warning; POST performs the guarded deletion.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/pageHelpers.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';
$pdo = openDatabase();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $user = requireLogin($pdo);
    requireRole($user, ['Administrator']);
    $id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if (!$id || $id < 1) { http_response_code(400); exit('Invalid user ID'); }
    $confirmed = isset($_POST['confirm']) && $_POST['confirm'] === 'yes';
    try {
        deleteUserAccount($pdo, $user, $id, $confirmed);
        setFlash('Account deleted.');
        redirectTo('manage-users.php');
    } catch (Throwable $e) {
        $code = statusForException($e);
        if ($code === 500) { error_log($e->getMessage()); }
        http_response_code($code);
        renderMessagePage('Account not deleted', $code === 500 ? 'Unable to delete the account.' : $e->getMessage(), 'manage-user.php?user_id=' . $id, 'Back to account');
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit('Method not allowed'); }
$user = requireLogin($pdo);
requireRole($user, ['Administrator']);
$id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid user ID'); }
try { $target = describeDeletionImpact($pdo, $user, $id); }
catch (UserNotFoundException $e) { http_response_code(404); exit('User not found'); }
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main>
    <h1>Delete account</h1>
    <div class="account-card">
        <p>Delete the account for <strong><?= escapeHtml($target['full_name']) ?></strong> (<?= escapeHtml($target['email']) ?>)?</p>
        <p>Related tutoring requests: <strong><?= (int)$target['request_count'] ?></strong>.
<?php if ((int)$target['request_count'] > 0): ?>Deletion will be <strong>blocked</strong> because tutoring records must be retained. Deactivate the account instead.<?php else: ?>No tutoring records depend on this account. Deletion cannot be undone.<?php endif; ?></p>
        <form class="auth-form" method="post" action="delete-user.php">
            <?= csrfField() ?><input type="hidden" name="user_id" value="<?= (int)$target['user_id'] ?>">
            <label class="choice"><input type="checkbox" name="confirm" value="yes" required> <span>I understand and confirm deletion</span></label>
            <button type="submit">Confirm Delete</button>
        </form>
        <a class="btn btn-secondary" href="manage-user.php?user_id=<?= (int)$target['user_id'] ?>">Cancel</a>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
