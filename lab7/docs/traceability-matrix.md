# Requirements Traceability Matrix: UM-01 to UM-10

Evidence basis: `docs/test-results.txt`, a real run of `php tests/run-all.php` on PHP 8.3.6 (218 checks, 0 failed). Status labels follow the traceability-auditor skill. **Verified** means executed, passing tests cover the behaviour. It does not mean the design decision itself is approved.
Test-ID prefixes: `T` = service tests (`tests/user-management-tests.php`), `H` = HTTP tests (`tests/http-tests.php`), `AUTH` = tutoring-page guards, `MIG` = migration.

## Forward traceability

| Req. ID | Requirement (design doc) | Implementation evidence | Test evidence (executed) | Status | Gap / action |
|---|---|---|---|---|---|
| UM-01 | Create active account; validate fields, unique email, password match, allowed role; store hash; deny Administrator | `php/register.php` (WF-01 form: Student/Tutor radios only) → `registerUser()` in `services/userService.php` → `insertUser()` / `ensureStudentRecord()`; `users.email UNIQUE`, role CHECK | UM-01-T01..T20 (27 passing checks incl. H01..H07): active status, hash only, duplicate (case-insensitive), Administrator denied with no write, mismatch, short password, bad email, blank name, special characters, rollback, legacy-student adoption, forged role over HTTP, output encoding, CSRF | **Verified** | Password rules are an assumption (8..72); confirm. |
| UM-02 | Authenticated user views own name, email, role, status; only own profile; expired session denied | `php/profile.php` → `getOwnAccount()` (no target id); `requireLogin()` | UM-02-T01..T06, H01..H04 (10) | **Verified** | None |
| UM-03 | Administrator searches/filters accounts and opens details; unauthorized or no match handled | `php/manage-users.php`, `manage-user.php` → `searchUserAccounts()`, `getUserAccountDetails()` → `searchUsers()` (prepared LIKE, wildcards escaped) | UM-03-T01..T14, H01..H09 (24): name/email search, role, status, combined, empty, `%`, SQL-injection text, Student/Tutor/anonymous denied, no hash in results | **Verified** | None |
| UM-04 | Edit own name/email; validate ownership and fields; role/status cannot change; invalid or duplicate email rejected | `php/edit-profile.php` → `updateOwnAccount()`; students row synced in same transaction | UM-04-T01..T11, H01..H04 (15): update, normalisation, sync, role/status tamper denied, duplicate, invalid, CSRF | **Verified** | None |
| UM-05 | Verify current password, validate new one, store new hash; old password stops working | `php/change-password.php` → `changePassword()` → `updateUserPasswordHash()` | UM-05-T01..T08, H01..H04 (12): new works, old fails, wrong current, mismatch, short, other user unaffected | **Verified** | Password rules assumed. |
| UM-06 | Administrator changes role; unauthorized/prohibited change denied | `manage-user.php` → `update-user-role.php` → `changeUserRole()` | UM-06-T01..T14, H01..H05 (19): admin changes, Student/Tutor denied (incl. self-promotion), unconfirmed, invalid role, no-op, last admin, inactive actor, CSRF | **Verified** | "Prohibited change" rules are the design's *proposed* rules; confirm. |
| UM-07 | Administrator activates/deactivates with confirmation; inactive user cannot log in | `update-user-status.php` → `setUserStatus()`; `requireLogin()` re-reads status | UM-07-T01..T10, H01..H06 (16): login blocked, reactivation, invalid transition, last admin, open session ended on next request | **Verified** | None |
| UM-08 | Delete under approved retention policy; cancelled or blocked by related requests; do not silently remove dependents | `php/delete-user.php` → `describeDeletionImpact()`, `deleteUserAccount()` | UM-08-T01..T13, H01..H07 (20): blocked when requests exist (nothing removed), unconfirmed, self, last admin, non-admin, success without records, FK safety net | **Verified against an assumed policy** | **Policy is open in the design.** Approve or change the "no related requests" rule. |
| UM-09 | Verify hash and status; regenerate session ID; establish session; generic failure | `php/login.php` → `authenticateUser()` → `establishSession()` (`session_regenerate_id(true)`); `requireLogin()` guard, 20-min timeout | UM-09-T01..T09, H01..H13 (22): valid/invalid/unknown/inactive, SQL injection, rehash, cookie flags, ID regeneration, pre-login ID useless, timeout | **Verified** | `Secure` cookie flag only exercised when HTTPS is present (not testable on plain HTTP): **Implemented, unverified** for that one attribute. |
| UM-10 | Invalidate session and cookie; redirect to login; already-expired handled; protected access denied after logout | `php/logout.php` → `logoutSession()` | UM-10-H01..H07 (7): POST-only, CSRF, redirect, cookie expired, protected page denied, old cookie replay denied, expired-session path | **Verified** | None |

