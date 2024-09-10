<?php
/*
    api.php
    --------

    This file contains the API endpoints for the Vox Celeris site. 
    Requests are made via renderPage.js on index.php.

    It will only respond to POST requests, and will be routed to the appropriate
    function based on the 'endpoint' key in the JSON payload.
    

    Author: SteveGMedia
*/

require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['jsonPayload'])) {
        $input = json_decode($_POST['jsonPayload'], true);        
    }else{
        $input = json_decode(file_get_contents('php://input'), true);
    }

    if(isset($input['endpoint'])) {
        $endpoint = $input['endpoint'];

        switch($endpoint)
        {
            case "gallery":
                API_getUserGallery();
                break;
            case "posts":
                API_userGetPosts();
                break;
            case "followers":
                API_getUserFollowers();
                break;
            case "people":
                API_getPublicUserList();
                break;
            case "follow":
                if (isset($input['username'])) {
                    API_followUser($input['username']);
                }else{
                    echo json_encode(['message' => 'Username is required.', 'error' => true]);
                }
                break;
            case "unfollow":
                if (isset($input['username'])) {
                    API_unfollowUser($input['username']);
                }else{
                    echo json_encode(['message' => 'Username is required.', 'error' => true]);
                }
                break;
            case "following":
                API_getUserFollowing();
                break;
            case "makepost":

                if(!isset($input['message'])){
                    echo json_encode(['message' => 'Message is required.', 'error' => true]);
                    return;
                }

                $images = [];
                // Check if there are any files uploaded
                if (isset($_FILES)) {

                    foreach ($_FILES as $file) {
                        $photoExt = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $photoPath = POST_PHOTO_DIR . uniqid('', true) . '.' . $photoExt;

                        if (move_uploaded_file($file['tmp_name'], $photoPath)) {
                            $images[] = $photoPath;

                        } else {
                            echo json_encode(['message' => 'Failed to upload image.', 'error' => true]);
                            return;
                        }
                    }
                }

                /*
                    Initially I put some checks here, but since API_useMakePost()
                    has $images as an optional parameter, and we have an empty $images array here,
                    we can just do the checks in the function itself.
                */
                API_userMakePost($input['message'], $images); 

                break;
            default:
                echo json_encode(['message' => 'Invalid endpoint.', 'error' => true]);
                break;
        }

    }
}

/****************************

    API Endpoint Functions
*****************************/

// Fetches the logged in user's post feed.
function API_userGetPosts(){
    /* Make sure session exists */
    if (!sessionExists()) {
        echo json_encode(['message' => 'You are not logged in.', 'error' => true]);
        return;
    }

    $user_id = $_SESSION['user_id'];

    /* Validate user_id with regex pattern */
    if (!preg_match('/^\d+$/', $user_id)) {
        echo json_encode(['message' => 'Invalid user_id.', 'error' => true]);
        return;
    }
    
    global $conn;


    $sql = "SELECT 
                posts.id AS post_id,
                posts.post_text,
                posts.post_date,
                users.username,
                users.profile_photo
            FROM posts
            JOIN followers 
                ON posts.user_id = followers.follower_id
            JOIN users 
                ON posts.user_id = users.id
            WHERE followers.user_id = ?
            ORDER BY posts.post_date DESC";


    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $posts = [];

    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    
    $sql = "SELECT 
                photos.photo_url,
                photos.photo_caption
            FROM post_photos
            JOIN photos ON post_photos.photo_id = photos.id
            WHERE post_photos.post_id = ?";

    $photoStmt = $conn->prepare($sql);
    
    foreach ($posts as &$post) {
        $photoStmt->bind_param("i", $post['post_id']);
        $photoStmt->execute();
        $photoResult = $photoStmt->get_result();

        $post['photos'] = [];

        while ($photoRow = $photoResult->fetch_assoc()) {
            $post['photos'][] = $photoRow;
        }
    }

    //Fetch current user's username and profile photo
    $stmt = $conn->prepare("SELECT username, profile_photo FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    $response = [];

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($username, $profile_photo);
        $stmt->fetch();

        $response['username'] = $username;
        $response['profile_photo'] = $profile_photo;
        $response['posts'] = $posts;
    }
    
    echo json_encode($response);

    $stmt->close();
    $photoStmt->close();
}

