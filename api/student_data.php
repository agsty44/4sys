<?php
// API PAGE WHICH RETURNS DATA ON A GIVEN USER.
// TODO - POST IMPLEMENTATION FOR PLUGIN PURPOSES?
// ALTerNATIVE - REWRITE THIS FOR ADMIN ACCESS - is_user_in_right_area() now goes with parameter 4
// and identifier is taken from POST/GET. - call this student_data_all.php perhaps?

// import header lib. this establishes db connections
include($_SERVER['DOCUMENT_ROOT'] . '/header.php');

is_user_in_right_area(1);                                                       // pass the access tier of this page into the parameters.
$identifier = grab_user_id();

// return info on the user
$sql = 'SELECT `OtherNames`, `LastName`, `YearGroup`
        FROM `People`
        WHERE `PersonID` = ?';
$query = $conn->prepare($sql);
$query->bind_param('i', $identifier);
$query->execute();
$user_info = $query->get_result();
$user_data_record = $user_info->fetch_assoc();
$query->close();

// return the most recent 3 grades, joining the name of the class based on the class ID used to identify the grade
$sql = 'SELECT c.`ClassName`, g.`Percentage`, g.`Comment`, g.`Timestamp`
        FROM `Classes` AS c
        INNER JOIN `Grades` AS g 
        ON c.`ClassID` = g.`ClassID` 
        WHERE g.`StudentID` = ? 
        ORDER BY g.Timestamp DESC 
        LIMIT 3';

$query = $conn->prepare($sql);
$query->bind_param('i', $identifier);
$query->execute();
$grade_result = $query->get_result();

$grade_list = [];                                                               // define an array which stores the grades

while ($row = $grade_result->fetch_assoc()) {                                   // iteratively add each grade to the array, this can be iterated later.
    $grade_list[] = $row;
}

$query->close();

// return the students bus name
$sql = 'SELECT b.`RouteName`
        FROM `Buses` AS b
        INNER JOIN `People` AS p 
        ON b.`BusID` = p.`BusID`
        WHERE p.`PersonID` = ?';

$query = $conn->prepare($sql);
$query->bind_param('i', $identifier);
$query->execute();
$bus_result = $query->get_result();
$bus_name = $bus_result->fetch_assoc()['RouteName'];                            // this MIGHT look like messy syntax, but it just retrieves the route name from the record

// collect timetable data
$sql = 'SELECT t.`DayID`, t.`SessionID`, c.`ClassName`, c.`YearGroup`, c.`Room`, p.`OtherNames`, p.`LastName`
        FROM `Timetable` AS t
        INNER JOIN `Classes` AS c
        ON t.`ClassID` = c.`ClassID`
        INNER JOIN `People` AS p
        ON p.`PersonID` = c.`TeacherID`
        WHERE t.`StudentID` = ?
        ORDER BY t.`DayID`, t.`SessionID`';

$query = $conn->prepare($sql);
$query->bind_param('i', $identifier);
$query->execute();
$timetable_result = $query->get_result();

$timetable = [];
$timetable['warnings'] = [];

while ($row = $timetable_result->fetch_assoc()) {
        // extract data from the row
        $day = $row['DayID'];
        $period = $row['SessionID'];
        $class = $row['ClassName'];
        $year = $row['YearGroup'];
        $room = $row['Room'];
        $name = $row['OtherNames'] . ' ' . $row['LastName'];

        // if there isnt an array for the day, create
        if (!isset($timetable[$day])) {
                $timetable[$day] = [];
        }

        // if there isnt an array for the day->session, create
        if (!isset($timetable[$day][$period])) {
                $timetable[$day][$period] = [];
        } else {
                $timetable['warnings'][] = 'WARNING: DUPLICATE ON: DAY ' . $day . 'AND PERIOD ' . $session;
        }

        // append to the array, use this for handling if multiple classes exist
        $timetable[$day][$period][] = $class . ', '. $year . ', '. $room . ', '. $name;
}

// return json data
header('Content-Type: application/json');                                       // set header to be json
echo(json_encode([                                                              // echo as json
    'student_info' => $user_data_record,
    'grade_list' => $grade_list,
    'bus_name' => $bus_name,
    'timetable' => $timetable
]))                             

?>