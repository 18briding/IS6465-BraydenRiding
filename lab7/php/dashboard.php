<?php
// Landing page after login (UM-09 step 3).
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/requestService.php';
$user = requireLogin(openDatabase());
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main>
    <h1>Welcome, <?= escapeHtml($user['full_name']) ?></h1>
    <div class="account-card">
        <p>You are signed in as <strong><?= escapeHtml($user['role']) ?></strong>.</p>
        <ul>
            <li><a href="profile.php">View my profile</a></li>
<?php if ($user['role'] === 'Student'): ?>
            <li><a href="request-form.php">Request a tutoring session</a></li>
            <li><a href="requests.php">My tutoring requests</a></li>
<?php elseif ($user['role'] === 'Administrator'): ?>
            <li><a href="manage-users.php">Manage users</a></li>
            <li><a href="requests.php">All tutoring requests</a></li>
<?php else: ?>
            <li>Tutors have no request workflow in this version.</li>
<?php endif; ?>
        </ul>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