// Creates a new post for the logged in user.
function API_userMakePost($message, $images = []){
    /* Make sure session exists */
    if (!sessionExists()) {
        echo json_encode(['message' => 'You are not logged in.', 'error' => true]);
        return;
    }

    $user_id = $_SESSION['user_id'];

    /* Validate user_id with regex pattern */
    if (!preg_match('/^\d+$/', $user_id)) {
        echo json_encode(['message' => 'Invalid user_id.', 'error' => true]);
        return;
    }

    global $conn;

    if (empty($message)) {
        echo json_encode(['message' => 'Post cannot be empty.', 'error' => true]);
        return;
    }

    if (strlen($message) > 500) {
        echo json_encode(['message' => 'Post cannot exceed 500 characters.', 'error' => true]);
        return;
    }

    /* First we insert the actual post */
    $stmt = $conn->prepare("INSERT INTO posts (user_id, post_text, post_date) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();

    $post_id = $stmt->insert_id;

    /* If there are images then we first insert the images into the photos table */
    if (count($images) > 0) {
        $photoStmt = $conn->prepare("INSERT INTO photos (user_id, photo_url, photo_caption, photo_date) VALUES (?, ?, ?, NOW())");

        /* Then we make sure to link the photos to the post by adding the photo_id and post_id to the post_photos junction table */
        foreach ($images as $image) {
            $photoStmt->bind_param("iss", $user_id, $image, $message);
            $photoStmt->execute();

            $photo_id = $photoStmt->insert_id;

            $postPhotoStmt = $conn->prepare("INSERT INTO post_photos (post_id, photo_id) VALUES (?, ?)");
            $postPhotoStmt->bind_param("ii", $post_id, $photo_id);
            $postPhotoStmt->execute();
            $postPhotoStmt->close();
        }

        $photoStmt->close();
    }

    echo json_encode(['message' => 'Post created successfully', 'error' => false]);
    $stmt->close();
}

// Fetches the logged in user's gallery.
function API_getUserGallery(){
    /* Make sure session exists */
    if (!sessionExists()) {
        echo json_encode(['message' => 'You are not logged in.', 'error' => true]);
        return;
    }
    $user_id = $_SESSION['user_id'];

    /* Validate user_id with regex pattern */
    if (!preg_match('/^\d+$/', $user_id)) {
        echo json_encode(['message' => 'Invalid user_id.', 'error' => true]);
        return;
    }

    global $conn;

    $sql = "SELECT 
                photos.photo_url,
                photos.photo_caption,
                photos.photo_date
            FROM photos
            WHERE photos.user_id = ?
            ORDER BY photos.photo_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $photos = [];

    while ($row = $result->fetch_assoc()) {
        $photos[] = $row;
    }

    if (count($photos) == 0) {
        echo json_encode(['message' => 'No photos found.', 'error' => true]);
        return;
    }

    echo json_encode($photos);

    $stmt->close();

}

// Follows a user.
function API_followUser($username) {
    /* Make sure session exists */
    if (!sessionExists()) {
        echo json_encode(['message' => 'You are not logged in.', 'error' => true]);
        return;
    }

    $user_id = $_SESSION['user_id'];

    if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        echo json_encode(['message' => 'Invalid username.', 'error' => true]);
        return;
    }

    /* Validate user_id with regex pattern */
    if (!preg_match('/^\d+$/', $user_id)) {
        echo json_encode(['message' => 'Invalid user_id.', 'error' => true]);
        return;
    }

    global $conn;

    // Check if the user to be followed exists and isn't a private account
    $stmt = $conn->prepare("SELECT id, private_account FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(['message' => 'User does not exist.', 'error' => true]);
        return;
    }

    $stmt->bind_result($followed_id, $private_account);
    $stmt->fetch();


    $sql = "SELECT 
                users.id, 
                users.username, 
                users.profile_photo
            FROM followers
            JOIN users ON followers.follower_id = users.id
            WHERE followers.user_id = ? AND users.id = ?;";

    // Check if the private user is following the current user
    $check_stmt = $conn->prepare($sql);
    $check_stmt->bind_param("ii", $user_id, $followed_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    $is_following = $check_stmt->num_rows;


    if ($is_following == 0 && $private_account == 1) {
        echo json_encode(['message' => 'Cannot follow a private account unless they follow you first.', 'error' => true]);
        return;
    }

    if ($is_following > 0) {
        echo json_encode(['message' => 'You are already following this user.', 'error' => true]);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO followers (user_id, follower_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $followed_id);
    $stmt->execute();

    echo json_encode(['message' => 'Followed user successfully.', 'error' => false]);

    $stmt->close();
}

// Unfollows a user.
function API_unfollowUser($username) {
    /* Make sure session exists */
    if (!sessionExists()) {
        echo json_encode(['message' => 'You are not logged in.', 'error' => true]);
        return;
    }

    $user_id = $_SESSION['user_id'];

    if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        echo json_encode(['message' => 'Invalid username.', 'error' => true]);
        return;
    }

    /* Validate user_id with regex pattern */
    if (!preg_match('/^\d+$/', $user_id)) {
        echo json_encode(['message' => 'Invalid user_id.', 'error' => true]);
        return;
    }

    global $conn;

    // Check if the user to be unfollowed exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(['message' => 'User does not exist.', 'error' => true]);
        return;
    }

    $stmt->bind_result($followed_id);
    $stmt->fetch();

    $sql = "SELECT 
                users.id, 
                users.username, 
                users.profile_photo
            FROM followers
            JOIN users ON followers.follower_id = users.id
            WHERE followers.user_id = ? AND users.id = ?";

    // Check if the user is already being followed
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $followed_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(['message' => 'You are not following this user.', 'error' => true]);
        return;
    }

    // Delete the follow relationship
    $stmt = $conn->prepare("DELETE FROM followers WHERE user_id = ? AND follower_id = ?");
    $stmt->bind_param("ii", $user_id, $followed_id);
    $stmt->execute();

    echo json_encode(['message' => 'Unfollowed user successfully.', 'error' => false]);

    $stmt->close();
}

