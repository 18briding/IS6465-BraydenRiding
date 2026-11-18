<?php
// UM-02 View Own Account (WF-02). Uses only the session user id; there is no user_id parameter to tamper with.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/requestService.php';
$pdo = openDatabase();
$user = requireLogin($pdo);
$me = getOwnAccount($pdo, $user);
$flash = takeFlash();
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main>
    <h1>My profile</h1>
<?php if ($flash !== ''): ?><div class="alert alert-success page-message" role="status"><?= escapeHtml($flash) ?></div><?php endif; ?>
    <div class="account-card">
        <dl>
            <dt>Name</dt><dd><?= escapeHtml($me['full_name']) ?></dd>
            <dt>Email</dt><dd><?= escapeHtml($me['email']) ?></dd>
            <dt>Role (read only)</dt><dd><?= escapeHtml($me['role']) ?></dd>
            <dt>Status (read only)</dt><dd><?= escapeHtml($me['status']) ?></dd>
        </dl>
        <a class="btn btn-primary" href="edit-profile.php">Edit Profile</a>
        <a class="btn btn-outline-secondary" href="change-password.php">Change Password</a>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
