<?php
/*
    auth.php
    --------

    This file is used to authenticate a user's account.

    It checks if the user is already logged in, and if not, it will display a login form. The form
    will send a POST request to itself to authenticate the user.

    This could be integrated into renderPage.js, and api.php.
    

    Author: SteveGMedia
*/

session_start();
require_once 'db.php'; // Make sure to include your database connection script

if(sessionExists()){
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = json_decode(file_get_contents('php://input'), true);

    if(isset($input['username']) && isset($input['password'])) {
        $username = filter_var($input['username'], FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z0-9]+$/")));
        $password = $input['password'];

        $authResponse = authenticateUser($username, $password);

        if (isset($authResponse['id'])) {
            $_SESSION['user_id'] = $authResponse['id'];
            $_SESSION['username'] = $authResponse['username'];

            echo json_encode(['message' => 'Login successful. Redirecting...']);
        } else {

            if(isset($authResponse['error'])) {
                echo json_encode(['message' => $authResponse['error']]);
            } else {
                echo json_encode(['message' => 'An error occurred while trying to authenticate your account.']);
            }
        }
    }

    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vox Celeris - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/js/all.min.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body class="bg-[#3d0e0f] text-white antialiased">

    <div class="flex flex-col overflow justify-center items-center min-h-screen px-4">

        <!-- Title Area -->
        <div class="flex flex-col items-center justify-center mt-10 bg-black bg-opacity-50 rounded-lg p-4 max-w-md w-full">
            <h1 class="text-2xl md:text-4xl lg:text-6xl font-semibold text-pink-800 text-shadow">Vox Celeris</h1>
        </div>

        <!-- Login Form Area -->
        <div class="flex flex-col items-center mt-8 mb-4 bg-black bg-opacity-50 rounded-lg p-4 max-w-md w-full">
            <h2 class="text-xl md:text-2xl lg:text-4xl font-semibold text-red-800 text-shadow mb-4">Login</h2>

            <form id="login-form" class="w-full">
                <!-- Username Input -->
                <div class="mb-4">
                    <label id=" for="username" class="block text-sm font-bold mb-2">Username</label>
                    <input type="text" id="username" name="username" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your username" required>
                </div>

                <!-- Password Input -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-bold mb-2">Password</label>
                    <input type="password" id="password" name="password" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your password" required>
                </div>

                <!-- Login Button -->
                <div class="flex items-center justify-between">
                    <button id="btn-login" type="submit" class="bg-pink-800 hover:bg-red-800 text-white font-bold py-2 px-4 rounded w-full">
                        Login
                    </button>
                </div>
            </form>

            <div id="errorMessage" class="hidden mt-4 p-2 bg-black bg-opacity-25 text-white text-center rounded-lg">
            </div>

            <!-- Additional Links -->
            <div class="mt-2 text-center">
                <a href="register.php" class="text-sm text-pink-800 hover:text-red-800">Don't have an account? Register</a>
            </div>
            <div class="text-center">
                <a href="#" class="text-sm text-pink-800 hover:text-red-800">Forgot Password?</a>
            </div>
        </div>
    </div>

    <script src="./assets/js/authenticate.js"></script>
</body>
</html>