## Cross-cutting rules

| Rule (source) | Implementation | Evidence | Status |
|---|---|---|---|
| Passwords stored only as hashes (design, handout) | `password_hash(PASSWORD_DEFAULT)`; public lookups omit `password_hash` | UM-01-T02, UM-02-T02, UM-03-T12, UM-05-T04, UM-09-T02 | Verified |
| Public registration cannot grant Administrator (approved) | role whitelist in `registerUser()`; no web path to create admins | UM-01-T09/T10, H01, H04 | Verified |
| Only administrators change others' role/status (proposed) | `requireAdministratorActor()` re-reads actor from DB | UM-06-T03..T06, UM-07-T05, H-tests | Verified |
| Prevent removal of last active administrator (proposed) | checks in role/status/delete services | UM-06-T10, UM-07-T09, UM-08 | Verified |
| Server-side validation, output encoding, prepared SQL, POST for writes | all pages/repos | UM-01-H06, UM-03-T08/T09, UM-09-T07, UM-10-H01 | Verified (sampled); code review in audit |
| CSRF on state-changing requests (handout) | `csrfField()`, `verifyCsrfToken()` | H-tests on register, login, profile, password, logout, role, status, delete, request cancel | Verified |
| Student sees own requests only; Tutor none; Admin all (handout matrix) | ownership in SQL (`findRequest`, `listRequests`, `updateOpenRequest`, `cancelOpenRequest`) | AUTH-H01..H12 | Verified |
| UC01/UC02 behaviour preserved | optional params only | `integration-tests.php` (14), `validation-tests.php` (10) | Verified |
| Upgrade of an existing Lab 6 database | `scripts/init-db.php` + `database/migrate-add-users.sql` | MIG-01..05 | Verified |
| Runs on PHP 7.x | syntax kept 7-compatible by review | none | **Unknown** (only PHP 8.3.6 available) |

## Backward traceability (code with no design requirement)

| Item | Justification | Note |
|---|---|---|
| `php/dashboard.php` | UM-09 step 3 "Views dashboard" | Minimal landing page |
| CSRF, 20-min timeout, session regeneration, cookie flags | Design "Security: ... session protections"; handout sections 4, 8, 10, 17 | Required |
| Authentication guards on existing request pages, ownership SQL | Handout sections 13-15 (Lab 7); needed so UM-08 "related tutoring requests" is meaningful | **Scope extension beyond UM-01..10**; keep or remove with instructor approval |
| `scripts/create-admin.php`, `--demo` seeding | Needed because registration may never create Administrators (approved rule) | CLI only; not a design use case |
| Router blocks `tests/ scripts/ config/ services/ repositories/ docs/` | Prevents web execution of tests/scripts | Hardening only |
| `PTH_DB_PATH`, `PTH_SESSION_TIMEOUT` environment overrides | Allow isolated, fast tests | Default behaviour unchanged |
| Password rehash on login | Handout section 6 (`password_needs_rehash`) | Verified UM-09-T09 |
