<?php
// logout.php
// delete cookie then include the login form.
setcookie('auth_token', '', time() - 1);                                // delete the cookie, its bad.
include($_SERVER['DOCUMENT_ROOT'] . '/login_form.html');                // send them home
die();
?>