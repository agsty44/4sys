## 2025-11-25 14:32:10 +0000
### ADD: documentation of apis/file structure etc (GENERATED WITH COPILOT)
Notable changes:
- Added `documentation/README.md`, `documentation/architecture.md`, `documentation/file_inventory.md`, `documentation/security_review.md`, and `documentation/next_steps.md`.

Justification:
- Consolidates the code review findings and next steps in-repo for handover and auditing. This is meta work to improve maintainability and to make the project's design and risks explicit.

---

## 2025-11-25 14:23:43 +0000
### FIX: include path had missing /
Notable changes:
- Edited `header.php` to correct include paths that used `$_SERVER['DOCUMENT_ROOT']` without a leading/trailing slash.

Code excerpt (fixed include example):
```php
include($_SERVER['DOCUMENT_ROOT'] . '/login_form.html');
```

Justification:
- Ensures includes work reliably across servers regardless of whether DOCUMENT_ROOT includes a trailing slash; prevents fatal include errors when rendering login and other templates.

---

## 2025-11-24 10:46:55 +0000
### FIX: identifier in view_student.php refers to the GET var not the account ID
Notable changes:
- Edited `admin/view_student/view_student.php` to correctly use the requested student's id (`$_GET['id']`) when rendering the admin view.

Justification:
- Fixes a functional bug that caused the wrong student to be displayed to admins. Important for correctness and auditability of admin operations.

---

## 2025-11-21 14:34:45 +0000
### ADD: api and frontend calls for the student page view feature
Notable changes:
- Added `api/view_student_as_admin.php` and `admin/view_student/*` (PHP/HTML/JS/CSS) to render and fetch a single student's details from the admin UI.

Justification:
- Implements the admin detail-view workflow using a server-side JSON endpoint plus client-side rendering. This separates concerns and enables reuse of the API for other admin tools.

---

## 2025-11-20 14:41:27 +0000
### ADD: ability to search for students and view their info in admin section
Notable changes:
- Added `admin/student_search/student_search.php`, `student_search.html`, `student_search.js`, `student_search.css`, and `api/student_search.php`.

Code excerpt (client-side result insertion):
```javascript
let studentLink = `<a href="/admin/view_student/view_student.php?id=${student.PersonID}">${student.OtherNames} ${student.LastName} [${student.PersonID}]</a><br>`;
searchResults.insertAdjacentHTML("beforeend", studentLink);
```

Justification:
- Provides a fast, searchable admin workflow. The implementation favoured quick rendering in the browser; subsequent commits fixed iteration/duplication bugs. Note: the approach uses raw HTML insertion which is functional but needs sanitisation hardening (see `security_review.md`).

---

## 2025-11-20 (follow-ups)
### FIX: SQL/parameter and JS iteration fixes for student_search
Notable changes:
- Several small commits corrected SQL fields, adjusted the number of parameters for prepared statements, and improved the JS to iterate over returned objects and clear prior results.

Justification:
- Stabilised the search feature after initial rollout; typical iterative fixes to ensure correctness and correct data flow between API and UI.

---

## 2025-11-17 15:32:23 +0000
### ADD: api to return admin info
Notable changes:
- Added `api/admin_data.php` and frontend scaffolding (`admin/admin_fetch_data.js`, `admin/admin_home_page.html`, `admin/index.php`).

Justification:
- This adds admin-specific data endpoints and UI, mirroring the pattern used for student dashboards and simplifying the admin UX.

---

## 2025-11-13 .. 2025-11-11
### MULTIPLE: student dashboard — timetable and grades
Notable changes:
- Incremental commits enhanced `api/student_data.php` and `students/*` pages with timetable parsing, grades display, UI layout work, and JS defensive coding (try/finally, DOM readiness wrappers).

Justification:
- Feature development followed an iterative approach: add incremental server-side fields, then progressively render them on the student dashboard. The approach prioritised fast feedback and iterative UX improvements.

---

## 2025-10-02 .. 2025-10-07
### MULTIPLE: core auth & header refactor, token work
Notable changes:
- Moved auth/db boilerplate from `index.php` to `header.php` and added helper functions: `grab_login_from_cookie()`, `grab_user_id()`, `is_user_in_right_area()`, and `send_to_dashboard()`.
- Added token generation and login handling in `index.php` (later consolidated into `header.php`).

Code excerpt (current token generation style, from `index.php`):
```php
$token_number = random_int(0, 999999999999);
$token_number = (string) $token_number;
$token_hash = password_hash($token_number, PASSWORD_DEFAULT);
// uniqueness check & store bcrypt of token in DB
```

Justification:
- Centralising authentication logic reduces duplication and simplifies enforcing access control across pages. Token-based login enables persistent sessions without storing user credentials in cookies. The current token scheme works but can be hardened (higher entropy and cookie flags recommended).

---

## Selected earlier improvements (useful for retro report)
- Standardised include paths to use `$_SERVER['DOCUMENT_ROOT']` so the project is portable across dev environments.
- Added logout endpoint (`logout.php`) and associated UI elements.
- Adjusted cookie handling to use path `'/'` to avoid stale/mixed cookies across directories.

Justification:
- These operational fixes reduced environment-dependent bugs and made session handling more predictable across pages.

---

## Observations and short recommendations (from commit history)
- The development pattern is feature-first with fast front-end iteration followed by incremental bugfixes. This increases velocity but means some security hardening was deferred to later commits (see `security_review.md`).
- Key recommended retrospective actions (already documented in `next_steps.md`):
  - Move DB credentials out of source control (env/config),
  - Harden token generation (use `random_bytes`),
  - Add cookie security flags (`HttpOnly`, `Secure`, `SameSite`),
  - Sanitize data before inserting into the DOM (avoid insertAdjacentHTML with unescaped values).

---