//Fetches a list of users that the logged in user is following.
function API_getUserFollowing(){
    /* Make sure session exists */
    if (!sessionExists()) {
        echo json_encode(['message' => 'You are not logged in.', 'error' => true]);
        return;
    }

    $user_id = $_SESSION['user_id'];

    /* Validate user_id with regex pattern */
    if (!preg_match('/^\d+$/', $user_id)) {
        echo json_encode(['message' => 'Invalid user_id.', 'error' => true]);
        return;
    }

    global $conn;

    $sql = "SELECT 
                users.id, 
                users.username, 
                users.profile_photo,
                users.location,
                users.bio
            FROM followers
            JOIN users ON followers.follower_id = users.id
            WHERE followers.user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $following = [];

    while ($row = $result->fetch_assoc()) {
        $following[] = $row;
    }

    if (count($following) == 0) {
        echo json_encode(['message' => 'You are not following anyone.', 'error' => true]);
        return;
    }

    echo json_encode($following);
}

//Fetches a list of users that are following the logged in user.
function API_getUserFollowers(){
    /* Make sure session exists */
    if (!sessionExists()) {
        echo json_encode(['message' => 'You are not logged in.', 'error' => true]);
        return;
    }

    $user_id = $_SESSION['user_id'];

    /* Validate user_id with regex pattern */
    if (!preg_match('/^\d+$/', $user_id)) {
        echo json_encode(['message' => 'Invalid user_id.', 'error' => true]);
        return;
    }

    global $conn;

    $sql = "SELECT 
                users.id, 
                users.username, 
                users.profile_photo,
                users.location,
                users.bio,

                /* Check if the logged-in user follows this follower back */
                CASE WHEN mutual.user_id IS NOT NULL THEN 1 ELSE 0 END AS follows_back

            FROM followers
            JOIN users ON followers.user_id = users.id

            /* Left join to check if the user follows back the follower */
            LEFT JOIN followers AS mutual 
                ON mutual.user_id = ?
                AND mutual.follower_id = users.id

            WHERE followers.follower_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $followers = [];

    while ($row = $result->fetch_assoc()) {
        $followers[] = $row;
    }

    if (count($followers) == 0) {
        echo json_encode(['message' => 'No followers found.', 'error' => true]);
        return;
    }

    echo json_encode($followers);
}

// Fetches a list of public users.
function API_getPublicUserList(){
    /* Make sure session exists */
    if (!sessionExists()) {
        echo json_encode(['message' => 'You are not logged in.', 'error' => true]);
        return;
    }

    $user_id = $_SESSION['user_id'];

    /* Validate user_id with regex pattern */
    if (!preg_match('/^\d+$/', $user_id)) {
        echo json_encode(['message' => 'Invalid user_id.', 'error' => true]);
        return;
    }
    
    global $conn;

    /* I think this is probably the best way to query this? */
    $sql = "SELECT 
                users.id, 
                users.username, 
                users.profile_photo,
                users.location,
                users.bio,

                /* Check if the logged-in user is following this user */
                EXISTS (
                    SELECT 1 
                    FROM followers 
                    WHERE followers.user_id = ?
                    AND followers.follower_id = users.id
                ) AS is_following,
                
                /* Check if this user is following the logged-in user */
                EXISTS (
                    SELECT 1 
                    FROM followers 
                    WHERE followers.follower_id = ?
                    AND followers.user_id = users.id
                ) AS is_followed_by
                
            FROM users;
            ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode($users);

    $stmt->close();
}

$conn->close();
?>