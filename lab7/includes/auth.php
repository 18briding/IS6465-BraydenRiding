<?php
// Session, CSRF and access-guard helpers (UM-09, UM-10 and every protected page).
// Include this BEFORE any HTML output so headers and redirects still work.
require_once dirname(__DIR__) . '/repositories/userPDO.php';

function sessionTimeoutSeconds() {
    $env = getenv('PTH_SESSION_TIMEOUT');   // tests shorten this; default is 20 minutes
    return ($env !== false && ctype_digit($env) && (int)$env > 0) ? (int)$env : 1200;
}
function requestIsHttps() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');   // Codespaces proxy
}
function startSecureSession() {
    if (session_status() === PHP_SESSION_ACTIVE) { return; }
    $secure = requestIsHttps();
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
    } else {
        session_set_cookie_params(0, '/; samesite=Lax', '', $secure, true);   // PHP < 7.3 workaround
    }
    session_start();
}
function redirectTo($location, $code = 303) {
    header('Location: ' . $location, true, $code);
    exit;
}

// ---- CSRF (handout section 17) ----
function csrfToken() {
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf_token'];
}
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}
function verifyCsrfToken($post) {
    startSecureSession();
    $sent = isset($post['csrf_token']) ? (string)$post['csrf_token'] : '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(403);
        exit('Invalid or missing security token. Go back, reload the form and try again.');
    }
}
function requirePost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('POST required'); }
}

// ---- UM-09: establish the authenticated session ----
function establishSession($user) {
    startSecureSession();
    session_regenerate_id(true);   // defeats session fixation
    $_SESSION = [];                // drop anything an earlier anonymous session held
    $_SESSION['user_id'] = (int)$user['user_id'];
    $_SESSION['display_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    csrfToken();
}

// ---- UM-10: end the session (data, cookie and server-side record) ----
function logoutSession() {
    startSecureSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ---- Shared guard for every protected page (handout section 10) ----
// Returns the CURRENT user record from the database; role and status are never taken from the browser.
function requireLogin($pdo) {
    startSecureSession();
    if (empty($_SESSION['user_id'])) { redirectTo('login.php', 302); }
    $last = isset($_SESSION['last_activity']) ? (int)$_SESSION['last_activity'] : 0;
    if (time() - $last > sessionTimeoutSeconds()) {
        logoutSession();
        redirectTo('login.php?expired=1', 302);
    }
    $user = findUserById($pdo, (int)$_SESSION['user_id']);
    if (!$user || $user['status'] !== 'Active') {
        logoutSession();
        redirectTo('login.php?inactive=1', 302);
    }
    $_SESSION['last_activity'] = time();
    $_SESSION['display_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    return $user;
}
function requireRole($user, $allowedRoles) {
    if (!in_array($user['role'], $allowedRoles, true)) {
        http_response_code(403);
        exit('You are not authorized to perform this action.');
    }
}

// ---- One-time messages shown after a redirect ----
function setFlash($message) { startSecureSession(); $_SESSION['flash'] = (string)$message; }
function takeFlash() {
    startSecureSession();
    $m = isset($_SESSION['flash']) ? $_SESSION['flash'] : '';
    unset($_SESSION['flash']);
    return $m;
}
// Maps a service exception to an HTTP status code.
function statusForException($e) {
    if ($e instanceof InvalidArgumentException) { return 422; }
    if ($e instanceof AccessDeniedException) { return 403; }
    if ($e instanceof UserNotFoundException) { return 404; }
    if ($e instanceof BlockedOperationException) { return 409; }
    return 500;
}
