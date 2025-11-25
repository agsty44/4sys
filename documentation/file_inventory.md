# File inventory — key files

This is a short inventory of the most relevant files I inspected.

Top-level:
- `index.php` — site entry page; handles login flow by cookie or POST credentials and dispatches to dashboards.
- `header.php` — DB connection and auth helper functions (`grab_login_from_cookie`, `grab_user_id`, `is_user_in_right_area`, `send_to_dashboard`). Contains DB credentials in-source.
- `login_form.html`, `login_form.css` — login form UI.
- `logout.php` — deletes auth cookie and sends user to login.

`api/` (APIs returning JSON):
- `api/admin_data.php` — returns admin info; requires admin access.
- `api/student_data.php` — returns a logged-in student's data and recent grades.
- `api/student_search.php` — search students endpoint, expects `?query=...`.
- `api/view_student_as_admin.php` — returns a specific student by id (requires admin access).

`admin/` and subfolders:
- `admin/index.php` — admin landing page; includes helpers and forwards to `admin/admin_home_page.html`.
- `admin/student_search/student_search.php` — admin view that includes `student_search.html` (UI) and `student_search.js` (client-side search).
- `admin/student_search/student_search.html` — search UI.
- `admin/student_search/student_search.js` — client-side fetch of `api/student_search.php` and inserts links into the DOM.
- `admin/view_student/view_student.php` (+ html/js/css) — admin view for a single student; uses `$_GET['id']`.

`students/`:
- `students/index.php` — student landing page.
- `students/student_fetch_data.js` — fetches `api/student_data.php`.

Misc:
- `TempAdmin/createuser.php` — creates a `People` record from POSTed email/pass; currently has no auth guard.

Notes:
- Prepared statements are used in DB access consistently.
- Passwords are hashed using `password_hash()` and verified with `password_verify()`.
- Cookie name used: `auth_token` (set via `setcookie` in multiple places).
- There is a mix of absolute includes using `$_SERVER['DOCUMENT_ROOT']` and relative includes.
