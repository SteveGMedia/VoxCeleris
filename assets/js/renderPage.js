/*

    renderPage.js
    -------------

    This javascript file is responsible for handling state management and rendering
    the page based on the state of the application.

    It also handles the API calls to the server to retrieve data and update the page.

    Author: SteveGMedia
*/


/* ============================================================
    
                    Page Rendering Functions

============================================================ */

/* ------------------ Tooltip Rendering ------------------ */
function renderTooltips(){
    
    /* Create the tooltip element */
    const tooltip = document.createElement('div');
    tooltip.id = 'tooltip';
    tooltip.className = 'hidden absolute px-2 py-1 text-xs leading-none text-white whitespace-no-wrap bg-black shadow-lg rounded-md z-50';

    /* Check if tooltip already exists */
    if (document.getElementById('tooltip')){
        document.getElementById('tooltip').remove();
    }

    document.body.appendChild(tooltip);
  
    /* Show tooltip */
    const showTooltip = (e) => {
      const target = e.currentTarget;
      tooltip.textContent = target.getAttribute('data-tooltip');
      tooltip.classList.remove('hidden');
      
      /* Position the tooltip */
      const rect = target.getBoundingClientRect();
      tooltip.style.top = `${rect.bottom + window.scrollY}px`;
      tooltip.style.left = `${rect.left + window.scrollX + rect.width / 2 - tooltip.offsetWidth / 2}px`;
    };
  
    /* Hide tooltip */
    const hideTooltip = () => {
      tooltip.classList.add('hidden');
    };
  
    /* 
        Add event listeners to all elements with the tooltip class 
        Sometimes, depending on how things are rendered, tooltips may not work as expected.
        It can be quirky :/
    */
    const tooltipElements = document.querySelectorAll('.tooltip');

    tooltipElements.forEach(el => {
      el.addEventListener('mouseenter', showTooltip);
      el.addEventListener('mouseleave', hideTooltip);
      el.addEventListener('mousemove', showTooltip);
      el.addEventListener('click', hideTooltip);
    });
}

/* ------------------ Navigation Rendering ------------------ */
function renderNavigation() {
    let navigation = document.getElementById('user-nav');

    navigation.innerHTML = '';
    navigation.innerHTML = `
            <div class="flex flex-wrap justify-center">
                <a id="home" class="tooltip mx-2" data-tooltip="My Feed" href="#"><i class="fas fa-home fa-3x text-pink-800 hover:text-red-800"></i></a>
                <a id='nav-editprofile' class="tooltip mx-2" data-tooltip="My Account" href="#"><i class="fas fa-user fa-3x text-pink-800 hover:text-red-800"></i></a>
                <a id='nav-gallery' class="tooltip mx-2" data-tooltip="My Photos" href="#"><i class="fas fa-images fa-3x text-pink-800 hover:text-red-800"></i></a>
             </div>

            <!-- Vertical Divider -->
            <div class="mx-2 h-10 w-px bg-[#751515] hidden sm:block"></div>

            <div class="flex flex-wrap justify-center">
                <a id='nav-followers' class="tooltip mx-2" data-tooltip="Followers" href="#"><i class="fas fa-user-friends fa-3x text-pink-800 hover:text-red-800"></i></a>
                <a id='nav-following' class="tooltip mx-2" data-tooltip="Following" href="#"><i class="fas fa-user-check fa-3x text-pink-800 hover:text-red-800"></i></a>
                <a id='nav-people' class="tooltip mx-2" data-tooltip="Find People" href="#"><i class="fas fa-search fa-3x text-pink-800 hover:text-red-800"></i></a>
                <a id='nav-messages' class="tooltip mx-2" data-tooltip="Direct Messages" href="#"><i class="fas fa-envelope fa-3x text-pink-800 hover:text-red-800"></i></a>
                <a class="tooltip mx-2" data-tooltip="Logout" href="logout.php"><i class="fas fa-sign-out-alt fa-3x text-pink-800 hover:text-red-800"></i></a>
            </div>`;

    // Add event listeners to the navigation links
    document.getElementById('home').addEventListener('click', () => loadPageContent('posts'));              /* User Feed / Posts Feed */
    document.getElementById('nav-people').addEventListener('click', () => loadPageContent('people'));       /* Find people */
    document.getElementById('nav-followers').addEventListener('click', () => loadPageContent('followers')); /* Who is following the user */
    document.getElementById('nav-following').addEventListener('click', () => loadPageContent('following')); /* Who the user is following */
    document.getElementById('nav-messages').addEventListener('click', () => loadPageContent('messages'));   /* Direct Messages */
    document.getElementById('nav-editprofile').addEventListener('click', () => loadPageContent('editprofile')); /* Edit User Profile */
    document.getElementById('nav-gallery').addEventListener('click', () => loadPageContent('gallery')); /* User Photo Gallery */
}

