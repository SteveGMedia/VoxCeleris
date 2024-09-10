/*
    verification.js
    ---------------

    This file is for user verification after registration has posted to the database.
    It sends a POST request to the server with the verification code.

    This could probably be integrated with renderPage.js.

    
    Author: SteveGMedia
*/

/* Function to flash an error message on the screen */
function flashErrorMessage(message, delay = 3) {
    document.getElementById('errorMessage').innerText = message;
    document.getElementById('errorMessage').classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('errorMessage').classList.add('hidden');
    }, delay * 1000);
}

/* Function to bind to the Re-Send Email button */
function resendEmail() {
    let email = document.getElementById('email').value;

    // Check if email is empty and re-hide error after a few seconds.
    if (email === '') {
        flashErrorMessage('Email cannot be empty');
        return;
    }

    fetch('verify.php', {
        method: 'POST',
        body: JSON.stringify({ email: email }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        flashErrorMessage(data.message);
    });
}

/* Function to bind to the Verify button */
function verifyAccount() {
    let verificationCode = document.getElementById('verificationCode').value;

    // Check if verification code is empty and re-hide error after a few seconds.
    if (verificationCode === '') {
        document.getElementById('errorMessage').innerText = 'Verification code cannot be empty';
        document.getElementById('errorMessage').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('errorMessage').classList.add('hidden');
        }, 3000);
        return;
    }

    fetch('verify.php', {
        method: 'POST',
        body: JSON.stringify({ verificationCode: verificationCode }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        flashErrorMessage(data.message);
    });
}

/* make sure the page is loaded before running the script */
document.addEventListener('DOMContentLoaded', function() {

    /* add event listeners to the buttons */
    document.querySelector('button[type="submit"]').addEventListener('click', verifyAccount);
    document.querySelectorAll('button[type="submit"]')[1].addEventListener('click', resendEmail);

    /* if token get parameter is set, add the token to the form */
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    if (token) {
        document.getElementById('verificationCode').value = token;

        /*
             re-write the URL if the user clicks an email link to remove the token 
             GET parameter from the browser window 
        */
        if (window.location.search.includes('token')) 
        {
            window.history.replaceState({}, document.title, window.location.pathname);

            /* auto-verify the account */
            verifyAccount();
        }
    }
});