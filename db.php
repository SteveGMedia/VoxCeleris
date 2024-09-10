<?php
/*
    db.php
    ------

    This file contains a portion of the database functions used in the application. (The other portion is in api.php)
    These are mostly functions that interact with the database to create, update, and delete user accounts.


    Author: SteveGMedia
*/

require_once 'config.php';

/* Create connection and obtain our credentials from config.php */
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

/* Make sure connection is established */
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
/* Use the utf-8 character set */
$conn->set_charset("utf8");


/****************************

    Registration Functions

*****************************/

/* Function to create a new user account */
function createUser($userDetails){
    global $conn;

    $stmt = $conn->prepare("INSERT INTO users (email, username, passhash, first_name, last_name, phone, dob, profile_photo, bio, location, private_account, active_account) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssss", $userDetails['email'], $userDetails['username'], $userDetails['passhash'], $userDetails['first_name'], $userDetails['last_name'], $userDetails['phone'], $userDetails['dob'], $userDetails['profile_photo'], $userDetails['bio'], $userDetails['location'], $userDetails['private_account'], $userDetails['active_account']);
    $stmt->execute();
    return $stmt->insert_id;
}

/* Creates a unique activation code upon registration */
function createRegistrationToken($user_id){
    global $conn;

    // Create a verification token
    $newToken = bin2hex(random_bytes(16));
    $tokenExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = $conn->prepare("INSERT INTO registration_tokens (user_id, token, token_expires) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $newToken, $tokenExpires);
    $stmt->execute();
    return $newToken;
}

/* 
    Checks if a user account is not activated, and creates a new token if none exist
*/
function refreshExpiredToken($user_id){
    global $conn;

    /* Check if active_account on a user is set to 0 first, if it is, then we can refresh the token */
    $stmt = $conn->prepare("SELECT active_account FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($active_account);
        $stmt->fetch();
        
        if ($active_account == 0) {
            $stmt->close();
            $newToken = createRegistrationToken($user_id);
            return $newToken;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/* Function to resend the verification email */
function resendVerificationEmail($email)
{
    /* If user is not activated, we can resend the email 
    also perform a join to registration_tokens to check if a token already exists
    */

    global $conn;

    $stmt = $conn->prepare("SELECT users.id FROM users LEFT JOIN registration_tokens ON users.id = registration_tokens.user_id WHERE users.email = ? AND users.active_account = 0 AND registration_tokens.token IS NULL");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();

        $newToken = refreshExpiredToken($user_id);

        if ($newToken) {
            $to = $email;
            $subject = "Activate your account";
            $message = "Click the link below to activate your account:\n\n";
            $message .= SITE_URL . "/verify.php?token=" . $newToken;

            $headers = "From: " . EMAIL_NAME . " <" . EMAIL_FROM . ">\r\n";

            require_once 'mailer.php';

            if (sendEmail($to, $subject, $message, $headers)) {
                return true;
            }
        }
    }

    return false;
}

/* 
    Function to activate a user account 
*/
function verifyUser($token){
    global $conn;

    $stmt = $conn->prepare("SELECT user_id FROM registration_tokens WHERE token = ? AND token_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        $stmt->close();

        // Activate the user account
        $stmt = $conn->prepare("UPDATE users SET active_account = 1 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Delete the token
        $stmt = $conn->prepare("DELETE FROM registration_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        return true;
    } else {
        return false;
    }
}

/* Handle user authentication */
function authenticateUser($username, $password){
    global $conn;

    $stmt = $conn->prepare("SELECT id, username, passhash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $passhash);
        $stmt->fetch();

        if (password_verify($password, $passhash)) {
            return ['id' => $id, 'username' => $username];
        } else {
            return ['error' => 'Invalid username or password'];
        }
    } else {
        return ['error' => 'No user found matching your input.'];
    }
}

function sessionExists(){
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
        return false;
    }
    return true;
}

/* Logout / Destroy Session */
function handleLogout(){
    $_SESSION = array();

    session_destroy();
    header("Location: auth.php");
    exit;
}


?>