/* ------------------ PAGE Following ------------------ */
function renderFollowing(response){
    let content = document.getElementById('content-container');
    let following_html = '';
    let following = response || [];

    /* Only Render if there are followers */
    if(!response.error){
        following.forEach(following => {
            following_html += `
                    <div class="flex items-center justify-between bg-pink-800 p-4 rounded-lg mb-4" data-username="${following.username}">
                        <div class="flex items-center">
                            <img src="${following.profile_photo}" alt="Follower Avatar" class="w-12 h-12 rounded-full mr-4">
                            <div>
                                <p class="font-semibold">${following.username}</p>
                                <p class="text-sm text-gray-300">${following.bio}</p>
                            </div>
                        </div>

                        <div id='usr-actions' class="flex space-x-4">
                            <div id='follow-action'>
                                <a id="btn-unfollow" onClick="unfollowPerson('${following.username}')" class="tooltip mx-2" data-tooltip="Unfollow"><i class="fas fa-minus-square fa-3x text-red-800 hover:text-red-800"></i></a>
                            </div>
                            <div id='block-action'>
                                <a class="tooltip mx-2" data-tooltip="Block"><i class="fas fa-ban fa-3x text-red-800 hover:text-red-800"></i></a>
                            </div>
                        </div>
                    </div>`;
        }); 
    }

    /* Renders Regardless */
    content.innerHTML = '';
    content.innerHTML = `
            <h2 class="text-xl md:text-2xl lg:text-4xl font-semibold text-red-800 text-shadow mb-4">Following</h2>

            <!-- Search Bar -->
            <div class="mb-4 w-full max-w-md">
                <input type="text" placeholder="Search followers..." class="w-full bg-pink-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-pink-600">
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="hidden mt-4 p-2 bg-black bg-opacity-25 text-white text-center rounded-lg"></div>


            <!-- Followers List -->
            <div id="followersList" class="w-full"></div>`;

    document.getElementById('followersList').innerHTML = following_html;

    /* If there is an error, display it */
    if (response.error){
        // Affix error message
        document.getElementById('errorMessage').classList.remove('hidden');
        document.getElementById('errorMessage').innerText = "No following to display";
    }

    renderTooltips();
}


