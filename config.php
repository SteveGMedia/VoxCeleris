<?php
/*
    config.php
    --------
    
    This file contains all the configuration settings for the application.


    Author: SteveGMedia
*/

/*
    Database Settings (Change these)
    -----------------
    These settings are used to connect to the database.

    DB_HOST: The hostname of the database server
    DB_USER: The username to connect to the database
    DB_PASS: The password to connect to the database
    DB_NAME: The name of the database to connect to
*/
define('DB_HOST', 'localhost');         /* CHANGE IF NEEDED */
define('DB_USER', '');                  /* ADD THIS */
define('DB_PASS', '');                  /* ADD THIS */
define('DB_NAME', 'vox_celeris');       /* CHANGE IF NEEDED */


/*
    Site Settings (Change these)
    -------------
    These settings are used to configure the site.

    SITE_URL: The base URL of the site EX: define('SITE_URL', 'http://mysite.net')
    SITE_NAME: The name of the site (used in the title bar)
*/
define('SITE_URL', '');                 /* ADD THIS */  
define('SITE_NAME', 'Vox Celeris');     /* CHANGE IF YOU WANT TO */

/*
    Email Settings (Change these to your own settings)
    --------------
    These settings are used to configure how the site sends emails.

    EMAIL_FROM: The email address to send emails from
    EMAIL_NAME: The name to send emails from
    EMAIL_PASS: The password to send emails
    SMTP_HOST: The hostname of the SMTP server
    SMTP_PORT: The port of the SMTP server
    SMTP_AUTH: Whether to use SMTP authentication
*/
define('EMAIL_FROM', '');               /* ADD THIS */
define('EMAIL_NAME', 'Vox Celeris');    /* CHANGE IF YOU WANT TO */
define('EMAIL_PASS', '');               /* ADD THIS */
define('SMTP_HOST', '');                /* ADD THIS */
define('SMTP_PORT', 25);                /* CHANGE IF NEEDED */
define('SMTP_AUTH', false);             /* CHANGE IF NEEDED */


/*
    File Upload Settings (Change these as needed)
    --------------------
    These settings are used to configure how the site handles file uploads.
    These are mostly self-explanatory.

    UPLOAD_DIR: The directory to store uploaded files
    PROFILE_PHOTO_DIR: The directory to store profile photos
    POST_PHOTO_DIR: The directory to store post photos
    MAX_FILE_SIZE: The maximum file size allowed
    ALLOWED_FILE_TYPES: The allowed file types
*/
define('UPLOAD_DIR', './assets/uploads/');
define('PROFILE_PHOTO_DIR', UPLOAD_DIR . 'profile_photos/');
define('POST_PHOTO_DIR', UPLOAD_DIR . 'post_photos/');
define('MAX_FILE_SIZE', (1024*1024) * 15); // 15MB is extremely generous
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

/*
    MISC Settings (Can be changed to whatever you want)
    -------------
    These are miscellaneous settings used throughout the site.

    MAX_POST_LENGTH: The maximum length of a user's post.
*/
define('MAX_POST_LENGTH', 500); 
?>
