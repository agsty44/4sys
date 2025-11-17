<?php
// import header lib. this establishes db connections
include($_SERVER['DOCUMENT_ROOT'] . '/header.php');

is_user_in_right_area(4);                                                       // pass the access tier of this page into the parameters.
$identifier = grab_user_id();

// this page will display the users name, a greeting, and some hyperlinks.

include('./admin_home_page.html');
die();
?>