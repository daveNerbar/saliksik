<?php
session_start();
if (!isset($_SESSION['pupid'])) {
    header("Location: facultyogin.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | SALIKSIK</title>

    <link rel="stylesheet" href="headerstyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/19d37dc8d9.js" crossorigin="anonymous"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>


    <style>
        :root {
            --bg1: #550000;
            /* Maroon */
            --bg2: #2c0000;
            --yellow: #ffd200;
            --white: #ffffff;
            --text-dark: #333;
            --card-gray: #dcdcdc;
            /* The gray background from your image */
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: #fff;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* =========================================
           NAVBAR CSS
           ========================================= */



        /* =========================================
           MAIN LAYOUT (Split View)
           ========================================= */
        .page-title {
            text-align: center;
            font-weight: 900;
            font-size: 32px;
            margin-top: 40px;
            text-transform: uppercase;
        }

        .main-container {
            display: flex;
            justify-content: center;
            gap: 40px;
            padding: 20px 5%;
            margin-bottom: 60px;
            flex-wrap: wrap;
            /* Allows stacking on mobile */
        }

        /* --- LEFT COLUMN: Message Box --- */
        .message-box {
            background-color: var(--card-gray);
            border-radius: 10px;
            padding: 40px;
            flex: 2;
            /* Takes up more space */
            min-width: 300px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            line-height: 1.6;
        }

        .message-box h3 {
            margin-top: 0;
            font-size: 16px;
        }

        .checklist {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        .checklist li {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .checklist i {
            color: #00c853;
            /* Green check color */
            font-size: 16px;
        }

        /* --- RIGHT COLUMN: Librarian Card --- */
        .librarian-card {
            flex: 1;
            min-width: 280px;
            max-width: 350px;
            background-color: #800000;
            /* Dark Red */
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            height: fit-content;
        }

        .inner-white-box {
            background-color: white;
            border-radius: 10px;
            width: 100%;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 250px;
            /* Adjust based on image aspect ratio */
        }

        .librarian-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .name-badge {
            background-color: #ffcccc;
            /* Pinkish pill color */
            color: #000;
            padding: 8px 30px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
            margin-top: 15px;
            display: inline-block;
            width: 90%;
        }

        .role-title {
            color: white;
            font-weight: 600;
            font-size: 12px;
            margin-top: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        /* =========================================
           FOOTER (Library Hours)
           ========================================= */
        .library-footer {
            background-color: #800000;
            color: white;
            padding: 40px 20px;
            margin-top: auto;
            /* Pushes footer to bottom */
            text-align: center;
        }

        .hours-title-box {
            border: 2px solid #3b5bdb;
            /* The Blue border from image */
            display: inline-block;
            padding: 5px 20px;
            font-weight: 900;
            font-size: 18px;
            text-transform: uppercase;
            margin-bottom: 5px;
            box-shadow: 0 0 10px rgba(59, 91, 219, 0.3);
            /* Slight glow */
        }

        .campus-name {
            font-size: 12px;
            margin-bottom: 30px;
            display: block;
        }

        .schedule-container {
            display: flex;
            justify-content: center;
            gap: 80px;
            flex-wrap: wrap;
        }

        .schedule-item h4 {
            margin: 0 0 10px 0;
            font-weight: 800;
            font-size: 16px;
            text-transform: uppercase;
        }

        .schedule-item p {
            margin: 0;
            font-weight: 600;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <header class="navbar">
        <div class="nav-left">
            <a href="facultyhome.php"><img src="puplogo.png" alt="PUP Logo" /></a>
            <div class="brand-title">
                <p class="univ">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</p>
                <div class="saliksik">SALIKSIK</div>
            </div>
        </div>
        <i class="fa-solid fa-bars" id="menu-icon"></i>
        <nav class="nav-right" id="nav-menu">
            <i class="fa-solid fa-xmark" id="close-icon"></i>

            <a href="facultyhome.php">home</a>

            <div class="nav-item-dropdown">
                <a href="facultyhome.php#about">About Us <i class="fa-solid fa-angle-down"></i></a>
                <div class="dropdown-content">
                    <a href="facultyrules.php"><i class="fa-solid fa-scale-balanced"></i> Rules</a>
                    <a href="facultymessages.php"><i class="fa-solid fa-envelope"></i> Messages</a>
                </div>
            </div>

            <a href="facultyviewbooks.php">Books</a>
            <a href="facultyhome.php#contact">Contact us</a>

            <div class="sign-out">
                <div class="profile-menu">
                    <i class="fa-solid fa-circle-user fa-lg" style="color: #ffffff;"></i>
                    <div class="dropdown">
                        <a href="facultyprofile.php"><i class="fa-solid fa-user" style="color: #550000;"></i>Profile</a>
                        <a href="facultylogout.php"><i class="fa-solid fa-right-from-bracket" style="color: #550000;"></i>Sign Out</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <h1 class="page-title">MESSAGE</h1>

    <div class="main-container">

        <div class="message-box">
            <p style="margin-top:0;">Welcome to SALIKSIK – Now Available for Students and Faculty</p>

            <h3>Dear SALIKSIK Developers,</h3>

            <p>
                On behalf of the PUP Parañaque Campus Library, thank you for developing SALIKSIK: The Digital Research Platform.
            </p>
            <p>
                Your work has greatly improved our services by enabling students and faculty to browse, borrow, and access academic resources online. We sincerely appreciate your contribution to modernizing our library and supporting scholarly research.
            </p>

            <br>

            <h3>To Our Dear PUPian Students and Faculty:</h3>
            <p>
                We are excited to announce that SALIKSIK is now officially available for your use at the PUP Parañaque Campus Library.
            </p>
            <p>With SALIKSIK, you can:</p>

            <ul class="checklist">
                <li><i class="fa-solid fa-square-check"></i> Search and reserve books digitally</li>
                <li><i class="fa-solid fa-square-check"></i> Access research materials anytime</li>
                <li><i class="fa-solid fa-square-check"></i> Submit book reports and academic works</li>
            </ul>

            <p>Please visit the library terminals or log in directly using your PUP account to explore the platform.</p>

            <br>

            <p>
                For questions or assistance, our librarians are always here to help!<br>
                Together, let’s build a smarter and more accessible research environment.
            </p>

            <p style="margin-bottom: 0;">Warm regards,</p>
        </div>

        <div class="chat-wrapper">
            <button class="chat-toggle-btn" onclick="toggleChat()">
                <img src="chatbot.png" alt="Chat with SalikTech">
            </button>

            <div class="chat-container" id="chatContainer">
                <div class="chat-header">
                    <div class="header-title">
                        <h3>SalikTech Assistant</h3>
                        <span>Faculty Support Online</span>
                    </div>
                    <button onclick="toggleChat()" style="background:none;border:none;color:white;cursor:pointer;">
                        <iconify-icon icon="mdi:close" width="20"></iconify-icon>
                    </button>
                </div>

                <div class="chat-body" id="chatBody">
                    <div class="message bot">
                        <p>Good day, Professor. How can I assist you with the library system today?</p>
                    </div>

                    <div class="options-container">
                        <button class="option-btn" onclick="handleOption('Search for books')">Search for books</button>
                        <button class="option-btn" onclick="handleOption('My Account Status')">My Account Status</button>
                        <button class="option-btn" onclick="handleOption('Upcoming Events')">Upcoming Events</button>
                    </div>
                </div>

                <div class="chat-footer">
                    <input type="text" id="userInput" placeholder="Ask about books, events..." onkeypress="handleEnter(event)">
                    <button class="send-btn" onclick="sendMessage()">
                        <iconify-icon icon="mdi:send"></iconify-icon>
                    </button>
                </div>
            </div>
        </div>
        <script src="chatbotfaculty.js"></script>



    </div>

    <footer class="library-footer">
        <div class="hours-title-box">LIBRARY HOURS</div>
        <span class="campus-name">PUP Parañaque Campus</span>

        <div class="schedule-container">
            <div class="schedule-item">
                <h4>Monday – Friday</h4>
                <p>8:00 AM – 7:00 PM</p>
            </div>
            <div class="schedule-item">
                <h4>Saturday</h4>
                <p>8:00 AM – 5:00 PM</p>
            </div>
        </div>
    </footer>

    <script>
        const menuIcon = document.getElementById("menu-icon");
        const closeIcon = document.getElementById("close-icon");
        const navMenu = document.getElementById("nav-menu");

        if (menuIcon) {
            menuIcon.addEventListener("click", () => {
                navMenu.classList.add("active");
            });
        }
        if (closeIcon) {
            closeIcon.addEventListener("click", () => {
                navMenu.classList.remove("active");
            });
        }
    </script>
</body>

</html>