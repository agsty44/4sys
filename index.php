<?php

// what is this script meant to do?
// this is the homepage of the website: it should handle all kinds of logins. if someone already has an auth cookie, we should check it and pass them to the right part of the website.
// first we should check if the login token exists.
// if it does, we should find the user that matches it. 'SELECT `PersonID` FROM `People` WHERE `LoginToken` = ?' bind_params($_COOKIE['logintoken']);
// if not, we will check for user + pass in the POST form.
// these will be compared with the db (passwords are bcrypted)
// if the account being logged into has a logintoken, we will set that token to be the cookie
// if not, we will generate a token for the user.

// import header library
include($_SERVER['DOCUMENT_ROOT'] . '/header.php');

// check existence of cookie
if (isset($_COOKIE['auth_token'])) {
    $access_param = grab_login_from_cookie();
    send_to_dashboard($access_param);
    die();
}
else if (isset($_POST['username']) && isset($_POST['pass'])) {
    $access_param = grab_login_from_username_pass();
    send_to_dashboard($access_param);
    die();
}
else {
    include($_SERVER['DOCUMENT_ROOT'] . '/login_form.html');
    die();
}

function grab_login_from_username_pass() {
    global $conn;

    $username = $_POST['username'];                                             // extract POST data
    $pass = $_POST['pass'];
    
    // first we need the users password (if it exists)
    $sql = 'SELECT `PersonID`, `AccessTier`, `PassHash`
            FROM `People` 
            WHERE `Username` = ?';                                              // SQL layout
    $query = $conn->prepare($sql);                                              // prepare statement for execution
    $query->bind_param('s', $username);                                         // bind parameters
    $query->execute();                                                          // execute
    $returned_hash = $query->get_result();                                      // get results

    // if no hash present
    if ($returned_hash->num_rows == 0) {                                        // no account with that username, get out of here
        include($_SERVER['DOCUMENT_ROOT'] . '/login_form.html');
        echo('Incorrect login');
        die();
    }

    // now do comparison
    $record = $returned_hash->fetch_assoc();                                    // fetch record
    $pass_hash = $record['PassHash'];                                           // return passhash for verification
    $access_tier = $record['AccessTier'];                                       // return access tier for sorting
    $identifier = $record['PersonID'];                                          // return identifier

    // mismatch
    if (!password_verify($pass, $pass_hash)) {                                  // password verification failed, get out of here
        include($_SERVER['DOCUMENT_ROOT'] . '/login_form.html');
        echo('Incorrect login');
        die();
    }

    $query->close();

    //check for an access token in db, and generate one if needed.
    $sql = 'SELECT `LoginToken` 
            FROM `People` 
            WHERE `PersonID` = ?';                                              // retrieve the login token from the db
    $query = $conn->prepare($sql);                                              // prepare statement for execution
    $query->bind_param('i', $identifier);                                       // bind parameters
    $query->execute();                                                          // execute
    $returned_token = $query->get_result();                                     // get results
    $record = $returned_token->fetch_assoc();                                   // fetch record
    $access_token = $record['LoginToken'];                                      // return login token to check
    $query->close();                                                            // close query

    //handle existence of token
    if ($access_token == '') {
        $uniqueness_of_token = 1;

        // until that token returns no rows, generate a new one.
        while ($uniqueness_of_token != 0) {
            //upload new token
            $token_number = random_int(0, 999999999999);                        // generate a number up to 1 trillion
            $token_number = (string) $token_number;                             // string conversion
            $token_hash = password_hash($token_number,
            PASSWORD_DEFAULT);                                                  // convert it to a bcrypt hash

            //check for existence of token
            $sql = 'SELECT * FROM `People` WHERE `LoginToken` = ?';             // count the rows
            $query = $conn->prepare($sql);                                      // prepare
            $query->bind_param('s', $token_hash);                               // bind parameters
            $query->execute();                                                  // execute
            $token_integrity = $query->get_result();                            // get result
            $uniqueness_of_token = $token_integrity->num_rows;                  // check uniqueness
        }                                                                       // when we exit the loop, the token will be confirmed to be unique

        $query->close();                                                        // close query

        // insert the new token
        $sql = 'UPDATE `People` 
                SET `LoginToken` = ? 
                WHERE `PersonID` = ?';                                          // update the login token that matches the person id
        $query = $conn->prepare($sql);                                          // prepare
        $query->bind_param('si',$token_hash, $identifier);                      // bind parameters
        $query->execute();                                                      // execute
        $query->close();                                                        // close query
    }

    $sql = 'SELECT `LoginToken`
            FROM `People` 
            WHERE `PersonID` = ?';                                              // retrieve the login token from the db
    $query = $conn->prepare($sql);                                              // prepare statement for execution
    $query->bind_param('i', $identifier);                                       // bind parameters
    $query->execute();                                                          // execute
    $returned_token = $query->get_result();                                     // get results
    $record = $returned_token->fetch_assoc();                                   // fetch record
    $access_token = $record['LoginToken'];                                      // return login token to check
    $query->close();                                                            // close query

    // finally we can set the cookie of the auth token.
    setcookie('auth_token', $access_token, time() + 86400 * 7, '/');

    return $access_tier;
}
?>