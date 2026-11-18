<?php
// UM-09 Log In (WF-08).
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';   // escapeHtml()
startSecureSession();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    try {
        $found = authenticateUser(openDatabase(), isset($_POST['email']) ? $_POST['email'] : '', isset($_POST['password']) ? $_POST['password'] : '');
    } catch (Throwable $e) { error_log($e->getMessage()); http_response_code(500); exit('Unable to log in right now.'); }
    if ($found) {
        establishSession($found);
        redirectTo('dashboard.php');
    }
    http_response_code(401);
    $error = 'Invalid email or password.';   // same text for unknown, wrong password and inactive
}
$notice = '';
if (isset($_GET['expired'])) { $notice = 'Your session expired. Please log in again.'; }
elseif (isset($_GET['inactive'])) { $notice = 'Your session ended. Please log in again.'; }
elseif (isset($_GET['logged_out'])) { $notice = 'You have been logged out.'; }
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main>
    <h1>Log in</h1>
<?php if ($notice !== ''): ?><div class="alert alert-info page-message" role="status"><?= escapeHtml($notice) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert alert-danger page-message" role="alert"><?= escapeHtml($error) ?></div><?php endif; ?>
    <form class="auth-form" method="post" action="login.php">
        <?= csrfField() ?>
        <div class="form-field"><label for="email">Email</label><input id="email" name="email" type="email" required autocomplete="username"></div>
        <div class="form-field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div>
        <button type="submit">Log In</button>
    </form>
    <p class="text-center">No account yet? <a href="register.php">Register</a></p>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