/* ------------------ PAGE Followers ------------------ */
function renderFollowers(response){
    let content = document.getElementById('content-container');
    let followers_html = '';
    let followers = response || [];

    /* Only Render if there are followers */
    if(!response.error){
        followers.forEach(follower => {

            let follow_unfollow = '';

            if (follower.follows_back === 1){
                follow_unfollow = `<a id="btn-unfollow" onClick="unfollowPerson('${follower.username}')" class="tooltip mx-2" data-tooltip="Unfollow"><i class="fas fa-minus-square fa-3x text-red-400 hover:text-red-600"></i></a>`;
            }else{
                follow_unfollow = `<a id="btn-follow" onClick="followPerson('${follower.username}')" class="tooltip mx-2" data-tooltip="Follow"><i class="fas fa-plus-square fa-3x text-green-500 hover:text-green-800"></i></a>`;
            }

            followers_html += `
                    <div class="flex items-center justify-between bg-pink-800 p-4 rounded-lg mb-4" data-username="${follower.username}">
                        <div class="flex items-center">
                            <img src="${follower.profile_photo}" alt="Follower Avatar" class="w-12 h-12 rounded-full mr-4">
                            <div>
                                <p class="font-semibold">${follower.username}</p>
                                <p class="text-sm text-gray-300">${follower.bio}</p>
                            </div>
                        </div>
                        

                        <div id="usr-actions" class="flex space-x-4">
                            <div id='follow-action'>
                                ${follow_unfollow}
                            </div>
                            <div id='block-action'>
                                <a class="tooltip mx-2" data-tooltip="Block"><i class="fas fa-ban fa-3x text-red-800 hover:text-red-800"></i></a>
                            </div>
                        </div>

                    </div>`;
        });
    }

    /* Renders Regardless */
    content.innerHTML = '';
    content.innerHTML = `
            <h2 class="text-xl md:text-2xl lg:text-4xl font-semibold text-red-800 text-shadow mb-4">Followers</h2>

            <!-- Search Bar -->
            <div class="mb-4 w-full max-w-md">
                <input type="text" placeholder="Search followers..." class="w-full bg-pink-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-pink-600">
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="hidden mt-4 p-2 bg-black bg-opacity-25 text-white text-center rounded-lg"></div>
            <!-- Followers List -->
            <div id="followersList" class="w-full"></div>`;

    document.getElementById('followersList').innerHTML = followers_html;

    if (response.error){
        document.getElementById('errorMessage').classList.remove('hidden');
        document.getElementById('errorMessage').innerText = "No followers to display";
    }

    renderTooltips();
}

/* ------------------ Follow/Unfollow Toggle ------------------ */
function follow_toggle(username){
    
    let userDiv = document.querySelector(`div[data-username="${username}"]`);

    let usrActions = userDiv.querySelector('#usr-actions');
    let followAction = usrActions.querySelector('#follow-action');

    let unfollow_html = `<a id="btn-unfollow" onClick="unfollowPerson('${username}')" class="tooltip mx-2" data-tooltip="Unfollow"><i class="fas fa-minus-square fa-3x text-red-400 hover:text-red-600"></i></a>`;
    let follow_html = `<a id="btn-follow" onClick="followPerson('${username}')" class="tooltip mx-2" data-tooltip="Follow"><i class="fas fa-plus-square fa-3x text-green-500 hover:text-green-800"></i></a>`;
    
    
    if (followAction.querySelector('#btn-follow')){
        followAction.innerHTML = unfollow_html;
    }else{
        followAction.innerHTML = follow_html;
    }

    renderTooltips();
}


    

