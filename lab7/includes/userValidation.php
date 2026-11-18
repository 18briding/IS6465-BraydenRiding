<?php
// Validation helpers for user accounts. Each returns '' when valid, otherwise a message
// (same convention as includes/validation.php). Password rules were an OPEN decision in the
// design document; the values below are documented assumptions (docs/implementation-notes.md).

define('PASSWORD_MIN_BYTES', 8);
define('PASSWORD_MAX_BYTES', 72);   // bcrypt (PASSWORD_DEFAULT) ignores bytes beyond 72

function allRoles() { return ['Student', 'Tutor', 'Administrator']; }
function selfRegistrationRoles() { return ['Student', 'Tutor']; }   // approved scope: never Administrator
function allStatuses() { return ['Active', 'Inactive']; }

function normalizeEmail($email) { return strtolower(trim((string)$email)); }

function validateFullName($name) {
    $len = mb_strlen(trim((string)$name));
    if ($len < 1 || $len > 120) { return 'Full name is required (1 to 120 characters).'; }
    return '';
}
function validateEmailAddress($email) {
    $email = normalizeEmail($email);
    if ($email === '' || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Enter a valid email address.';
    }
    return '';
}
function validateNewPassword($password) {
    $password = (string)$password;
    if (strlen($password) < PASSWORD_MIN_BYTES || strlen($password) > PASSWORD_MAX_BYTES) {
        return 'Password must be ' . PASSWORD_MIN_BYTES . ' to ' . PASSWORD_MAX_BYTES . ' characters.';
    }
    return '';
}
