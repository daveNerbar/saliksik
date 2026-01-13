<?php
session_start();

if (!isset($_SESSION['studentnumber'])) {
    header("Location: studentlogin.php");
    exit();
}
// Optional: If you need database access here later, include your connection
// include 'db.php'; 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rules and Regulations | SALIKSIK</title>
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
            --paper-white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: #f0f0f0;
            /* Light gray to make paper pop */
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            /* Reset padding for navbar */
            color: var(--text-dark);
        }

        /* =========================================
           NAVBAR CSS (From Home Page)
           ========================================= */


        /* =========================================
           DOCUMENT/PAPER CSS
           ========================================= */
        .page-content-wrapper {
            padding: 40px 20px;
            /* Padding moved here */
        }

        .document-container {
            max-width: 900px;
            background: var(--paper-white);
            margin: 0 auto;
            padding: 60px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        /* Document Header (Logos + Title) */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo-doc {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }

        .doc-title {
            text-align: center;
            flex-grow: 1;
        }

        .doc-title h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #000;
        }

        /* Content Sections */
        .section-block {
            margin-bottom: 35px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .section-header i {
            font-size: 20px;
            color: var(--bg1);
        }

        .section-header h3 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            color: #000;
        }

        .rules-list {
            list-style: none;
            padding-left: 32px;
            margin: 0;
        }

        .rules-list li {
            position: relative;
            margin-bottom: 8px;
            font-size: 14px;
            line-height: 1.6;
            color: var(--text-dark);
        }

        .numbered-list {
            counter-reset: item;
        }

        .numbered-list li {
            padding-left: 5px;
        }

        .numbered-list li::before {
            content: counter(item) ". ";
            counter-increment: item;
            font-weight: 600;
            color: var(--bg1);
            margin-right: 5px;
        }

        .sub-category {
            font-weight: 700;
            color: var(--bg1);
            margin-top: 10px;
            display: block;
        }

        .bullet-list li::before {
            content: "•";
            color: var(--bg1);
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
        }

        /* =========================================
           RESPONSIVE CSS
           ========================================= */
        /* =========================================
           RESPONSIVE CSS (MOBILE FIXES)
           ========================================= */
        @media (max-width: 768px) {
            #menu-icon {
                display: block;
            }

            /* Sidebar container */
            .nav-right {
                position: fixed;
                top: 0;
                right: -250px;
                width: 250px;
                height: 100vh;
                background: var(--bg2);
                display: flex;
                /* Ensure flex is set */
                flex-direction: column;
                align-items: flex-start;
                padding: 60px 20px;
                transition: right 0.3s ease;
                z-index: 2000;
                overflow-y: auto;
                /* Allow scrolling if menu is tall */
            }

            .nav-right.active {
                right: 0;
            }

            /* Standard Links */
            .nav-right>a {
                width: 100%;
                margin: 15px 0;
                display: block;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                /* Separator lines */
                padding-bottom: 5px;
            }

            #close-icon {
                display: block;
                position: absolute;
                top: 20px;
                right: 25px;
                font-size: 30px;
                color: var(--white);
            }

            /* --- DROPDOWN FIXES START --- */

            /* 1. Reset the dropdown container to stack vertically */
            .nav-item-dropdown {
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
                display: block;
                /* Important for stacking */
                margin: 15px 0;
            }

            /* 2. Style the "HOME" trigger */
            .nav-item-dropdown>a {
                width: 100%;
                display: flex;
                justify-content: space-between;
                /* Push arrow to the right */
                color: var(--white);
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding-bottom: 5px;
            }

            /* 3. The Dropdown Content Box */
            .nav-item-dropdown .dropdown-content {
                position: relative;
                /* FIX: This pushes "About Us" down */
                top: 0;
                width: 100%;
                box-shadow: none;
                background: rgba(0, 0, 0, 0.2);
                /* Darker background for contrast */
                padding: 0;

                /* Start hidden, JS will toggle this */
                display: none;
                opacity: 1;
                transform: none;
            }

            /* 4. When active class is added by JS, show it */
            .nav-item-dropdown.active .dropdown-content {
                display: block;
            }

            /* 5. Style the sub-links (Rules, Messages) */
            .nav-item-dropdown .dropdown-content a {
                color: var(--white) !important;
                border: none;
                padding: 12px 0 12px 30px;
                /* Indent them */
                font-size: 12px;
                background: transparent;
            }

            /* --- DROPDOWN FIXES END --- */

            /* Profile Dropdown Mobile */
            .profile-menu .dropdown {
                position: relative;
                top: 0;
                width: 100%;
                box-shadow: none;
                background: rgba(255, 255, 255, 0.1);
            }
        }
    </style>
