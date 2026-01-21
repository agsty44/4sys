function login() {
    fetch('/api/login.php', {
        method: 'POST',
        body: new URLSearchParams({
            'username': document.querySelector('input[name="username"]').value,
            'pass': document.querySelector('input[name="pass"]').value
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Login failed');
        return response.json();
    })
    .then(data => {
        if (data.result === 'failure') { // login failed, reload login
            window.location.replace('/');
        }
        else {
            roleID = data.access_tier;
            let location = '';
            if (roleID === 0) location = '/bus';
            else if (roleID === 1) location = '/student';
            else if (roleID === 2) location = '/parent';
            else if (roleID === 3) location = '/teacher';
            else if (roleID === 4) location = '/admin';

            window.location.replace(location);
        }
    })
}