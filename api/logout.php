<?php
setcookie('auth_token', '', time() - 1, '/');                                   // delete token cookie
header('Content-Type: application/json');                                       // set header to be json
$json = json_encode([
    'result' => 'success'
]);
echo($json);
?>