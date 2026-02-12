<?php
header('Content-Type: application/json');                                       // set header to be json

if (!isset($_COOKIE['auth_token'])) {
    $json = json_encode([
        'result' => 'failure'
    ]);
    echo($json);
    die();
}

setcookie('auth_token', '', time() - 1, '/');                                   // delete token cookie

$json = json_encode([
    'result' => 'success'
]);

echo($json);
die();
?>