</head>

<body>

    <header class="navbar">
        <div class="nav-left">
            <a href="studenthome.php"><img src="puplogo.png" alt="PUP Logo" /></a>
            <div class="brand-title">
                <p class="univ">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</p>
                <div class="saliksik">SALIKSIK</div>
            </div>
        </div>
        <i class="fa-solid fa-bars" id="menu-icon"></i>

        <nav class="nav-right" id="nav-menu">
            <i class="fa-solid fa-xmark" id="close-icon"></i>

            <a href="studenthome.php">Home</a>

            <div class="nav-item-dropdown">
                <a href="studenthome.php#about">About us <i class="fa-solid fa-angle-down"></i></a>
                <div class="dropdown-content">
                    <a href="studentrules.php"><i class="fa-solid fa-scale-balanced"></i> Rules</a>
                    <a href="studentmessages.php"><i class="fa-solid fa-envelope"></i> Messages</a>
                </div>
            </div>


            <a href="studentviewbooks.php">Books</a>
            <a href="studenthome.php#contact">Contact us</a>


            <div class="sign-out">
                <div class="profile-menu">
                    <i class="fa-solid fa-circle-user fa-lg" style="color: #ffffff;"></i>
                    <div class="dropdown">
                        <a href="studentprofile.php"><i class="fa-solid fa-user" style="color: #550000;"></i>Profile</a>
                        <a href="studentlogout.php"><i class="fa-solid fa-right-from-bracket" style="color: #550000;"></i>Sign Out</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>


    <div class="page-content-wrapper">
        <div class="document-container">

            <header class="doc-header">
                <img src="puplogo.png" alt="PUP Logo" class="logo-doc">
                <div class="doc-title">
                    <h1>Rules and Regulations</h1>
                </div>
                <img src="paranaquelogo.jpg" alt="Parañaque Logo" class="logo-doc">
            </header>

            <div class="section-block">
                <div class="section-header">
                    <i class="fa-solid fa-book-bookmark"></i>
                    <h3>Library Rules and Regulations</h3>
                </div>
                <div style="padding-left: 32px; margin-bottom: 10px; font-weight: 600; color: #550000; font-size: 13px;">📍 GENERAL RULES</div>
                <ol class="rules-list numbered-list">
                    <li>Library ID or School ID is required to enter and borrow materials.</li>
                    <li>Maintain silence at all times to avoid disturbing others.</li>
                    <li>Use of mobile phones is not allowed inside the reading areas.</li>
                    <li>Food and drinks are strictly prohibited.</li>
                    <li>Handle books, computers, and furniture with care and respect.</li>
                    <li>Personal belongings must be left in the designated area.</li>
                </ol>
            </div>

            <div class="section-block">
                <div class="section-header">
                    <i class="fa-solid fa-layer-group"></i>
                    <h3>Borrowing and Returning Books</h3>
                </div>
                <ul class="rules-list bullet-list">
                    <span class="sub-category">1. Borrowing Limits:</span>
                    <li>Students: Up to 3 books</li>
                    <li>Faculty: Up to 5 books</li>
                    <li>Alumni: Up to 2 books</li>

                    <span class="sub-category">2. Loan Period:</span>
                    <li>Students & Alumni: 7–14 days</li>
                    <li>Faculty: Up to 30 days</li>

                    <br>
                    <li>Renewals are allowed once if no one else has reserved the item.</li>
                    <li>Overdue books will incur fines (e.g., ₱5 per day/book).</li>
                    <li>Lost or damaged books must be replaced or paid for at the current market price.</li>
                </ul>
            </div>

            <div class="section-block">
                <div class="section-header">
                    <i class="fa-solid fa-laptop-code"></i>
                    <h3>Digital Library Usage</h3>
                </div>
                <ol class="rules-list numbered-list">
                    <li>Use your official login credentials to access digital content.</li>
                    <li>Downloading and sharing copyrighted materials is prohibited.</li>
                    <li>Only educational or research purposes are allowed—no gaming or unrelated browsing.</li>
                    <li>Access is monitored to ensure fair use and security.</li>
                </ol>
            </div>

            <div class="section-block">
                <div class="section-header">
                    <i class="fa-solid fa-handshake"></i>
                    <h3>User Conduct</h3>
                </div>
                <ol class="rules-list numbered-list">
                    <li>Be courteous and respectful to library staff and fellow users.</li>
                    <li>Misconduct, including vandalism or verbal abuse, will result in disciplinary action.</li>
                    <li>Follow all posted signage and staff instructions.</li>
                    <li>Alumni must follow the same rules and may have limited access to physical materials.</li>
                </ol>
            </div>

            <div class="section-block">
                <div class="section-header">
                    <i class="fa-solid fa-bullhorn"></i>
                    <h3>Violations</h3>
                </div>
                <ul class="rules-list bullet-list">
                    <li>Repeated violations may lead to suspension of library privileges.</li>
                    <li>Serious offenses will be reported to the appropriate school authority.</li>
                </ul>
            </div>

        </div>
    </div>
    
    <div class="chat-wrapper">
        <button class="chat-toggle-btn" onclick="toggleChat()">
            <img src="chatbot.png" alt="Chat with SalikTech">
        </button>

        <div class="chat-container" id="chatContainer">
            <div class="chat-header">
                <div class="header-info">
                    <div class="bot-avatar">
                        <img src="chatbot.png" alt="Bot">
                    </div>
                    <div class="bot-details">
                        <h3>SalikTech AI</h3>
                        <span class="status-text"><span class="status-dot"></span> Online</span>
                    </div>
                </div>
                <button class="close-chat" onclick="toggleChat()">
                    <iconify-icon icon="mdi:close"></iconify-icon>
                </button>
            </div>

            <div class="chat-body" id="chatBody">
                <div class="message bot">
                    <p>Hello Student! 👋 I'm SalikTech. I can help you find books, check events, or view your account status.</p>
                    <span class="time">Just now</span>
                </div>
                <div class="options-container">
                    <button class="option-btn" onclick="handleOption('Find a book')">Find a book</button>
                    <button class="option-btn" onclick="handleOption('Events today')">Events today</button>
                    <button class="option-btn" onclick="handleOption('My Account Status')">My Account Status</button>
                </div>
            </div>

            <div class="chat-footer">
                <input type="text" id="userInput" placeholder="Ask about books, events, or account..." onkeypress="handleEnter(event)">
                <button class="send-btn" onclick="sendMessage()">
                    <iconify-icon icon="mdi:send"></iconify-icon>
                </button>
            </div>
        </div>
    </div>
    <script src="chatbot.js"></script>


    <script>
        const menuIcon = document.getElementById("menu-icon");
        const closeIcon = document.getElementById("close-icon");
        const navMenu = document.getElementById("nav-menu");
        const navLinks = document.querySelectorAll(".nav-right a");

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

        // Auto-close menu when clicking a link
        navLinks.forEach(link => {
            link.addEventListener("click", () => {
                navMenu.classList.remove("active");
            });
        });
    </script>

</body>

</html>