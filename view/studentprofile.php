<?php

session_start();

// 1. SECURITY CHECK
// If the user hasn't logged in (no session found), kick them back to login page
if (!isset($_SESSION['studentnumber'])) {
    header("Location: studentlogin.php");
    exit;
}

// 2. GET DATA FROM SESSION (This data came from your login page)
$firstname     = $_SESSION['firstname'];
$middlename    = $_SESSION["middlename"];
$lastname      = $_SESSION['lastname'];
$suffix        = $_SESSION['suffix'];
$course        = $_SESSION['course'];
$section       = $_SESSION['section'];
$phonenumber   = $_SESSION['phonenumber'];
$studentnumber = $_SESSION['studentnumber'];
$email         = $_SESSION['email']; // This comes from your login code: $row['email']
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SALIKSIK | Profile</title>
    <link rel="stylesheet" href="headerstyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/19d37dc8d9.js" crossorigin="anonymous"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>

    <style>
        :root {
            --maroon: #550000;
            --bg1: #5a0c0c;
            --bg2: #2c0000;
            --yellow: #ffd200;
            --white: #ffffff;
            --text-dark: #333;
            --border: #d9d9d9;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--white);
        }




        /* Profile Specific Styles */
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background: white;
            padding: 50px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2.section-title {
            font-size: 26px;
            margin-bottom: 15px;
            color: var(--text-dark);
            border-bottom: 2px solid var(--maroon);
            padding-bottom: 5px;
        }

        .student-name {
            font-size: 22px;
            font-weight: bold;
            color: var(--maroon);
            margin-bottom: 20px;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 40px;
            margin-top: 20px;
        }

        .field-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #555;
            margin-bottom: 4px;
        }

        /* This displays the user data */
        .value-box {
            background: #fafafa;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 5px;
            font-size: 15px;
            color: var(--text-dark);
        }

        .logout-btn {
            background: var(--maroon);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            margin-top: 30px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }

        .logout-btn:hover {
            background: #6d0000;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            #menu-icon {
                display: block;
            }

            /* Sidebar container */
            .nav-right {
                position: fixed;
                top: 0;
                right: -300px;
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

    <div class="container">
        <h2 class="section-title">Personal Data</h2>

        <div class="student-name">
            <?php echo strtoupper("$lastname, $firstname  $suffix $middlename"); ?>
            (<?php echo $studentnumber; ?>)
        </div>

        <div class="row">
            <div class="field-group">
                <label>Student Number</label>
                <div class="value-box"><?php echo $studentnumber; ?></div>
            </div>

            <div class="field-group">
                <label>Course</label>
                <div class="value-box"><?php echo $course; ?></div>
            </div>

            <div class="field-group">
                <label>Full Name</label>
                <div class="value-box">
                    <?php echo strtoupper("$lastname, $firstname $suffix $middlename "); ?>
                </div>
            </div>

            <div class="field-group">
                <label>Section</label>
                <div class="value-box"><?php echo $section; ?></div>
            </div>

            <div class="field-group">
                <label>Phone Number</label>
                <div class="value-box"><?php echo $phonenumber; ?></div>
            </div>

            <div class="field-group">
                <label>Suffix</label>
                <div class="value-box"><?php echo $suffix ?: 'N/A'; ?></div>
            </div>

            <div class="field-group">
                <label>Email Address</label>
                <div class="value-box"><?php echo $email; ?></div>
            </div>
        </div>

        <button class="logout-btn" onclick="window.location.href='studentlogout.php'">
            Logout
        </button>
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
        // Auto-Close Menu when a link is clicked (Optional but recommended)
        navLinks.forEach(link => {
            link.addEventListener("click", () => {
                navMenu.classList.remove("active");
            });
        });
    </script>

</body>

</html>