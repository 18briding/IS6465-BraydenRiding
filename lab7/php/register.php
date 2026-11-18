<?php
// UM-01 Create User Account (WF-01). Public page; role choices are Student or Tutor only.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';   // escapeHtml()
startSecureSession();
$errors = [];
$created = false;
$old = ['full_name' => '', 'email' => '', 'role' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    foreach ($old as $k => $v) { $old[$k] = isset($_POST[$k]) ? (string)$_POST[$k] : ''; }
    try {
        registerUser(openDatabase(), $_POST);
        $created = true;
    } catch (InvalidArgumentException $e) { http_response_code(422); $errors[] = $e->getMessage();
    } catch (AccessDeniedException $e) { http_response_code(403); $errors[] = $e->getMessage();
    } catch (Throwable $e) { error_log($e->getMessage()); http_response_code(500); $errors[] = 'Unable to create the account.'; }
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main>
    <h1>Create an account</h1>
<?php if ($created): ?>
    <div class="account-card">
        <p class="fw-bold">Your account was created and is active.</p>
        <a class="btn btn-primary" href="login.php">Log in</a>
    </div>
<?php else: ?>
<?php foreach ($errors as $err): ?><div class="alert alert-danger page-message" role="alert"><?= escapeHtml($err) ?></div><?php endforeach; ?>
    <form class="auth-form" method="post" action="register.php" novalidate>
        <?= csrfField() ?>
        <div class="form-field"><label for="full_name">Full name</label><input id="full_name" name="full_name" required maxlength="120" value="<?= escapeHtml($old['full_name']) ?>"></div>
        <div class="form-field"><label for="email">Email</label><input id="email" name="email" type="email" required maxlength="254" value="<?= escapeHtml($old['email']) ?>"></div>
        <div class="form-field"><label for="password">Password (8 to 72 characters)</label><input id="password" name="password" type="password" required autocomplete="new-password"></div>
        <div class="form-field"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" required autocomplete="new-password"></div>
        <fieldset>
            <legend>Account type</legend>
            <label class="choice"><input type="radio" name="role" value="Student" <?= $old['role'] === 'Student' ? 'checked' : '' ?> required> <span>Student</span></label>
            <label class="choice"><input type="radio" name="role" value="Tutor" <?= $old['role'] === 'Tutor' ? 'checked' : '' ?>> <span>Tutor</span></label>
        </fieldset>
        <button type="submit">Create Account</button>
    </form>
<?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
