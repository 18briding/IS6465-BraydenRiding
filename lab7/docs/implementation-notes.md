# Implementation notes: UM-01 to UM-10

## 1. Inputs used
- Design: `Peer_Tutoring_Hub_User_Management_Design.docx` (v1.0 draft): use cases, wireframes WF-01..WF-08, business rules, acceptance tests.
- Context: IS 6465 Authentication handout (users table, `students.user_id` link, session/CSRF/guard patterns, access matrix).
- Current app: `lab6.zip` (kept: Lab 4 form controls, JSON `topic` convention, layer structure, existing tests).
- Skill packs: application-builder, test-harness-generator, traceability-auditor.

## 2. Decisions that needed an assumption (please confirm with your instructor)
The design lists these as **open**; I implemented the conservative option so nothing is silently destroyed.

| Open item | Implemented assumption | Where |
|---|---|---|
| Permanent deletion policy / retention of tutoring requests | Delete only if the account has **zero** related tutoring requests. Otherwise block and advise deactivation. Never delete dependent records. Self-delete and last-active-administrator delete are blocked. | `deleteUserAccount()` |
| Exact password requirements | 8 to 72 bytes (72 = bcrypt limit), no composition rules. | `includes/userValidation.php` |
| USER ↔ STUDENTS ↔ tutoring_requests reconciliation | Handout model: new `users` table; `students.user_id` (nullable, UNIQUE) links a Student account to its students row. `tutoring_requests` is **unchanged**. Tutors/Admins have no students row. | `database/setup-database.sql` |
| Who may reach tutoring pages | Handout access matrix: Student = own requests; Administrator = all requests, may edit/cancel any Open one; Tutor = none. | `php/request-*.php`, `requests.php` |
| Role-change rules ("prohibited change") | Same-role no-op and demoting/deactivating/deleting the **last active** administrator are prohibited (design "proposed" rule). Admins may promote to Administrator. | `changeUserRole()`, `setUserStatus()` |
| First administrator | Created only by CLI (`scripts/create-admin.php`) or `--demo`. No web path creates an Administrator. | `scripts/` |
| Confirmation (UM-06/07/08) | Server-enforced `confirm=yes` field (checkbox), not just a JavaScript prompt. | `update-user-*.php`, `delete-user.php` |

## 3. How each use case works (Browser → page → service → repository → SQLite)
- **UM-01** `register.php` → `registerUser()` validates name, email, password match/length, role ∈ {Student, Tutor}; an `Administrator` value is denied (403) before any write. Duplicate email is rejected. In one transaction: `insertUser()` (hash via `password_hash`, status Active) and, for Students, `ensureStudentRecord()`.
- **UM-02** `profile.php` → `getOwnAccount()` takes no target id, so only the signed-in user's record can be shown. Hash is never selected by public lookups.
- **UM-03** `manage-users.php` → `searchUserAccounts()` (Administrator re-verified from the database each call); search is a prepared `LIKE` with wildcards escaped; role/status filters are whitelisted.
- **UM-04** `edit-profile.php` → `updateOwnAccount()` changes name/email only; a posted `role`/`status` that differs is denied; the linked students row is updated in the same transaction.
- **UM-05** `change-password.php` → `changePassword()` verifies the current password with `password_verify`, validates the new one, stores a new hash.
- **UM-06 / UM-07** admin page `manage-user.php` posts to `update-user-role.php` / `update-user-status.php` → `changeUserRole()` / `setUserStatus()`.
- **UM-08** `delete-user.php` (GET shows the warning with the related-request count; POST rechecks inside a transaction) → `deleteUserAccount()`.
- **UM-09** `login.php` → `authenticateUser()` (one generic message for unknown/wrong/inactive) → `establishSession()` (`session_regenerate_id(true)`, minimal session data) → `dashboard.php`. `requireLogin()` runs on every protected page: reloads the user from SQLite (so deactivation/role change takes effect immediately) and applies a 20-minute inactivity timeout.
- **UM-10** `logout.php` accepts POST + CSRF token only; clears `$_SESSION`, expires the cookie, `session_destroy()`, redirects to login. An already-expired session is redirected to login without error.

Cross-cutting: CSRF token on every state-changing form (`hash_equals`); session cookie `HttpOnly`, `SameSite=Lax`, `Secure` when HTTPS (or `X-Forwarded-Proto: https`, as in Codespaces) is detected; all output through `escapeHtml()`; all SQL parameterised; foreign keys enforced on every connection.

## 4. Changes to existing Lab 6 files (kept backward compatible)
- `repositories/requestPDO.php`, `services/requestService.php`: optional trailing `$userId` parameter on `findRequest`, `listRequests`, `updateOpenRequest`, `cancelOpenRequest`, `reviseRequest` (ownership enforced **in SQL**). Calls without it behave as before.
- `php/request-form.php`, `process-request.php`, `requests.php`, `request-details.php`, `edit-request.php`, `update-request.php`, `delete-request.php`: login + role guard + CSRF; the form shows the account's name/email read-only and the server overwrites those fields from the account (forged values are ignored). Lab 4 controls (course, topic checkboxes, date, time radios, reason textarea) are unchanged.
- `includes/nav.php`, `header.php`, `css/styles.css`: role-aware navigation; extra CSS scoped to new classes.
- `config/db.php`: optional `PTH_DB_PATH` environment override (tests only; default path unchanged).
- `database/setup-database.sql`: adds `users` and `students.user_id`; original seed rows kept.
- `index.php` now redirects to the login page.
- `tests/integration-tests.php`: the 14 Lab 6 checks are unchanged. **The Lab 6 baseline had 3 failing checks** (duplicate-email count, UC02 filter count, "invalid submission writes nothing") because the schema file's sample seed row made every count off by one. The fixture now loads the schema without seed rows (`tests/test-helpers.php`). No assertion was weakened.

## 5. Not changed / deliberate non-goals
Course/topic/time lists, the JSON `topic` convention, `tutoring_requests` columns, preferred-time values.

## 6. Limitations
- Verified only on PHP 8.3.6 (`php -l` clean on all files). Code avoids PHP 8-only syntax, but PHP 7.x was **not** available to test (and the Lab 6 code already used 7.1 array destructuring).
- No login throttling/lockout, password reset, e-mail verification, audit log, or HTTPS enforcement.
- Registration reveals when an email is already taken (the design requires a duplicate message); login does not reveal account existence.
- Last-administrator checks run inside a SQLite transaction; under heavy concurrent writes a stricter `BEGIN IMMEDIATE` would be needed.
- The existing Lab 6 sample student (Maria Lopez) has no login until she registers with the same email.
- Bootstrap CSS/JS load from a CDN, so pages are unstyled offline.
