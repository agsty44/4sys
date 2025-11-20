function showResults(searchQuery) {
    const searchResults = document.getElementById("student-page-links");

    if (searchQuery == "") {
        //set blank as no query
        searchResults.innerHTML = "";
        return;
    }

    fetch(`/api/student_search.php?query=${searchQuery}`)
        .then(response => {
                if (!response.ok) throw new Error('Api failure');
                return response.json();
            })
        .then(data => {

            for (let enumerator = 0; enumerator < data.length; enumerator++) {
                let studentLink = `<a href="/admin/view_student/view_student.php?id=${data[enumerator].PersonID}">${data[enumerator].OtherNames} ${data[enumerator].LastName} [${data[enumerator].PersonID}]</a>`;
                searchResults.insertAdjacentHTML("beforeend", studentLink);
            }
        })
}