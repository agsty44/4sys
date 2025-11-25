# Architecture & Authentication Flow

This is a condensed description of how the app routes and authenticates users based on the code read.

Auth primitives (in `header.php`):
- DB connection created with mysqli using hard-coded credentials.
- `grab_login_from_cookie()` — reads `$_COOKIE['auth_token']`, looks up `People.LoginToken` and returns `AccessTier`. If invalid or missing, includes the login form and exits.
- `grab_user_id()` — returns `PersonID` for the current `auth_token`.
- `is_user_in_right_area($intended_access_level)` — checks `grab_login_from_cookie()` and forwards to the correct dashboard if user's `AccessTier` differs.
- `send_to_dashboard($level)` — header redirect to the relevant dashboard (students, admin, teachers, parents, bus, etc.).

Login flow (`index.php`):
- If `auth_token` cookie exists: call `grab_login_from_cookie()` and then `send_to_dashboard()`.
- Else if POST `username` and `pass` exist: call `grab_login_from_username_pass()` which verifies the password with `password_verify()`, ensures a login token exists (generates one if blank), stores it in the DB and sets the cookie.
- Else includes the login form HTML.

Token handling:
- Login token generation (when missing) currently:
  - creates `random_int(0, 999999999999)`; casts to string; runs `password_hash()` on that number; checks DB for uniqueness of the hash; stores the bcrypt hash in `People.LoginToken` and returns it to the frontend cookie.
- Cookies are set via `setcookie('auth_token', $token, time()+..., '/')`.

APIs & access:
- Most API pages include `header.php` and call `is_user_in_right_area()` with the required role (1 for students, 4 for admins).
- `api/*` return JSON directly with `header('Content-Type: application/json')`.

Client-side search:
- `admin/student_search/student_search.js` calls `/api/student_search.php?query=...`, gets JSON, and inserts HTML into the page using `insertAdjacentHTML`.

Notes on include usage:
- Includes use `$_SERVER['DOCUMENT_ROOT']` (absolute) for importing helpers and sometimes relative includes for HTML templates.
- There is at least one previously-fixed issue where a missing slash in an include path caused a bug (it was committed).