/* ------------------ PAGE People ------------------ */
function renderPeople(response){
    let content = document.getElementById('content-container');
    let people_html = '';
    let people = response || [];

                       
    people.forEach(person => {

        let follow_unfollow = '';

        if (person.is_following === 1){
            follow_unfollow = `<a id="btn-unfollow" onClick="unfollowPerson('${person.username}')" class="tooltip mx-2" data-tooltip="Unfollow"><i class="fas fa-minus-square fa-3x text-red-400 hover:text-red-600"></i></a>`;
        }else{
            follow_unfollow = `<a id="btn-follow" onClick="followPerson('${person.username}')" class="tooltip mx-2" data-tooltip="Follow"><i class="fas fa-plus-square fa-3x text-green-500 hover:text-green-800"></i></a>`;
        }

        people_html += `
                <div class="flex items-center justify-between bg-pink-800 p-4 rounded-lg mb-4" data-username="${person.username}">
                    <div class="flex items-center">
                        <img src="${person.profile_photo}" alt="Follower Avatar" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <p class="font-semibold">${person.username}</p>
                            <p class="text-sm text-gray-300">${person.bio}</p>
                        </div>
                    </div>

                    <div id='usr-actions' class="flex space-x-4">
                        <div id='follow-action'>
                            ${follow_unfollow}
                        </div>
                        <div id='block-action'>
                            <a class="tooltip mx-2" data-tooltip="Block"><i class="fas fa-ban fa-3x text-red-800 hover:text-red-800"></i></a>
                        </div>
                    </div>
                </div>`;
    });

    content.innerHTML = '';
    content.innerHTML = `
            <h2 class="text-xl md:text-2xl lg:text-4xl font-semibold text-red-800 text-shadow mb-4">Public Accounts</h2>

            <!-- Search Bar -->
            <div class="mb-4 w-full max-w-md">
                <input type="text" placeholder="Search followers..." class="w-full bg-pink-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-pink-600">
            </div>

            <!-- Followers List -->
            <div id="followersList" class="w-full"></div>`;

    if (people.length === 0){
        // Affix error message
        document.getElementById('errorMessage').classList.remove('hidden');
        document.getElementById('errorMessage').innerText = "No people to display";
    }else{
        document.getElementById('followersList').innerHTML = people_html;

    }


    renderTooltips();
}

/* ------------------ PAGE User Profile ------------------ */
/* ------------------ NOT IMPLEMENTED ------------------ */
function renderEditUserProfile(response){
    let content = document.getElementById('content-container');
    let profile_html = '';
    let profile = response || [];

    content.innerHTML = '';
    content.innerHTML = `
            <h2 class="text-xl md:text-2xl lg:text-4xl font-semibold text-red-800 text-shadow mb-4">Edit Profile (Not Implemented)</h2>

            <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="w-full">
                <!-- Profile Photo Input -->
                <div class="mb-4 text-center">
                    <label for="profilePhoto" class="block text-sm font-bold mb-2">Profile Photo</label>
                    <input type="file" id="profilePhoto" name="profilePhoto" accept="image/*" class="bg-pink-800 text-white font-bold py-2 px-4 rounded">
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

                <!-- Email Input -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-bold mb-2">Email</label>
                    <input type="email" id="email" name="email" class="bg-pink-800 focus:ring-2 focus:ring-pink-500 text-white font-bold py-2 px-4 rounded w-full" placeholder="Enter your email" required>
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
                        Save Changes
                    </button>
                </div>
            </form>

            <!-- Success/Error Message -->
            <div id="error" class="hidden mt-4 p-2 bg-black bg-opacity-25 text-white text-center rounded-lg"></div>`;

    renderTooltips();

}


