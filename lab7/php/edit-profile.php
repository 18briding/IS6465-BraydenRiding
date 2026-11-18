<?php
// UM-04 Update Own Account (WF-02). Name and email only; role and status are not form fields.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';
$pdo = openDatabase();
$user = requireLogin($pdo);
$errors = [];
$form = ['full_name' => $user['full_name'], 'email' => $user['email']];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    foreach ($form as $k => $v) { $form[$k] = isset($_POST[$k]) ? (string)$_POST[$k] : ''; }
    try {
        updateOwnAccount($pdo, $user, $_POST);
        setFlash('Your profile was updated.');
        redirectTo('profile.php');
    } catch (InvalidArgumentException $e) { http_response_code(422); $errors[] = $e->getMessage();
    } catch (AccessDeniedException $e) { http_response_code(403); $errors[] = $e->getMessage();
    } catch (Throwable $e) { error_log($e->getMessage()); http_response_code(500); $errors[] = 'Unable to update the profile.'; }
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main>
    <h1>Edit profile</h1>
<?php foreach ($errors as $err): ?><div class="alert alert-danger page-message" role="alert"><?= escapeHtml($err) ?></div><?php endforeach; ?>
    <form class="auth-form" method="post" action="edit-profile.php" novalidate>
        <?= csrfField() ?>
        <div class="form-field"><label for="full_name">Name</label><input id="full_name" name="full_name" required maxlength="120" value="<?= escapeHtml($form['full_name']) ?>"></div>
        <div class="form-field"><label for="email">Email</label><input id="email" name="email" type="email" required maxlength="254" value="<?= escapeHtml($form['email']) ?>"></div>
        <p class="text-muted">Role: <?= escapeHtml($user['role']) ?> (read only) &middot; Status: <?= escapeHtml($user['status']) ?> (read only)</p>
        <button type="submit">Save Changes</button>
    </form>
    <p class="text-center"><a href="profile.php">Back to profile</a></p>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
