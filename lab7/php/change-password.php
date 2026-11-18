<?php
// UM-05 Change Password (WF-04). Password fields are never echoed back or stored.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';
$pdo = openDatabase();
$user = requireLogin($pdo);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    try {
        changePassword($pdo, $user, $_POST);
        setFlash('Your password was updated.');
        redirectTo('profile.php');
    } catch (InvalidArgumentException $e) { http_response_code(422); $errors[] = $e->getMessage();
    } catch (AccessDeniedException $e) { http_response_code(403); $errors[] = $e->getMessage();
    } catch (Throwable $e) { error_log($e->getMessage()); http_response_code(500); $errors[] = 'Unable to change the password.'; }
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main>
    <h1>Change password</h1>
<?php foreach ($errors as $err): ?><div class="alert alert-danger page-message" role="alert"><?= escapeHtml($err) ?></div><?php endforeach; ?>
    <form class="auth-form" method="post" action="change-password.php" novalidate>
        <?= csrfField() ?>
        <div class="form-field"><label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" required autocomplete="current-password"></div>
        <div class="form-field"><label for="new_password">New password (8 to 72 characters)</label><input id="new_password" name="new_password" type="password" required autocomplete="new-password"></div>
        <div class="form-field"><label for="confirm_password">Confirm new password</label><input id="confirm_password" name="confirm_password" type="password" required autocomplete="new-password"></div>
        <button type="submit">Update Password</button>
    </form>
    <p class="text-center"><a href="profile.php">Back to profile</a></p>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
