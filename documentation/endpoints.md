# API/Endpoints

## General

All endpoints return JSON. Plain and simple.

## Specific

### admin_data.php

Requires: a login token, which the corresponding user has access level 4 (admin page).

Returns: the corresponding record for that user.

### logout.php

Requires: nothing, but is pointless without a login token.

Returns: login token removed from cookies, and a success flag.

### student_data.php

Requires: a login token, which the corresponding user has access level 1 (student page).

Returns: the corresponding record, their timetable, and their recent grades.

### student_search.php

Requires: a login token, which the corresponding user has access level 4 (admin page), and a query to search for students.

Returns: a list of students names and IDs.

### view_student_as_admin.php

Requires: a login token, which the corresponding user has access level 4 (admin page), and a student ID.

Returns: the corresponding record, their timetable, and their recent grades.