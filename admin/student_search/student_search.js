function showResults(searchQuery) {
    const searchResults = document.getElementById("student-page-links");

    searchResults.innerHTML = ""; //set blank to prevent dupes

    if (searchQuery == "") {
        //set blank as no query
        return;
    }

    fetch('/api/student_search.php', {
        method: 'POST',
        body: new URLSearchParams({
            'query': searchQuery
        })
    })
        .then(response => {
                if (!response.ok) throw new Error('Api failure');
                return response.json();
            })
        .then(data => {

            for (let student of data.students) {
                let studentLink = `<a href="/admin/view_student/view_student.php?id=${student.PersonID}">${student.OtherNames} ${student.LastName} [${student.PersonID}]</a><br>`;
                searchResults.insertAdjacentHTML("beforeend", studentLink);
            }
        })
}