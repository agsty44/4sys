document.addEventListener('DOMContentLoaded', () => {
    fetch('/api/admin_data.php')                                                // fetch query
        .then(response => {
            if (!response.ok) throw new Error('Api failure');
            return response.json();
        })
        .then(data => {
            const adminInfo = data.admin_info;

            const now = new Date();
            const day = now.getDay();
            const hours = now.getHours();
            const minutes = now.getMinutes();
            const weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const dayWord = weekdays[day];
            const formatMinutes = minutes < 10 ? `0${minutes}` : minutes;       // force it to take proper formatting
            document.getElementById('greet-user').textContent = `Welcome back, ${adminInfo.OtherNames} ${adminInfo.LastName}. It is currently ${hours}:${minutes} on a ${dayWord}. Have a great day.`
        })
        .catch(error => {
            console.log(error);
        })
})

function logout() {
    fetch('/api/logout.php')
        .then(response => {
            if (!response.ok) throw new Error('Api failure');
            return response.json();
        })
        .then(data => {
            window.location.replace('/')
        })
}