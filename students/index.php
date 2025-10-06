<?php
// import header lib. this establishes db connections
include($_SERVER['DOCUMENT_ROOT'] . '/header.php');

is_user_in_right_area(1);                                                       // pass the access tier of this page into the parameters.
$identifier = grab_user_id();

// what is this page meant to display?
// this page will return the users name and year group as a kind of "title", so they know they are logged in correctly.
// it will then display the users 3 most recent grades
// it will also display their general timetable, and specifically their next upcoming lesson.
// the above code is likely to be used in most instances of index.php for account verification.

include('./student_home_page.html');
die();
?>