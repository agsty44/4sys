/* This file reuses most of the code 
from students/student_fetch_data.js.
the only things changed are the greeting,
and a few additional hyperlinks at the bottom of the page.
code has also been added to retrieve the GET value as that is quite important.*/

const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get('id');

document.addEventListener('DOMContentLoaded', () => {
    function selectNextPeriod(currDay, currTime) {
        /* lookup table 
            0845 -> 525 (1)
            0900 -> 540 (2)
            0955 -> 595 (3)
            1050 -> 650 (4)
            1110 -> 670 (5)
            1205 -> 725 (6)
            1300 -> 780 (7)
            1355 -> 835 (8)
            1450 -> 890 (9)
            1545 -> 945 (10)
            1645 -> 1005 (11) (this needs to refer to the next day - handle it as an edge case)

            0 - sun
            1 - mon
            2 - tue
            3 - wed
            4 - thu
            5 - fri
            6 - sat
        */

        // handle weekends - early return
        if (currDay === 0 || currDay === 6) return [1, 1];                          // day 1 (monday) period 1 (reg)

        let nextSesh;

        // if else tower begins here...
        if (currTime < 525) nextSesh = 1;                                           // before 08:45
        else if (currTime < 540) nextSesh = 2;                                      // before 09:00
        else if (currTime < 595) nextSesh = 3;                                      // before 09:55
        else if (currTime < 650) nextSesh = 4;                                      // before 10:50
        else if (currTime < 670) nextSesh = 5;                                      // before 11:10
        else if (currTime < 725) nextSesh = 6;                                      // before 12:05
        else if (currTime < 780) nextSesh = 7;                                      // before 13:00
        else if (currTime < 835) nextSesh = 8;                                      // before 13:55
        else if (currTime < 890) nextSesh = 9;                                      // before 14:50
        else if (currTime < 945) nextSesh = 10;                                     // before 15:45
        else nextSesh = 11;                                                         // no more today.

        // edge case - end of day / nextSesh === 11:
        if (nextSesh === 11) {
            currDay += 1;
            nextSesh = 1;
        }                       

        // final edge case - friday evening (currDay becomes 6...)
        if (currDay === 6) return [1, 1];                                       // day 1 period 1, because its friday (weekend)

        return [currDay, nextSesh];
    }

    fetch(`/api/view_student_as_admin.php?id=${id}`)
        .then(response => {
            if (!response.ok) throw new Error('Api failure');
            return response.json();
        })
        .then(data => {
            const student_info = data.student_info;
            const grades = data.grade_list;
            const bus = data.bus_name;
            const timetable = data.timetable;

            const now = new Date();
            const day = now.getDay();
            const hours = now.getHours();
            const minutes = now.getMinutes();
            const sumMinutes = hours * 60 + minutes;                            // converts time format into minutes.
            const weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const dayWord = weekdays[day];
            const formatMinutes = minutes < 10 ? `0${minutes}` : minutes;       // force it to take proper formatting

            // insert data
            document.getElementById('greet-user').textContent = `You are currently viewing ${student_info.OtherNames} ${student_info.LastName}'s info. `;
            document.getElementById('greet-subheading').textContent = `Their year group: ${student_info.YearGroup}`;

            const timetableBody = document.querySelector('#timetable tbody');

            // array to store session names
            const seshNames = {
                1: 'Registration, 08:45 -> 09:00',
                2: 'Period 1, 09:00 -> 09:55',
                3: 'Period 2, 09:55 -> 10:50',
                4: 'Break, 10:50 -> 11:10',
                5: 'Period 3, 11:10 -> 12:05',
                6: 'Period 4, 12:05 -> 13:00',
                7: 'Lunch, 13:00 -> 13:55',
                8: 'Period 5, 13:55 -> 14:50',
                9: 'Period 6, 14:50 -> 15:45',
                10: 'Enrichment, 15:45 -> 16:45'
            }

            // for every period...
            for (let period = 1; period <= 10; period++) {
                // create a new row
                const sessionsRow = document.createElement('tr');

                // first column states what period it is.
                const seshNameTD = document.createElement('td');
                seshNameTD.textContent = seshNames[period];
                sessionsRow.appendChild(seshNameTD);

                // for every column (day)
                for (let day = 1; day <= 5; day++) {
                    const sessionTD = document.createElement('td');

                    // if exists:
                    if (timetable[day] && timetable[day][period]) {
                        // check if there is a clash:
                        if (Array.isArray(timetable[day][period])) {
                            sessionContent = timetable[day][period].join(', ');
                        } 
                        // no clash
                        else {
                            sessionContent = timetable[day][period];
                        }

                        sessionTD.textContent = sessionContent;
                    } 
                    // if doesnt exist:
                    else { 
                        sessionTD.textContent = 'N/A - please fix!!!';
                    }

                    // add column
                    sessionsRow.appendChild(sessionTD);
                }

                // finally commit the row
                timetableBody.appendChild(sessionsRow);
            }

            const gradeBody = document.querySelector('#grades tbody');

            // log the 3 most recent grades into the table
            for (let enumerator = 0; enumerator <= 2; enumerator++) {
                try {
                    const gradeRow = document.createElement('tr');

                    const classTD = document.createElement('td');
                    const percentTD = document.createElement('td');
                    const commentTD = document.createElement('td');
                    const timestampTD = document.createElement('td');

                    classTD.textContent = grades[enumerator].ClassName;
                    percentTD.textContent = grades[enumerator].Percentage;
                    commentTD.textContent = grades[enumerator].Comment;
                    timestampTD.textContent = grades[enumerator].TimestampTD;

                    gradeRow.appendChild(classTD);
                    gradeRow.appendChild(percentTD);
                    gradeRow.appendChild(commentTD);
                    gradeRow.appendChild(timestampTD);

                    gradeBody.appendChild(gradeRow);
                } catch {

                } finally {
                    // empty to discard errors.
                }
            }

            // upcoming lesson calculations

            const [dayIndex, sessionIndex] = selectNextPeriod(day, sumMinutes);     // retrieve index of next lesson using our function we wrote

            let nextSessionString;

            // if the next lesson exists:
            if (timetable[dayIndex] && timetable[dayIndex][sessionIndex]) {
                // check if there is a clash:
                if (Array.isArray(timetable[dayIndex][sessionIndex])) {
                    nextSessionString = timetable[dayIndex][sessionIndex].join(', ');
                } 
                // no clash
                else {
                    nextSessionString = timetable[dayIndex][sessionIndex];
                }
            } 
            // if doesnt exist:
            else { 
                nextSessionString = 'N/A - please fix!!!';
            }

            document.getElementById('next-lesson').textContent = `${student_info.OtherNames} ${student_info.LastName}'s next lesson is: ${nextSessionString}`;
        })
        .catch(error => {
            console.error(error);
            // this error message is too general! alert('Failed to load timetable - contact admins.');
        });
})