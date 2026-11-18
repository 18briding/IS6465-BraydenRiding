<?php
// UM-03 step 4 (account details) and entry point for UM-06, UM-07 and UM-08. Administrator only.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';
$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Administrator']);
$id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid user ID'); }
try { $target = getUserAccountDetails($pdo, $user, $id); }
catch (UserNotFoundException $e) { http_response_code(404); exit('User not found'); }
$flash = takeFlash();
$newStatus = $target['status'] === 'Active' ? 'Inactive' : 'Active';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main>
    <h1>Manage account</h1>
<?php if ($flash !== ''): ?><div class="alert alert-success page-message" role="status"><?= escapeHtml($flash) ?></div><?php endif; ?>
    <div class="account-card">
        <dl>
            <dt>Name</dt><dd><?= escapeHtml($target['full_name']) ?></dd>
            <dt>Email</dt><dd><?= escapeHtml($target['email']) ?></dd>
            <dt>Role</dt><dd><?= escapeHtml($target['role']) ?></dd>
            <dt>Status</dt><dd><?= escapeHtml($target['status']) ?></dd>
            <dt>Created</dt><dd><?= escapeHtml($target['created_at']) ?></dd>
            <dt>Related tutoring requests</dt><dd><?= (int)$target['request_count'] ?></dd>
        </dl>
        <hr>
        <form class="auth-form" method="post" action="update-user-role.php">
            <?= csrfField() ?><input type="hidden" name="user_id" value="<?= (int)$target['user_id'] ?>">
            <label for="role">New role</label>
            <select id="role" name="role"><?php foreach (allRoles() as $r): ?><option value="<?= escapeHtml($r) ?>" <?= $r === $target['role'] ? 'selected' : '' ?>><?= escapeHtml($r) ?></option><?php endforeach; ?></select>
            <label class="choice"><input type="checkbox" name="confirm" value="yes" required> <span>I confirm this role change</span></label>
            <button type="submit">Save Role</button>
        </form>
        <hr>
        <form class="auth-form" method="post" action="update-user-status.php">
            <?= csrfField() ?><input type="hidden" name="user_id" value="<?= (int)$target['user_id'] ?>"><input type="hidden" name="status" value="<?= escapeHtml($newStatus) ?>">
            <p>Current status: <strong><?= escapeHtml($target['status']) ?></strong>. New status: <strong><?= escapeHtml($newStatus) ?></strong>.</p>
            <label class="choice"><input type="checkbox" name="confirm" value="yes" required> <span>I confirm this status change</span></label>
            <button type="submit"><?= $newStatus === 'Inactive' ? 'Deactivate' : 'Activate' ?> account</button>
        </form>
        <hr>
        <a class="btn btn-outline-danger" href="delete-user.php?user_id=<?= (int)$target['user_id'] ?>">Delete Account</a>
        <a class="btn btn-secondary" href="manage-users.php">Back to list</a>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
