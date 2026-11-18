<?php
// UM-10 Log Out. POST + CSRF token because logout changes session state.
require_once __DIR__ . '/../includes/auth.php';
requirePost();
startSecureSession();
if (empty($_SESSION['user_id'])) {
    redirectTo('login.php?expired=1');   // already ended: nothing to invalidate, send to login
}
verifyCsrfToken($_POST);
logoutSession();
redirectTo('login.php?logged_out=1');
