<?php
// import header lib. this establishes db connections
include($_SERVER['DOCUMENT_ROOT'] . '/header.php');

is_user_in_right_area(4);                                                       // pass the access tier of this page into the parameters.
$identifier = grab_user_id();

if (!isset($_GET['id'])) {
    header('Location: /admin/student_search/student_search.php');
    die();
}

// SELECT COUNT TO CHECK USER EXISTS - VERY NEEDED!!!
$sql = 'SELECT COUNT(*)
        FROM `People`
        WHERE `PersonID` = ?
        AND AccessTier = 1';
$query = $conn->prepare($sql);
$query->bind_param('i', $identifier);
$query->execute();
$query_result = $query->get_result();
$user_real = $query_result->fetch_row()[0] == 1;                                // assert if this value is equal to one

if (!$user_real) {
    header('Location: /admin/student_search/student_search.php');
    die();
}

include('view_student.html');
?>