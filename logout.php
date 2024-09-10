<?php
/*
    logout.php
    ----------

    I think this file is pretty self-explanatory. It logs the user out.
    

    Author: SteveGMedia
*/

require_once 'db.php'; // Make sure to include your database connection script
session_start();
handleLogout();
?>