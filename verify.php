<?php
/*
    verify.php
    ----------

    This file is used to verify a user's account. It allows the user to enter a verification code
    that was sent to their email address. If the verification code is valid, the user's account is
    activated.

    This could probably be integrated broken down and integrated into renderPage.js, and api.php.

    Author: SteveGMedia
*/
session_start();
require_once 'db.php'; /* Include the database connection */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if(isset($input['email'])) {
        $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL); /* sanitize email */
    
        if (resendVerificationEmail($email)) {
            echo json_encode(['message' => 'Email sent successfully']);
        } else {
            echo json_encode(['message' => 'Failed to send email']);
        }
    }

    if(isset($input['verificationCode'])) {
        $verificationCode = $input['verificationCode'];

        /* make sure verification code is alphanumeric */
        if (!ctype_alnum($verificationCode)) {
            echo json_encode(['message' => 'Invalid verification code']);
            return;
        }

        $activated = verifyUser($verificationCode);

        if ($activated) {
            echo json_encode(['message' => 'Account verified successfully']);
        } else {
            echo json_encode(['message' => 'Either the verification code is invalid, expired or maybe the account is already active?']);
        }
    }

    exit();
}   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vox Celeris - Verify</title>
    <link rel="icon" href="./favicon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/js/all.min.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body class="bg-[#3d0e0f] text-white antialiased">

    <div class="flex flex-col justify-center items-center min-h-screen px-4">

        <!-- Title Area -->
        <div class="flex flex-col items-center justify-center mt-10 bg-black bg-opacity-50 rounded-lg p-4 max-w-md w-full">
            <h1 class="text-2xl md:text-4xl lg:text-6xl font-semibold text-pink-800 text-shadow">Vox Celeris</h1>
        </div>

        <!-- Login Form Area -->
        <div class="flex flex-col items-center mt-8 mb-4 bg-black bg-opacity-50 rounded-lg p-4 max-w-md w-full">
            <h2 class="text-xl md:text-2xl lg:text-4xl font-semibold text-red-800 text-shadow mb-4">Account Verification</h2>

            <div class="w-full">

                <!-- Verification Code Input -->
                <div class="mb-6">
                    <label for="verificationCode" class="block text-sm font-bold mb-2">Verification Code</label>
                    <input type="text" id="verificationCode" name="verificationCode" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your verification code">
                </div>
                <div class="flex items center justify-between">
                    <button type="submit" class="bg-pink-800 hover:bg-red-800 text-white font-bold py-2 px-4 rounded w-full">
                        Verify
                    </button>
                </div>

                <div class="my-4 text-center text-white font-bold">
            or
        </div>

                <!-- Email Input -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-bold mb-2">Didn't receive the verification email?</label>
                    <input type="email" id="email" name="email" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your email">
                </div>
                <div class="flex items center justify-between mt-4">
                    <button type="submit" class="bg-pink-800 hover:bg-red-800 text-white font-bold py-2 px-4 rounded w-full">
                        Re-send Email
                    </button>
                </div>
            </div>

            <!-- Error Message -->

            <div id="errorMessage" class="hidden mt-4 p-2 bg-black bg-opacity-25 text-white text-center rounded-lg"></div>

        </div>
    </div>
    <!-- JS File for verification requests -->
    <script src="./assets/js/verification.js"></script>
</body>
</html>
