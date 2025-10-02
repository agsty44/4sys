<?php

// DB boilerplate...
$server = 'localhost';
$user = 'root';
$pass = 'password';
$db = '4sys_main';

$conn = new mysqli($server, $user, $pass, $db);

if ($conn->connect_error) {
    die('Failed connect: ' . $conn->connect_error);
}

function send_to_dashboard($level) {
    // switch case to forward users to the right access page
    switch ($level) {
        case 0:                                                                 // bus drivers
            header('http://localhost/bus/');
            break;
        case 1:                                                                 // students
            header('http://localhost/students/');  
            break;
        case 2:                                                                 // parents
            header('http://localhost/parents/');  
            break;
        case 3:                                                                 // teachers
            header('http://localhost/teachers/');  
            break;
        case 4:                                                                 // admins
            header('http://localhost/admin/');  
            break;
        default:
            include('loginform.html');
    }
}

function grab_login_from_cookie() {
    global $conn;

    $token = $_COOKIE['auth_token'];                                            // extract into a variable for cleaner code
    $sql = 'SELECT `AccessTier` FROM `People` WHERE `LoginToken` = ?';          // this is the layout of our SQL statement to return a userID.
    $query = $conn->prepare($sql);                                              // prepare statement for execution
    $query->bind_param('s', $token);                                            // bind parameters
    $query->execute();                                                          // execute
    $returned_id = $query->get_result();                                        // get results

    // handle if the cookie is bad
    if ($returned_id->num_rows == 0) {
        setcookie('auth_token', '', time() - 1);                                // delete the cookie, its bad.
        include('loginform.html');                                              // send them home
        die();
    }

    // if the user logs in with a good token we should refresh it
    setcookie('auth_token', $token, time() + 86400 * 7);                        // set cookie life to 1 day (86400 seconds) * 7

    $record = $returned_id->fetch_assoc();                                      // fetch record
    $accessTier = $record['AccessTier'];                                        // return access level to send them to the right dashboard.
    $query->close();                                                            // close stmt
}

?>