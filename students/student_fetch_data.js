fetch('http://4sys.local:8080/api/student_data.php')
    .then(response => {
        if (!response.ok) throw new Error('Api failure');
        return response.json();
    })
    .then(data => {
        const student_info = data.student_info;
        const grades = data.grade_list;
        const bus = data.bus_name;
        // we need to write code for the timetable once refactored

        // insert data
        document.getElementById('greet-user').textContent = `Welcome back, ${student_info.OtherNames} ${student_info.LastName}.`;
        document.getElementById('greet-subheading').textContent = `Year group: ${student_info.YearGroup}`;
        
    })
    .catch(error => {

    });