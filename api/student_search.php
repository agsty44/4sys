<?php
// import header lib. this establishes db connections
include($_SERVER['DOCUMENT_ROOT'] . '/header.php');

is_user_in_right_area(4);                                                       // pass the access tier of this page into the parameters.
$identifier = grab_user_id();

if (!isset($_GET['query'])) {
    die();                                                                      // if no query is given, just exit     
}

$search_query = $_GET['query'];

$sql = 'SELECT `PersonID`, `OtherNames`, `LastName`
        FROM `People`
        WHERE (CONCAT(`OtherNames`, " ", `LastName`) LIKE ?
        OR CONCAT(`OtherNames`, `LastName`) LIKE ?)
        AND `AccessTier` = 1';

// this query covers both names with a space and without, now we need to setup the parameter
$sql_parameter = '%' . $search_query . '%';

$query = $conn->prepare($sql);
$query->bind_param('ss', $sql_parameter, $sql_parameter);
$query->execute();
$search_result = $query->get_result();

$student_list = [];                                                             // define an array which stores the grades

while ($row = $search_result->fetch_assoc()) {                                  // iteratively add each grade to the array, this can be iterated later.
    $student_list[] = $row;
}

$query->close();

// return json data
header('Content-Type: application/json');                                       // set header to be json
echo(json_encode([
    'students' => $student_list
]))
?>