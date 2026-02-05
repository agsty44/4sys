<?php
/* COMMENTED OUT --- for now...
// error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

/* DB boilerplate...
$server = 'localhost';
$user = 'root';
$pass = 'password';
$db = '4sys_main';
*/

require_once(__DIR__ . '/../4sys_config/config.php');

$conn = new mysqli($server, $user, $pass, $db);

if ($conn->connect_error) {
    die('Failed connect: ' . $conn->connect_error);
}

function send_to_dashboard($level) {
    // switch case to forward users to the right access page
    switch ($level) {
        case 0:                                                                 // bus drivers
            header('Location: /bus/index.php');
            die();
        case 1:                                                                 // students
            header('Location: /students/index.php');  
            die();
        case 2:                                                                 // parents
            header('Location: /parents/index.php');  
            die();
        case 3:                                                                 // teachers
            header('Location: /teachers/index.php');  
            die();
        case 4:                                                                 // admins
            header('Location: /admin/index.php');  
            die();
        default:
            include($_SERVER['DOCUMENT_ROOT'] . '/login_form.html');
            die();
    }
}

function grab_login_from_cookie() {
    global $conn;

    // this returns an access tier given an auth token, and also handles logic for invalid tokens.
    // if an invalid token is given, the user will be sent to login.

    $token = $_COOKIE['auth_token'] ?? null;                                    // extract into a variable for cleaner code. if the cookie doesn't exist return null to handle with isset()

    if ($token == null) {                                                       // die if no token
        include($_SERVER['DOCUMENT_ROOT'] . '/login_form.html');
        die();
    }


    $sql = 'SELECT `AccessTier`
            FROM `People` 
            WHERE `LoginToken` = ?';                                            // this is the layout of our SQL statement to return a userID.
    $query = $conn->prepare($sql);                                              // prepare statement for execution
    $query->bind_param('s', $token);                                           // bind parameters
    $query->execute();                                                          // execute
    $returned_id = $query->get_result();                                        // get results

    // handle if the cookie is bad
    if ($returned_id->num_rows == 0) {
        setcookie('auth_token', '', time() - 1, '/');                           // delete the cookie, its bad.
        include($_SERVER['DOCUMENT_ROOT'] . '/login_form.html');                // send them home
        die();
    }

    // if the user logs in with a good token we should refresh it
    setcookie('auth_token', $token, time() + 86400 * 7, '/');                   // set cookie life to 1 day (86400 seconds) * 7

    $record = $returned_id->fetch_assoc();                                      // fetch record
    $access_tier = $record['AccessTier'];                                       // return access level to send them to the right dashboard.
    $query->close();                                                            // close stmt

    return $access_tier;
}

function grab_user_id() {
    global $conn;

    // this function returns a user's id given their login token. 
    // this doesn't need to be verified as the function is only every called after
    // the usage of grab_login_from_cookie(), so valid auth_token is already handled.

    $token = $_COOKIE['auth_token'] ?? null;                                    // fetch login token or NULL if no token

    if ($token == null) {                                                       // die if token non existent
        include($_SERVER['DOCUMENT_ROOT'] . '/login_form.html');
        die();
    }

    $sql = 'SELECT `PersonID` 
            FROM `People` 
            WHERE `LoginToken` = ?';                                            // sql to fetch a users identifier
    $query = $conn->prepare($sql);
    $query->bind_param('s', $token);
    $query->execute();
    $returned_id = $query->get_result();
    $record = $returned_id->fetch_assoc();
    $identifier = $record['PersonID'];
    $query->close();
    return $identifier;
}

function is_user_in_right_area($intended_access_level) {
    $user_access_level = grab_login_from_cookie();                              // not only check that the login is right, but grab the access level to check this is the right location.

    if ($user_access_level != $intended_access_level) {                         // if the access level is incorrect, we should send them to the right one.
        send_to_dashboard($user_access_level);
    }
}
?>