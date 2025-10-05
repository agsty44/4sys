<?php
// import header lib. this establishes db connections
include($_SERVER['DOCUMENT_ROOT'] . '/header.php');

is_user_in_right_area(1);                                                       // pass the access tier of this page into the parameters.
$identifier = grab_user_id();

// what is this page meant to display?
// this page will return the users name and year group as a kind of "title", so they know they are logged in correctly.
// it will then display the users 3 most recent grades
// it will also display their general timetable, and specifically their next upcoming lesson.
// the above code is likely to be used in most instances of index.php for account verification.

// generate a web page relevant to the student.

// first, we need to withdraw any relevant data.

// return info on the user
$sql = 'SELECT `OtherNames`, `LastName`, `YearGroup` FROM `People` WHERE `PersonID` = ?';
$query = $conn->prepare($sql);
$query->bind_param('i', $identifier);
$query->execute();
$user_info = $query->get_result();
$user_data_record = $user_info->fetch_assoc();
$query->close();

// return the most recent 3 grades
$sql = 'SELECT c.`ClassName`, g.`Percentage`, g.`Comment` 
        FROM `Classes` AS c
        INNER JOIN `Grades` AS g ON c.`ClassID` = g.`ClassID` 
        WHERE g.`StudentID` = ? 
        ORDER BY g.Timestamp DESC 
        LIMIT 3';

$query = $conn->prepare($sql);
$query->bind_param('i', $identifier);
$query->execute();
$grade_result = $query->get_result();

$grade_list = [];                                                           // define an array which stores the grades

while ($row = $grade_result->fetch_assoc()) {                                   // iteratively add each grade to the array, this can be iterated later.
    $grade_list[] = $row;
}

// down here we need to use some form of concatenation to generate the html for the page, or we can use AJAX->js.

$query->close();
?>