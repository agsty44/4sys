<?php

// DB boilerplate:

$server = 'localhost';
$user = 'root';
$pass = 'password';
$db = '4sys_main';

$conn = new mysqli($server, $user, $pass, $db);

if ($conn->connect_error) {
    die('Failed connect: ' . $conn->connect_error);
}

// what is this script meant to do?
// this is the homepage of the website: it should handle all kinds of logins. if someone already has an auth cookie, we should check it and pass them to the right part of the website.
// first we should check if the login token exists.
// if it does, we should find the user that matches it. 'SELECT `PersonID` FROM `People` WHERE `LoginToken` = ?' bind_params($_COOKIE['logintoken']);
// if not, we will check for user + pass in the POST form.
// these will be compared with the db (passwords are bcrypted)
// if the account being logged into has a logintoken, we will set that token to be the cookie
// if not, we will generate a token for the user.

// check existence of cookie
if (isset($_COOKIE['authToken'])) {
    grab_login_from_cookie();
    die();
}
else if (isset($_POST['email']) && isset($_POST['pass'])) {
    grab_login_from_email_pass();
    die();
}
else {
    header('http://localhost/');
    die();
}

function grab_login_from_cookie() {
    global $conn;

    $token = $_COOKIE['authToken'];                                             // extract into a variable for cleaner code
    $sql = 'SELECT `AccessTier` FROM `People` WHERE `LoginToken` = ?';          // this is the layout of our SQL statement to return a userID.
    $query = $conn->prepare($sql);                                       // prepare statement for execution
    $query->bind_param('s', $token);                               // bind parameters
    $query->execute();                                                          // execute
    $returnedID = $query->get_result();                                         // get results

    // handle if the cookie is bad
    if ($returnedID->num_rows == 0) {
        setcookie('authToken', '', time() - 1);           // delete the cookie, its bad.
        header('http://localhost/');                                    // send them home
        die();
    }

    // if the user logs in with a good token we should refresh it
    setcookie('authToken', $token, time() + 86400 * 7);   // set cookie life to 1 day (86400 seconds) * 7

    $record = $returnedID->fetch_assoc();                                       // fetch record
    $accessTier = $record['AccessTier'];                                        // return access level to send them to the right dashboard.

    send_to_dashboard($accessTier);

    $query->close();                                                            // close stmt
}

function grab_login_from_email_pass() {
    global $conn;

    $email = $_POST['email'];                                                   // extract POST data
    $pass = $_POST['pass'];
    
    // first we need the users password (if it exists)
    $sql = 'SELECT `AccessTier`, `PassHash` FROM `People` WHERE `Email` = ?';                 // SQL layout
    $query = $conn->prepare($sql);                                       // prepare statement for execution
    $query->bind_param('s', $email);                               // bind parameters
    $query->execute();                                                          // execute
    $returnedHash = $query->get_result();                                       // get results

    // if no hash present
    if ($returnedHash->num_rows == 0) {                                         // no account with that email, get out of here
        header('http://localhost/');
        echo('Incorrect login');
        die();
    }

    // now do comparison
    $record = $returnedHash->fetch_assoc();                                     // fetch record
    $passHash = $record['PassHash'];                                            // return passhash for verification
    $accessTier = $record['AccessTier']; 

    // mismatch
    if (!password_verify($pass, $passHash)) {                   // password verification failed, get out of here
        header('http://localhost/');
        echo('Incorrect login');
        die();
    }

    send_to_dashboard($accessTier);

    $query->close();
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
    }
}

?>