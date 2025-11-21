<?php
// import header lib. this establishes db connections
include($_SERVER['DOCUMENT_ROOT'] . '/header.php');

is_user_in_right_area(4);                                                       // pass the access tier of this page into the parameters.
$identifier = grab_user_id();

if (!isset($_GET['id'])) {
    header('Location: /admin/student_search/student_search.php');
    die();
}

include('view_student.html');
?>