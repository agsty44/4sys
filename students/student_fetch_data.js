fetch('http://4sys.local:8080/api/student_data.php')
    .then(response => {
        if (!response.ok) throw new Error('Api failure');
        return response.json
    })
    .then(data => {
        
    })
    .catch(error => {

    });