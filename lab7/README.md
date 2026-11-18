# Peer Tutoring Hub: Tutoring Requests + User Management (UM-01 to UM-10)

IS 6465 classroom application. PHP + PDO + SQLite + Bootstrap. This package continues the Week 7 / Lab 6 tutoring app (UC01 request tutoring, UC02 view/filter requests) and adds the ten user-management use cases from the design document (`docs/Peer_Tutoring_Hub_User_Management_Design.docx`).

| ID | Use case | Page(s) |
|---|---|---|
| UM-01 | Create User Account | `php/register.php` |
| UM-02 | View Own Account | `php/profile.php` |
| UM-03 | View and Search User Accounts | `php/manage-users.php`, `php/manage-user.php` |
| UM-04 | Update Own Account | `php/edit-profile.php` |
| UM-05 | Change Password | `php/change-password.php` |
| UM-06 | Update User Role | `php/manage-user.php` → `php/update-user-role.php` |
| UM-07 | Activate or Deactivate Account | `php/manage-user.php` → `php/update-user-status.php` |
| UM-08 | Delete User Account | `php/delete-user.php` |
| UM-09 | Log In | `php/login.php`, `php/dashboard.php` |
| UM-10 | Log Out | `php/logout.php` (POST) |

## Quick start (GitHub Codespaces or any machine with PHP, pdo_sqlite, mbstring, curl)

```bash
php scripts/init-db.php --demo        # creates database/tutoring.db (or upgrades a Lab 6 one) + fictional demo accounts
php -S 0.0.0.0:8000 php/router.php    # open forwarded port 8000
php tests/run-all.php                 # in a second terminal: runs every test suite
```

Demo accounts (fictional; created only with `--demo`; change or delete before any real use):

| Role | Email | Password |
|---|---|---|
| Student | student@example.edu | StudentPass1 |
| Tutor | tutor@example.edu | TutorPass123 |
| Administrator | admin@example.edu | AdminPass123 |

Real administrators are created from the command line only (public registration can never create one):
`php scripts/create-admin.php "Full Name" admin@school.edu 'a-strong-password'`

Upgrading an existing Lab 6 `tutoring.db`: run `php scripts/init-db.php` (idempotent). Existing students and requests are kept. A returning student who registers with the same email adopts their old student row and request history.

## Layout

```
config/db.php                 PDO connection (foreign keys ON); PTH_DB_PATH override for tests
database/setup-database.sql   schema: users (new), students (+ user_id), tutoring_requests (unchanged)
database/migrate-add-users.sql  upgrade for a Lab 6 database (run via scripts/init-db.php)
repositories/                 SQL only: userPDO.php (new), studentPDO.php, requestPDO.php
services/                     rules + transactions: userService.php (new), requestService.php
includes/                     auth.php (session, CSRF, guards), userValidation.php, pageHelpers.php, templates
php/                          pages (HTML + Bootstrap only)
scripts/                      init-db.php, create-admin.php (CLI only)
tests/                        run-all.php, user-management-tests.php, http-tests.php, migration-tests.php,
                              integration-tests.php (UC01/UC02 regression), validation-tests.php (Lab 4), test-helpers.php
docs/                         design doc, implementation-notes.md, traceability-matrix.md, audit-report.md, test-results.txt
```

Flow: Browser → PHP page (`php/`) → service (`services/`) → repository (`repositories/`) → SQLite. Pages never contain SQL; repositories never contain business rules; transactions live in services.

## Read this next

- `docs/implementation-notes.md`: assumptions for the design's open decisions (deletion policy, password rules), how each use case works, limitations.
- `docs/traceability-matrix.md` and `docs/audit-report.md`: requirement → code → test evidence, with honest status labels.
- `docs/test-results.txt`: output of a real run (PHP 8.3.6). Re-run `php tests/run-all.php` yourself; passing tests are evidence only when they are actually executed.

## Classroom limits

Not production-ready: no HTTPS enforcement (cookie `Secure` flag is set only when HTTPS is detected), no login rate limiting or lockout, no password reset, no audit log. Use only fictional data. Do not commit `database/*.db` (already in `.gitignore`).
