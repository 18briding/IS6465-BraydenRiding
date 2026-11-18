<?php
// Pages set $user (from requireLogin) before including this file. Hiding links is only a convenience;
// every destination page still enforces access on the server.
$navUser = isset($user) && is_array($user) ? $user : null;
$navRole = $navUser ? $navUser['role'] : '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="<?= $navUser ? 'dashboard.php' : 'login.php' ?>">Peer Tutoring Hub</a>
    <div class="navbar-nav flex-row flex-wrap align-items-center gap-2">
<?php if ($navUser): ?>
      <a class="nav-link" href="profile.php">My Profile</a>
<?php   if ($navRole === 'Student' || $navRole === 'Administrator'): ?>
      <a class="nav-link" href="requests.php">Requests</a>
<?php   endif; if ($navRole === 'Student'): ?>
      <a class="nav-link" href="request-form.php">Request a Session</a>
<?php   endif; if ($navRole === 'Administrator'): ?>
      <a class="nav-link" href="manage-users.php">Manage Users</a>
<?php   endif; ?>
      <form class="inline-form" method="post" action="logout.php"><?= csrfField() ?><button class="btn btn-sm btn-light" type="submit">Log out</button></form>
<?php else: ?>
      <a class="nav-link" href="login.php">Log in</a>
      <a class="nav-link" href="register.php">Register</a>
<?php endif; ?>
    </div>
  </div>
</nav>
