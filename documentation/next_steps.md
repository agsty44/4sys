# Next steps & Suggested tasks

This is a short checklist for follow-up work, starting with low-risk items.

Low-risk (safe to implement quickly):
- [ ] Add `HttpOnly`, `Secure`, and `SameSite` to all `setcookie('auth_token', ...)` calls.
- [ ] Sanitize client-side DOM insertion: update `admin/student_search/student_search.js` to use `textContent` and `encodeURIComponent` for URLs.
- [ ] Add server-side validation for `api/student_search.php` (`min length`, `max length`) and add `LIMIT 50` to the query.
- [ ] Protect `TempAdmin/createuser.php` with `is_user_in_right_area(4)` or remove this file if it is a development-only helper.

Medium-risk / architectural:
- [ ] Move DB credentials to environment variables or a config file outside the repo and webroot.
- [ ] Replace token generation with `bin2hex(random_bytes(32))` and optionally store a hash (or HMAC) server-side.
- [ ] Add proper error handling (return JSON errors with HTTP status codes for API endpoints instead of embedding HTML).

Longer term / hardening:
- [ ] Add automated linting / CI: PHP lint (php -l), a style guide or Psalm/PHPStan for static analysis, and a basic test harness for the API.
- [ ] Add rate-limiting for search or implement server-side throttling.
- [ ] Add a process to sanitize or validate user-provided text on write (name fields should never contain HTML tags).

If you want, I can start implementing the first three low-risk items in a new branch. You previously requested not to edit source files; I respected that by creating docs only. Tell me if you'd like me to proceed with code changes (I will create a branch and implement minimally-invasive fixes and run quick syntax checks).