/* ------------------ PAGE Direct Messages ------------------ */
/* ------------------ NOT IMPLEMENTED ------------------ */
function renderDirectMessages(response){
    let content = document.getElementById('content-container');
    let messages_html = '';
    let messages = response || [];

    content.innerHTML = '';
    content.innerHTML = `            
            <h2 class="text-xl md:text-2xl lg:text-4xl font-semibold text-red-800 text-shadow mb-4">Direct Messages (Not Implemented)</h2>
            <div id='messages-frame' class="flex flex-col lg:flex-row mt-8 mb-4 bg-black bg-opacity-50 rounded-lg p-4 max-w-5xl w-full">

                <!-- Threads List -->
                <div class="lg:w-1/3 lg:pr-4 mb-4 lg:mb-0 flex flex-col">


                    <!-- Effectively this is a list of users -->
                    <div class="bg-pink-800 p-4 rounded-lg flex-1 overflow-y-auto">

                        <!-- Conversation 1 -->
                        <a href="#thread1" class="block mb-4 p-4 bg-pink-700 rounded-lg hover:bg-pink-600">
                            <div class="flex items-center">
                                <img src="https://placehold.co/50x50" alt="User Avatar" class="w-12 h-12 rounded-full mr-4">
                                <div>
                                    <p class="font-semibold">User Name</p>
                                    <p class="text-sm text-gray-300">Last message preview...</p>
                                </div>
                            </div>
                        </a>

                        <!-- Conversation 2 -->
                        <a href="#thread1" class="block mb-4 p-4 bg-pink-700 rounded-lg hover:bg-pink-600">
                            <div class="flex items-center">
                                <img src="https://placehold.co/50x50" alt="User Avatar" class="w-12 h-12 rounded-full mr-4">
                                <div>
                                    <p class="font-semibold">User Name</p>
                                    <p class="text-sm text-gray-300">Last message preview...</p>
                                </div>
                            </div>
                        </a>

                        <!-- Conversation 3 -->
                        <a href="#thread1" class="block mb-4 p-4 bg-pink-700 rounded-lg hover:bg-pink-600">
                            <div class="flex items-center">
                                <img src="https://placehold.co/50x50" alt="User Avatar" class="w-12 h-12 rounded-full mr-4">
                                <div>
                                    <p class="font-semibold">User Name</p>
                                    <p class="text-sm text-gray-300">Last message preview...</p>
                                </div>
                            </div>
                        </a>
                        <!-- END THREAD -->
                    </div>
                </div>

                <!-- Message Area -->
                <div class="lg:w-2/3 flex flex-col">
                    <div id="thread1" class="bg-pink-800 p-4 rounded-lg flex-1 overflow-y-auto">
                        <!-- Message Item -->
                        <div class="flex mb-4">
                            <img src="https://placehold.co/50x50" alt="Sender Avatar" class="w-10 h-10 rounded-full mr-3">
                            <div class="bg-pink-700 p-3 rounded-lg text-white">
                                <p class="font-semibold">SteveGMedia</p>
                                <p>Hello World!</p>
                            </div>
                        </div>
                        <!-- Repeat the above block for each message -->
                    </div>
                    <!-- Send Message -->
                    <div class="mt-4">
                        <textarea id="newMessage" class="bg-pink-700 text-white font-bold py-2 px-4 rounded w-full" placeholder="Type your message..."></textarea>
                        <button id="sendMessage" class="bg-pink-600 hover:bg-pink-500 text-white font-bold py-2 px-4 rounded mt-2">
                            Send
                        </button>
                    </div>
                </div>
            </div`;

    renderTooltips();
}

/* ------------------ PAGE Gallery ------------------ */
function renderGallery(response){
    let content = document.getElementById('content-container');
    let gallery_html = '';
    let gallery = response || [];

    // photo_url, photo_caption, photo_date

    gallery.forEach(photo => {
        gallery_html += `
                <div class="relative group">
                    <img src="${photo.photo_url}" alt="${photo.photo_caption}" class="w-full h-full object-cover rounded-lg">
                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <p class="text-white text-lg">${photo.photo_caption}</p>
                    </div>

                    <a href="${photo.photo_url}" data-tooltip="View" class="tooltip absolute top-2 right-10 text-white text-lg group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="${photo.photo_url}" data-tooltip="Download" download="${photo.photo_caption}" class="tooltip absolute top-2 right-2 text-white text-lg group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-download"></i>
                    </a>
                </div>`;
    });

    content.innerHTML = '';
    content.innerHTML = `
                <h2 class="text-xl md:text-2xl lg:text-4xl font-semibold text-red-800 text-shadow mb-4">Photo Gallery</h2>
                <!-- Error Message -->
                 <div id="errorMessage" class="hidden mt-4 p-2 bg-black bg-opacity-25 text-white text-center rounded-lg"></div>

                <div id='gallery-container' class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 w-full">
                    <!-- Photo Items -->
                </div>
            `;


    if (gallery.length === 0){
        // Affix error message
        document.getElementById('errorMessage').classList.remove('hidden');
        document.getElementById('errorMessage').innerText = "No photos to display";
    }else{
        document.getElementById('gallery-container').innerHTML = gallery_html;
    }

    renderTooltips();
}


