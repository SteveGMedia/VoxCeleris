# Vox Celeris

A simple social media network project written in PHP, inspired by Twitter/X. This was my final project for a college class.

I worked on this project intermittently over the course of about 10 days, with the majority of the work completed during a weekend hackathon in a last-minute panic. In hindsight, some of the code could be structured more effectively, but I’m sharing it on GitHub in the hopes that someone might find it useful.

## Features

- **Single-page application**: All pages load dynamically with JavaScript, preventing full page reloads (with a few exceptions, namely the pages that handle registration and auth).
- **Registration**: Users can create a new account, upload a profile photo. Your standard social media registration stuff.
- **Account verification**: A token-based verification system to activate accounts via email.
- **User interactions**: Users can follow/unfollow others and see who they’re following or being followed by.
- **Image Sharing / Gallery**: Users can upload (multiple) photos to posts, and view them in their gallery page.

## Setup

To get started, follow these steps:

1. Modify the configuration in `config.php` to suit your environment.
2. Import the `vox_celeris.sql` file into your MySQL database.
3. Run the project and have fun!

## Modifying the Code

Since the code could be better organized, I won’t go into too much detail. The main files to modify are:

- **API endpoints**: `api.php`
- **Page rendering**: `assets/js/renderPage.js`

### Adding an API Endpoint

1. Open `api.php`.
2. Add a new endpoint to the `switch` statement and create a corresponding function.

Example:

```php
case "posts":
    API_userGetPosts();
    break;
```

I tried to follow a naming convention like API_userGetPosts() for the function names.

3. In `assets/js/renderPage.js`, modify **the loadPageContent()** function to include a route for your new endpoint.
4. If you need to add a page or render content, review the existing render functions for examples, as they follow a similar structure.

## Final Words
I know this project isn’t perfect, but it served as a good PHP refresher for me. If you find it helpful, feel free to modify it as you see fit!
