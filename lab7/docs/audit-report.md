# Traceability Audit Report: User Management (UM-01 to UM-10)

## 1. Scope and inspected materials
Design document v1.0 (UM-01..UM-10, WF-01..WF-08, business rules, acceptance tests); IS 6465 authentication handout; Lab 6 baseline code; the code in this package; and the output of `php tests/run-all.php` run on PHP 8.3.6 (`docs/test-results.txt`). The approved Week 6 two-table schema was inspected for drift. Not supplied: an instructor-approved decision on deletion policy or password rules; an ERD revision approving the `users` table.

## 2. Overall assessment
All ten use cases are implemented and each is covered by executed, passing tests (218 checks, 0 failed). Authorization is enforced by the server on every protected page and re-checked inside services from the database, not from the session or form. Three items depend on decisions the design leaves open or on environments I could not run (see findings). This is a classroom application, not production-secure.

## 3. Forward traceability
See `docs/traceability-matrix.md`. Summary: UM-01..UM-10 = Verified (UM-08 against an assumed policy; the `Secure` cookie attribute is Implemented, unverified).

## 4. Backward traceability findings
- Request-page guards/ownership and the CLI admin script are not in the UM design; they come from the handout and the "no public Administrator" rule. (Informational: flagged in the matrix.)
- No unexplained features found. No code path writes a role other than via UM-06 or the CLI bootstrap.

## 5. Architecture and database findings
- **Layering respected.** SQL only in `repositories/`; transactions only in services; pages contain HTML + calls; `config/db.php` only connects. `includes/auth.php` handles sessions/CSRF (not SQL except via `findUserById`).
- **Schema (Major, needs approval).** New table `users` and new nullable column `students.user_id` (UNIQUE). The application-builder skill forbids adding tables without explicit approval; the design says "reconcile USER with existing tables" and the handout specifies this exact model, so it is implemented, but the ERD should be updated and approved. `tutoring_requests` is unchanged; the design's alternative (`user_id` on `tutoring_requests`) was **not** used.
- Integrity verified: unique email, FK `students.user_id → users`, default Open request status, foreign keys enabled on all PDO connections, prepared statements for all submitted values.

## 6. Security limitations
Known and accepted for the classroom: no rate limiting/lockout, password reset, MFA, audit log, or HTTPS enforcement; `Secure` cookies only when HTTPS is detected. CSRF protection, session regeneration, timeout, hashing, and generic login errors **are** implemented and tested. Registration reveals duplicate emails (required by the design).

## 7. Test evidence assessment
- Expected results come from the design's acceptance tests and rules, not from reading the code.
- The harness was validated by injecting defects and confirming the tests failed, then reverting each: missing admin guard, no session regeneration, missing request-ownership filter, allowing Administrator registration, removing last-admin protection, allowing inactive login, and a no-op logout were all detected. Removing only `session_destroy()` was *not* detected because the session data was already cleared (behaviourally equivalent), so it is not a defect. One weak assertion (UM-02-H03) was found by review and strengthened.
- The 3 Lab 6 baseline failures were fixture errors (seed row counted), fixed in the fixture without weakening assertions.
- Gaps: PHP 7.x untested; real HTTPS `Secure` cookie untested; no browser-level (JavaScript/CSS) tests; no concurrency tests; rendering not asserted beyond key text.

## 8. Findings and corrective actions

| # | Severity | Finding | Business impact | Action |
|---|---|---|---|---|
| 1 | Major | Deletion policy and retention of related tutoring records are **open** in the design; implemented "delete only if no requests" | A different approved policy (e.g., anonymise or cascade) would change UM-08 | Obtain approval; update `deleteUserAccount()` and UM-08 tests |
| 2 | Major | `users` table and `students.user_id` added without a revised approved ERD | Schema drift from Week 6 baseline | Update ERD / get approval |
| 3 | Minor | Password rules are assumed (8 to 72 characters) | Policy mismatch | Confirm requirement |
| 4 | Minor | Guards on existing tutoring pages exceed the UM scope | Changes Lab 6 behaviour: anonymous users can no longer submit requests | Confirm scope |
| 5 | Minor | PHP 7.x unverified | May fail on an older runtime | Run `php -v` and `php tests/run-all.php` on the target runtime |
| 6 | Minor | No lockout/rate limit on login | Password guessing is not slowed | Future requirement |
| 7 | Informational | Last-admin rule uses a deferred SQLite transaction | Theoretical race under concurrent admin actions | Use `BEGIN IMMEDIATE` if concurrency matters |
| 8 | Informational | Lab 6 demo student (Maria) has no login | Cosmetic | She registers with the same email to adopt her record |