/* ------------------ PAGE Posts / Home ------------------ */
function renderPosts(response){
    let content = document.getElementById('content-container');
    let post_html = '';
    let posts = response.posts || [];
    let username = response.username || '';
    let profile_photo = response.profile_photo || '';


    let attachedImages = [];

    posts.forEach(post => {
        post_html += `
            <div class="bg-pink-800 bg-opacity-50 p-4 rounded-lg mt-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <img src="${post.profile_photo}" alt="User Profile" class="w-12 h-12 rounded-full">
                        <h3 class="text-lg font-semibold text-white text-shadow">${post.username}</h3>
                    </div> 
                    <p class="text-sm text-white text-shadow font-bold">${post.post_date}</p>
                </div>
                <p class="text-white text-shadow">${post.post_text}</p>
                
            ${post.photos ? `<div class="flex flex-wrap bg-neutral-950 bg-opacity-60 gap-2 mt-2">
                ${post.photos.map(photo => `
                    <div class="relative group">
                        <img src="${photo.photo_url}" alt="${photo.photo_caption}" class="w-48 h-48 object-cover rounded-lg">
                        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"></div>

                        <a href="${photo.photo_url}" data-tooltip="View" class="tooltip absolute top-2 right-10 text-white text-lg group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="${photo.photo_url}" data-tooltip="Download" download="${photo.photo_caption}" class="tooltip absolute top-2 right-2 text-white text-lg group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                `).join('')}
            </div>` : ''}
            </div>`;
    });

    content.innerHTML = '';
    content.innerHTML = `
            <h2 class="text-xl md:text-2xl lg:text-4xl font-semibold text-red-800 text-shadow">@${username}</h2>
            <!-- Options Inputs -->
            <div class="flex flex-col sm:flex-row sm:flex-wrap justify-center items-center space-y-4 sm:space-y-0 sm:space-x-7 w-full mt-4">
                <img src="${profile_photo}" alt="User Profile" class="w-16 h-16 rounded-full">

                <textarea id="postText" class="bg-pink-800 focus:accent-pink-500 text-white font-bold py-2 px-4 rounded mt-2 sm:mt-0 sm:w-full md:flex-1" placeholder="Write your post here..."></textarea>

                <div class="flex space-x-4">
                    <!-- Attach Image -->
                    <label class="tooltip cursor-pointer" data-tooltip="Attach Image">
                        <i class="fas fa-paperclip fa-2x text-pink-800 hover:text-red-800"></i>
                        <input type="file" id="attachImage" accept="image/*" class="hidden" multiple>
                    </label>

                    <!-- Share Post Button -->
                    <button id="sharePost" class="tooltip cursor-pointer" data-tooltip="Share a post">
                        <i class="fas fa-file-import fa-2x text-pink-800 hover:text-red-800"></i>
                    </button>
                </div>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="hidden mt-4 p-2 bg-black bg-opacity-25 text-white text-center rounded-lg"></div>

            <!-- Preview Attached Images -->
            <div id="imagePreviewContainer" class="flex flex-wrap mt-4 gap-2"></div>

            <!-- User Feed Posts -->
            <div id="postContainer" class="sm:w-full md:w-5/6 mt-10 gap-4">
                <!-- User posts will be in here -->
            </div>`;

            if (posts.length === 0){
                // Affix error message
                document.getElementById('errorMessage').classList.remove('hidden');
                document.getElementById('errorMessage').innerText = "No posts to display";
            }else{
                document.getElementById('postContainer').innerHTML = post_html;
            }

        /* 
            Event listener for the remove buttons for images and the image preview container 
        */
        document.getElementById('attachImage').addEventListener('change', (event) => {
            const files = event.target.files;
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            imagePreviewContainer.innerHTML = '';
    
            attachedImages = [];
    
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
    
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('w-20', 'h-20', 'object-cover', 'rounded-lg');

                    /* Create a remove button for each image */
                    const removeButton = document.createElement('a');
                    removeButton.innerHTML = `<i class="fas fa-times fa-3x text-pink-800 hover:text-red-800"></i>`;
                    removeButton.classList.add('tooltip', 'mx-2');
                    removeButton.setAttribute('data-tooltip', 'Remove Image');


                    /* Event listener for the remove button */
                    removeButton.addEventListener('click', () => {
                        attachedImages.splice(i, 1);
                        img.remove();
                        removeButton.remove();
                    });
    
                    /* Append the image and remove button to the image preview container */
                    imagePreviewContainer.appendChild(img);
                    imagePreviewContainer.appendChild(removeButton);
                    attachedImages.push(file);

                    renderTooltips(); /* This really shouldn't be called multiple times like this, but it works for now */
                };
    
                reader.readAsDataURL(file);
            }

        });

    
    // Add event listeners to the buttons
    document.getElementById('sharePost').addEventListener('click', () => {
        let postText = document.getElementById('postText').value;
        makePost(postText, attachedImages);
    });

    renderTooltips();
}



