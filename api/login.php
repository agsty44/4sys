<?php

include($_SERVER['DOCUMENT_ROOT'] . '/header.php');                             // import header library

// check for login and password
if (!isset($_POST['username']) || !isset($_POST['pass'])) {
    header('Content-Type: application/json');
    $json = json_encode([
    'result' => 'failure'
    ]);
    echo($json);
}

$user = $_POST['username'];
$pass = $_POST['pass'];

// retrieve hash from db
// first we need the users password (if it exists)
$sql = 'SELECT `PersonID`, `AccessTier`, `PassHash`
        FROM `People` 
        WHERE `Username` = ?';                                                  // SQL layout
$query = $conn->prepare($sql);                                                  // prepare statement for execution
$query->bind_param('s', $user);                                                 // bind parameters
$query->execute();                                                              // execute
$returned_hash = $query->get_result();                                          // get results

// if no hash present
if ($returned_hash->num_rows == 0) {                                            // no account with that username, get out of here
    include($_SERVER['DOCUMENT_ROOT'] . '/login_form.html');
    echo('Incorrect login');
    die();
}

// retrieve record
$record = $returned_hash->fetch_assoc();                                        // fetch record
$pass_hash = $record['PassHash'];                                               // return passhash for verification
$access_tier = $record['AccessTier'];                                           // return access tier for sorting
$identifier = $record['PersonID'];                                              // return identifier

// compare password
if (!password_verify($pass, $pass_hash)) {
    header('Content-Type: application/json');
    $json = json_encode([
    'result' => 'failure'
    ]);
    echo($json);
}

$sql = 'SELECT `LoginToken` 
        FROM `People` 
        WHERE `PersonID` = ?';                                                  // retrieve the login token from the db
$query = $conn->prepare($sql);                                                  // prepare statement for execution
$query->bind_param('i', $identifier);                                           // bind parameters
$query->execute();                                                              // execute
$returned_token = $query->get_result();                                         // get results
$record = $returned_token->fetch_assoc();                                       // fetch record
$access_token = $record['LoginToken'];                                          // return login token to check
$query->close();                                                                // close query

//handle existence of token
if ($access_token == '') {
    $uniqueness_of_token = 1;
    // until that token returns no rows, generate a new one.
    while ($uniqueness_of_token != 0) {
        //upload new token
        $token_number = random_int(0, 999999999999);                            // generate a number up to 1 trillion
        $token_number = (string) $token_number;                                 // string conversion
        $token_hash = password_hash($token_number,
        PASSWORD_DEFAULT);                                                      // convert it to a bcrypt hash

        //check for existence of token
        $sql = 'SELECT * FROM `People` WHERE `LoginToken` = ?';                 // count the rows
        $query = $conn->prepare($sql);                                          // prepare
        $query->bind_param('s', $token_hash);                                   // bind parameters
        $query->execute();                                                      // execute
        $token_integrity = $query->get_result();                                // get result
        $uniqueness_of_token = $token_integrity->num_rows;                      // check uniqueness
        }                                                                       // when we exit the loop, the token will be confirmed to be unique

    $query->close();                                                            // close query

    // insert the new token
    $sql = 'UPDATE `People` 
            SET `LoginToken` = ? 
            WHERE `PersonID` = ?';                                              // update the login token that matches the person id
    $query = $conn->prepare($sql);                                              // prepare
    $query->bind_param('si',$token_hash, $identifier);                          // bind parameters
    $query->execute();                                                          // execute
    $query->close();                                                            // close query
}

// pull access token from db
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

$expire = time() + (60 * 60 * 24 * 7); // 7 days from now

// set the cookie as we have safety flags
setcookie(
    'accessToken',
    $access_token,
    [
        'expires' => $expire,
        'path' => '/',
        'domain' => '',           // current domain
        'secure' => true,         // HTTPS only
        'httponly' => true,       // not accessible via JS
        'samesite' => 'Strict'   // CSRF mitigation
    ]
);

// return json
header('Content-Type: application/json');
$json = json_encode([
    'result' => 'success',
    'access_tier' => $access_tier
]);
echo($json);