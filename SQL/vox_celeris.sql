/* 
    Vox Celeris Database Schema
    ---------------------------

    This file contains the SQL schema for the Vox Celeris database. 

    Author: SteveGMedia
*/

DROP DATABASE IF EXISTS vox_celeris;
CREATE DATABASE vox_celeris;
USE vox_celeris;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL,
    passhash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    dob DATE,
    profile_photo VARCHAR(255),
    bio TEXT,
    location VARCHAR(100),
    private_account BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    active_account BOOLEAN DEFAULT 0,
);

CREATE TABLE registration_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    token_expires DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE forgot_password_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    token_expires DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE followers (
    user_id INT NOT NULL,
    follower_id INT NOT NULL,
    PRIMARY KEY (user_id, follower_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE blocked_followers (
    user_id INT NOT NULL,
    blocked_user_id INT NOT NULL,
    PRIMARY KEY (user_id, blocked_user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE follow_requests (
    user_id INT NOT NULL,
    follower_id INT NOT NULL,
    PRIMARY KEY (user_id, follower_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    photo_url VARCHAR(255) NOT NULL,
    photo_caption TEXT,
    photo_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_text TEXT,
    post_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE post_photos (
    post_id INT NOT NULL,
    photo_id INT NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE
);

CREATE TABLE hashtags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hashtag VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE post_hashtags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    hashtag_id INT NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (hashtag_id) REFERENCES hashtags(id) ON DELETE CASCADE
);

/*
    -----------
    SQL Queries
    -----------

    The could be turned into stored procedures or views, but for now they are just SQL queries.

    Lists a users followers
    ========================

        SELECT 
            users.id, 
            users.username, 
            users.profile_photo,
            users.location,
            users.bio
        FROM followers
        JOIN users ON followers.follower_id = users.id
        WHERE followers.user_id = <CURRENT_USER_ID>



    Lists followers of a user
    =========================

        SELECT 
            users.id, 
            users.username, 
            users.profile_photo,
            users.location,
            users.bio,
            CASE WHEN mutual.user_id IS NOT NULL THEN 1 ELSE 0 END AS follows_back
        FROM followers
        JOIN users ON followers.user_id = users.id
        LEFT JOIN followers AS mutual 
            ON mutual.user_id = <CURRENT_USER_ID>
            AND mutual.follower_id = users.id
        WHERE followers.follower_id = <CURRENT_USER_ID>



    Generates a list of posts from users that a user is following
    =============================================================

        SELECT 
            posts.id AS post_id,
            posts.user_id AS post_user_id,
            posts.post_text,
            posts.post_date,
            users.username,
            users.profile_photo
        FROM posts
        JOIN followers 
            ON posts.user_id = followers.follower_id
        JOIN users 
            ON posts.user_id = users.id
        WHERE followers.user_id = <CURRENT_USER_ID>
        ORDER BY posts.post_date DESC;

*/