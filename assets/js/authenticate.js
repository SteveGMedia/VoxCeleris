/*

    authenticate.js
    ---------------

    This is responsible for user authentication. It sends a POST request to the server with the username and password.
    This could probably be integrated with renderPage.js.


    Author: SteveGMedia
*/

function handleAuth()
{
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    fetch('auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, password })
    })
    .then(response => response.json())
    .then(data => {
        const errorMessage = document.getElementById('errorMessage');

        errorMessage.textContent = data.message;
        errorMessage.classList.remove('hidden');

        if (data.message === 'Login successful. Redirecting...') {
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);
        }
    });

}

/* make sure the page is loaded before running the script */
document.addEventListener('DOMContentLoaded', function() {
    /* needs to be able to login if enter is pressed */
    const loginForm = document.getElementById('login-form');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleAuth();
    });

});