<?php

include($_SERVER['DOCUMENT_ROOT'] . '/header.php');                             // import header library

// check for login and password
if (!isset($_POST['username']) || !isset($_POST['pass'])) {
    header('Content-Type: application/json');
    $json = json_encode([
    'result' => 'failure'
    ]);
    echo($json);
    die();
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
    header('Content-Type: application/json');
    $json = json_encode([
    'result' => 'failure'
    ]);
    echo($json);
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
    die();
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

if (empty($access_token)) {
    $new_token = bin2hex(random_bytes(16));                                     // generate new token

    $sql = 'UPDATE `People` 
            SET `LoginToken` = ? 
            WHERE `PersonID` = ?';                                              // sql to update token

    $query = $conn->prepare($sql);                                              // prepare statement for execution
    $query->bind_param('si', $new_token, $identifier);                          // bind parameters
    $query->execute();                                                          // execute
    $query->close();                                                            // close query
    $access_token = $new_token;                                                 // set access token to new token
}


$expire = time() + (60 * 60 * 24 * 7);                                          // 7 days from now

// set the cookie as we have safety flags
setcookie(
    'auth_token', 
    $access_token,
    [
        'expires' => $expire,
        'path' => '/',
        'domain' => '',                                                         // current domain
        'secure' => true,                                                       // HTTPS only
        'httponly' => true,                                                     // not accessible via JS
        'samesite' => 'Strict'                                                  // CSRF mitigation
    ]
);

// return json
header('Content-Type: application/json');
$json = json_encode([
    'result' => 'success',
    'access_tier' => $access_tier
]);
echo($json);