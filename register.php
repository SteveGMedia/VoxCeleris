<?php
/*
    register.php
    ------------

    This file is used to register a new user account. It takes input from the user and creates a new
    account in the database. It also sends an email to the user with a verification link to activate
    their account.

    Since the rest of the site uses a single-page application architecture, this file could be
    integrated into renderPage.js and api.php at some point.


    Author: SteveGMedia
*/

session_start();
require_once 'db.php'; /* Include the database connection */

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    // Sanitize and validate inputs
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $username = trim($_POST['username']);
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $dob = trim($_POST['dob']);
    $bio = trim($_POST['bio']);
    $location = trim($_POST['location']);
    $privacy = trim($_POST['privacy']) === 'private' ? 1 : 0;
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $profilePhoto = $_FILES['profilePhoto'];

    /* Create an array to store any errors */
    $errors = [];

    if(!filter_var($username, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z0-9]+$/")))){
        $errors[] = "Invalid username.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    // Handle profile photo upload if provided
    $profilePhotoPath = '';
    if ($profilePhoto['error'] === UPLOAD_ERR_OK) {
        $photoDir = PROFILE_PHOTO_DIR;
        $photoExt = pathinfo($profilePhoto['name'], PATHINFO_EXTENSION);
        $profilePhotoPath = $photoDir . uniqid('', true) . '.' . $photoExt;

        // Move uploaded file to the specified directory
        if (!move_uploaded_file($profilePhoto['tmp_name'], $profilePhotoPath)) {
            $errors[] = "Failed to upload profile photo.";
        }
    }

    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Check if email or username already exists
        $stmt = $conn->prepare("SELECT email, username FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();


        if ($stmt->num_rows > 0) {
            $stmt->bind_result($existingEmail, $existingUsername);
            $stmt->fetch();

            if ($existingEmail === $email) {
                $errors[] = "Email address already in use.";
            }

            if ($existingUsername === $username) {
                $errors[] = "Username already in use.";
            }
        }else{
            /* Create new user account and get the user ID */

            $userDetails = array(
                'email' => $email,
                'username' => $username,
                'passhash' => $hashedPassword,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phoneNumber,
                'dob' => $dob,
                'profile_photo' => $profilePhotoPath,
                'bio' => $bio,
                'location' => $location,
                'private_account' => $privacy,
                'active_account' => 0
            );

             $user_id = createUser($userDetails);

            if ($user_id) {
                $token = createRegistrationToken($user_id);

                if ($token) {
                    $verificationLink = SITE_URL . "/verify.php?token=$token";

                    require_once 'mailer.php';

                    $subject = 'Vox Celeris - Verify Your Account';
                    $body = "Please click the following link to verify your account: <a href='$verificationLink'>$verificationLink</a>";
                    $altBody = "Please visit the following link to verify your account: $verificationLink";

                    if (sendEmail($email, $subject, $body, $altBody)) {
                        $errors[] = "Registration successful! Please check your email to verify your account.";
                    } else {
                        $errors[] = "Failed to send activation email. Try again later.";
                    }

                }else{
                    $errors[] = "Could not create activation token. Please try again.";
                }
            } else {
                $errors[] = "Could not create new user account. Please try again.";
            }

            $stmt->close();
        }
    $conn->close();
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vox Celeris - Register</title>
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

        <!-- Registration Form Area -->
        <div class="flex flex-col items-center mt-8 mb-4 bg-black bg-opacity-50 rounded-lg p-4 max-w-md w-full">
            <h2 class="text-xl md:text-2xl lg:text-4xl font-semibold text-red-800 text-shadow mb-4">Register</h2>

            <form action="register.php" method="POST" enctype="multipart/form-data" class="w-full">

                <!-- First Name Input -->
                <div class="mb-4">
                    <label for="firstName" class="block text-sm font-bold mb-2">Username</label>
                    <input type="text" id="username" name="username" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your username" required>
                </div>


                <!-- Email Input -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-bold mb-2">Email</label>
                    <input type="email" id="email" name="email" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your email" required>
                </div>

                <!-- First Name Input -->
                <div class="mb-4">
                    <label for="firstName" class="block text-sm font-bold mb-2">First Name</label>
                    <input type="text" id="firstName" name="firstName" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your first name" required>
                </div>

                <!-- Last Name Input -->
                <div class="mb-4">
                    <label for="lastName" class="block text-sm font-bold mb-2">Last Name</label>
                    <input type="text" id="lastName" name="lastName" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your last name" required>
                </div>

                <!-- Phone Number Input -->
                <div class="mb-4">
                    <label for="phoneNumber" class="block text-sm font-bold mb-2">Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your phone number" required>
                </div>

                <!-- Date of Birth Input -->
                <div class="mb-4">
                    <label for="dob" class="block text-sm font-bold mb-2">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" required>
                </div>

                <!-- Profile Photo Input -->
                <div class="mb-4">
                    <label for="profilePhoto" class="block text-sm font-bold mb-2">Profile Photo</label>
                    <input type="file" id="profilePhoto" name="profilePhoto" accept="image/*" class="bg-pink-800 text-white font-bold py-2 px-4 rounded w-full">
                </div>

                <!-- Bio Input -->
                <div class="mb-4">
                    <label for="bio" class="block text-sm font-bold mb-2">Bio</label>
                    <textarea id="bio" name="bio" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Tell us about yourself..."></textarea>
                </div>

                <!-- Location Input -->
                <div class="mb-4">
                    <label for="location" class="block text-sm font-bold mb-2">Location</label>
                    <input type="text" id="location" name="location" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your location">
                </div>

                <!-- Password Input -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-bold mb-2">Password</label>
                    <input type="password" id="password" name="password" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your password" required>
                </div>

                <!-- Confirm Password Input -->
                <div class="mb-6">
                    <label for="confirmPassword" class="block text-sm font-bold mb-2">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Confirm your password" required>
                </div>

                <!-- Account Privacy -->
                <div class="mb-4">
                    <label for="privacy" class="block text-sm font-bold mb-2">Account Privacy</label>
                    <select id="privacy" name="privacy" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full">
                        <option value="public">Public</option>
                        <option value="private">Private</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-pink-800 hover:bg-red-800 text-white font-bold py-2 px-4 rounded w-full">
                        Register
                    </button>
                </div>
            </form>

            <!-- Error Messages -->
            <?php if (isset($errors) && !empty($errors)): ?>
                <div id="errorMessage" class="mt-4 p-2 bg-black bg-opacity-25 text-white text-center rounded-lg">
                    <?php echo implode('<br>', $errors); ?>
                </div>
            <?php endif; ?>

            <!-- Additional Links -->
            <div class="mt-4 text-center">
                <a href="auth.php" class="text-sm text-pink-800 hover:text-red-800">Already have an account? Login</a>
            </div>
        </div>
    </div>
</body>
</html>