/* ============================================================
    
     Page Action Functions (API Calls with JSON responses)

============================================================ */

/* ======== Creates User Post ======== */
function makePost(postText, images) {

    /* 
        Since we're deling with multiform data, but primarily use JSON 
        for the rest of the application, we'll use a FormData object 
        to handle the image uploads.

        This way we can send the JSON payload and the images in one request.
    */
    let jsonPayload = {
        endpoint: 'makepost',
        message: postText
    };


    let formData = new FormData();

    formData.append('jsonPayload', JSON.stringify(jsonPayload));

    images.forEach((image, index) => {
        formData.append(`image${index}`, image);
    });

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.error === false) {
                loadPageContent('posts');
            }else{
                document.getElementById('errorMessage').classList.remove('hidden');
                document.getElementById('errorMessage').innerText = data.message;
            }
        })
        .catch(error => {
            console.error(error);
        });
}

/* ======== Follows a User ======== */
function followPerson(username) {
    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            endpoint: 'follow',
            username: username
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.error === false) {
                let currentPage = sessionStorage.getItem('currentPage'); 

                follow_toggle(username);
                //loadPageContent(currentPage); 
            }
        })
        .catch(error => {
            console.error(error);
        });
}

/* ======== Unfollows a User ======== */
function unfollowPerson(username) {
    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            endpoint: 'unfollow',
            username: username
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.error === false) {
                let currentPage = sessionStorage.getItem('currentPage');

                /* Hide/Show the follow/unfollow buttons */
                follow_toggle(username);
            }
        })
        .catch(error => {
            console.error(error);
        });
}


/* ======== Page Routing ======== */
function loadPageContent(page) {
    /* When a user navigates to a new page, we store the page in the session storage in case of refresh */
    sessionStorage.setItem('currentPage', page);
    console.log("Loading page content for: " + page);

    /* Not implemented, so we'll just display the template */
    if (page === 'messages'){
        renderDirectMessages(); 
        return;
    }

    /* Also not implemented, again, just display the template */
    if (page === 'editprofile'){
        renderEditUserProfile();
        return;
    }

    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            endpoint: page
        })
    })
        .then(response => response.json())
        .then(data => {

            switch(page) {
                case 'posts':
                    renderPosts(data);
                    break;
                case 'people':
                    renderPeople(data);
                    break;
                case 'followers':
                    renderFollowers(data);
                    break;
                case 'following':
                    renderFollowing(data);
                    break;
                case 'gallery':
                    renderGallery(data);
                    break;
                default:
                    renderPosts(data);
            console.log(data);
            }
        })
        .catch(error => {
            console.error(error);
        });

}

/* ======== Page Initialization ======== */
document.addEventListener('DOMContentLoaded', function() {

    /* Map the navigation links to their respective functions */
    document.getElementById('nav-home').addEventListener('click', () => loadPageContent('posts'));

    const page = sessionStorage.getItem('currentPage') || 'posts'; /* Default to posts page */
    renderNavigation();
    loadPageContent(page);
});