document.addEventListener('DOMContentLoaded', () => {
    fetch('/api/admin_data.php')
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

            document.getElementById('greet-user').textContent = `Welcome back, ${adminInfo.OtherNames} ${adminInfo.LastName}. It is currently ${hours}:${minutes} on a ${dayWord}. Have a great day.`
        })
        .catch(error => {
            console.log(error);
        })
})