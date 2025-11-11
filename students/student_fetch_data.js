fetch('http://4sys.local:8080/api/student_data.php')
    .then(response => {
        if (!response.ok) throw new Error('Api failure');
        return response.json();
    })
    .then(data => {
        const student_info = data.student_info;
        const grades = data.grade_list;
        const bus = data.bus_name;
        const timetable = data.timetable;

        // insert data
        document.getElementById('greet-user').textContent = `Welcome back, ${student_info.OtherNames} ${student_info.LastName}.`;
        document.getElementById('greet-subheading').textContent = `Year group: ${student_info.YearGroup}`;

        const timetableBody = document.querySelector('#timetable tbody');

        // array to store session names
        const seshNames = {
            1: 'Registration, 08:45->09:00',
            2: 'Period 1, 09:00->09:55',
            3: 'Period 2, 09:55->10:50',
            4: 'Break, 10:50->11:10',
            5: 'Period 3, 11:10->12:05',
            6: 'Period 4, 12:05->13:00',
            7: 'Lunch, 13:00->13:55',
            8: 'Period 5, 13:55->14:50',
            9: 'Period 6, 14:50->15:45',
            10: 'Enrichment, 15:45->16:45'
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
                    sessionTD.textContent = 'N/A - contact admin';
                }

                // add column
                sessionsRow.appendChild(sessionTD);
            }

            // finally commit the row
            timetableBody.appendChild(sessionsRow);
        }

        const gradeBody = document.querySelector('#grades tbody');

        for (let enumerator = 0; enumerator <= 2; enumerator++) {
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
        }

    })
    .catch(error => {
        console.error(error);
        alert('Failed to load timetable - contact admins.');
    });