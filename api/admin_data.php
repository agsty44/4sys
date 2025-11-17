<?php
// import header lib. this establishes db connections
include($_SERVER['DOCUMENT_ROOT'] . '/header.php');

is_user_in_right_area(4);                                                       // pass the access tier of this page into the parameters.
$identifier = grab_user_id();

$sql = 'SELECT `OtherNames`, `LastName`
        FROM `People`
        WHERE `PersonID` = ?';
$query = $conn->prepare($sql);
$query->bind_param('i', $identifier);
$query->execute();
$user_info = $query->get_result();
$user_data_record = $user_info->fetch_assoc();
$query->close();

// return json data
header('Content-Type: application/json');                                       // set header to be json
echo(json_encode([
    'admin_info' => $user_data_record
]))
?>