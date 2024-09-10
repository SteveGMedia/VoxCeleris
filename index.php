<?php
/*
    index.php
    --------

    This is the main page of the application. It's mostly just a barebones template using Tailwind CSS.
    
    
    Author: SteveGMedia
*/
session_start();
require_once 'db.php'; /* Include the database connection */

if (!sessionExists()) {
    header("Location: auth.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vox Celeris</title>
    <link rel="icon" href="./favicon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/js/all.min.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body class="bg-[#3d0e0f] text-white antialiased">

    <div class="flex flex-col justify-center items-center min-h-screen px-4">

        <!-- Title Area -->
        <div class="flex flex-col items-center justify-center mt-10 bg-black bg-opacity-50 rounded-lg p-4 max-w-5xl w-full">
            <a id='nav-home' href="#"><h1 class="text-2xl md:text-4xl lg:text-6xl font-semibold text-pink-800 text-shadow">Vox Celeris</h1></a>
        </div>
        <!-- Navigation Pane -->
        <div id='user-nav' class="flex flex-wrap justify-center space-x-8 mt-4 bg-black bg-opacity-50 rounded-lg p-4 max-w-5xl w-full"></div>

        <!-- Content Area -->
        <div id="content-container" class="flex flex-col items-center mt-8 mb-4 bg-black bg-opacity-50 rounded-lg p-4 max-w-5xl w-full"></div>
    </div>

    <script src="./assets/js/renderPage.js"></script>
</body>
</html>