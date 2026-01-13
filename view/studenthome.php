<?php
session_start();

// --- 1. SECURITY: CHECK LOGIN ---
if (!isset($_SESSION['studentnumber'])) {
    header("Location: studentlogin.php");
    exit();
}

// --- 2. DATABASE CONNECTION ---
include("conn.php");

// --- 3. HANDLE "VIEW ALL" TOGGLES ---
$annLimit = "LIMIT 3";
$evtLimit = "LIMIT 3";
$showAllAnn = false;
$showAllEvt = false;

if (isset($_GET['view'])) {
    if ($_GET['view'] == 'all_announcements') {
        $annLimit = ""; // No limit
        $showAllAnn = true;
    }
    if ($_GET['view'] == 'all_events') {
        $evtLimit = ""; // No limit
        $showAllEvt = true;
    }
}

// --- 4. FETCH ANNOUNCEMENTS ---
$ann_sql = "SELECT * FROM announcements WHERE end_date >= CURDATE() ORDER BY created_at DESC $annLimit";
$announcements = $conn->query($ann_sql);

// --- 5. FETCH EVENTS ---
$evt_sql = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC $evtLimit";
$events = $conn->query($evt_sql);

// --- HELPER: Get Emoji based on category/type ---
function getIcon($type)
{
    $type = strtolower($type);
    if (strpos($type, 'health') !== false) return '❤️‍🩹';
    if (strpos($type, 'book') !== false) return '📘';
    if (strpos($type, 'research') !== false) return '🔬';
    if (strpos($type, 'community') !== false) return '🤝';
    if (strpos($type, 'workshop') !== false) return '🛠️';
    return '📢'; // Default
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | SALIKSIK</title>
    <link rel="stylesheet" href="headerstyle.css" />
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/19d37dc8d9.js" crossorigin="anonymous"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>

    <style>
        :root {
            --bg1: #550000;
            --bg2: #2c0000;
            --yellow: #ffd200;
            --white: #ffffff;
            --gray: #f5f5f5;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg1);
            background-image: url('puppq.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            overflow-x: hidden;
        }

        /* --- NAVBAR CSS --- */



        /* --- SECTIONS --- */

        /* Hero Section */
        .hero {
            padding: 5% 0;
            background: rgba(0, 0, 0, 0.5);
            /* Darker overlay for better text visibility */
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .description {
            margin: 0 50px;
            font-family: 'Poppins', sans-serif;
            border-left: 5px solid #FFBF0F;
            padding-left: 30px;
            color: aliceblue;
            max-width: 800px;
        }

        .description h1 {
            color: white;
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .description p {
            width: 100%;
            max-width: 700px;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 40px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .description a {
            text-decoration: none;
            background-color: #FFBF0F;
            padding: 12px 30px;
            border-radius: 30px;
            color: #550000;
            font-weight: 800;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s, background 0.2s;
        }

        .description a:hover {
            transform: translateY(-3px);
            background-color: #ffcf40;
        }

        /* About Section */
        .about-section {
            padding: 80px 20px;
            text-align: center;
            opacity: 0;
            transform: translateY(50px);
            transition: all 1s ease;
            background: rgba(255, 255, 255, 0.95);
        }

        .about-section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .about-section h2 {
            color: #800000;
            font-size: 2.5rem;
            margin-bottom: 30px;
            font-weight: 800;
        }

        .motto {
            background: #800000;
            color: white;
            margin: 20px auto;
            padding: 40px;
            border-radius: 15px;
            width: 90%;
            max-width: 1000px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .motto h3 {
            margin-top: 0;
            font-size: 1.5rem;
        }

        .vision-mission {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 40px;
            flex-wrap: wrap;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .vision,
        .mission {
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border-radius: 15px;
            flex: 1;
            min-width: 300px;
            max-width: 500px;
            border-top: 5px solid #800000;
        }

        .vision h3,
        .mission h3 {
            color: #800000;
            margin-top: 0;
        }

        /* Announcement & Events Sections */
        .announcement-section,
        .events-section {
            text-align: center;
            opacity: 0;
            transform: translateY(50px);
            transition: all 1s ease;
            background: #f4f4f4;
            padding: 60px 0;
        }

        .announcement-section.visible,
        .events-section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .events-section {
            background: #ffffff;
        }

        .announcement-section h2,
        .events-section h2 {
            background: #800000;
            color: white;
            padding: 20px;
            font-weight: 800;
            font-size: 2rem;
            margin: 0 0 50px 0;
            letter-spacing: 2px;
        }

        /* Card Container */
        .announcement-container,
        .events-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding: 0 20px;
            max-width: 1300px;
            margin: 0 auto;
        }

        /* Cards */
        .announcement-card,
        .event-card {
            width: 320px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid #eee;
        }

        .announcement-card:hover,
        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .announcement-header,
        .event-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .category {
            display: inline-block;
            background: #ffebeb;
            color: #800000;
            font-size: 12px;
            font-weight: 700;
            border-radius: 20px;
            padding: 5px 12px;
            text-transform: uppercase;
        }

        .announcement-body,
        .event-body {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .icon-circle {
            font-size: 48px;
            margin-bottom: 20px;
            height: 80px;
            width: 80px;
            background: #fdf2f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #800000;
        }

        .announcement-body h3,
        .event-body h3 {
            color: #333;
            font-weight: 700;
            font-size: 1.2rem;
            margin: 0 0 10px 0;
            line-height: 1.3;
        }

        .announcement-body p,
        .event-body p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .date {
            color: #800000;
            font-weight: 600;
            font-size: 13px;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #eee;
            width: 100%;
        }

        .learn-btn {
            background: #800000;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
            margin-top: 10px;
            font-size: 13px;
        }

        .learn-btn:hover {
            background: #a00000;
        }

        .more-announcement,
        .more-events {
            text-align: center;
            margin-top: 40px;
        }

        .more-announcement a,
        .more-events a {
            color: #800000;
            text-decoration: none;
            font-weight: 700;
            border: 2px solid #800000;
            padding: 10px 25px;
            border-radius: 30px;
            transition: all 0.3s;
            display: inline-block;
        }

        .more-announcement a:hover,
        .more-events a:hover {
            background: #800000;
            color: white;
        }

        /* --- Contact Section --- */
        .contact-section {
            background: #800000;
            color: white;
            text-align: center;
            padding: 80px 20px;
            opacity: 0;
            transform: translateY(50px);
            transition: all 1s ease;
        }

        .contact-section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .contact-section h2 {
            font-size: 2.5em;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .contact-section .subtitle {
            font-size: 1.1rem;
            margin-bottom: 50px;
            color: #ffcccc;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .contact-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 50px;
            text-align: left;
            max-width: 1000px;
            margin: 0 auto;
        }

        .contact-info h3 {
            font-size: 1.5em;
            margin-bottom: 20px;
            border-bottom: 2px solid #ffbf0f;
            display: inline-block;
            padding-bottom: 5px;
        }

        .contact-info ul {
            list-style: none;
            padding: 0;
        }

        .contact-info li {
            margin-bottom: 20px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .contact-info .icon {
            font-size: 1.3rem;
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .contact-info a {
            color: white;
            text-decoration: none;
            transition: color 0.2s;
        }

        .contact-info a:hover {
            color: #ffbf0f;
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

    <section class="hero" id="home">
        <div class="description">
            <h1>Welcome to SALIKSIK</h1>
            <p>SALIKSIK is PUP’s digital research platform that provides equal access to academic
                resources, enhances research, and modernizes the library system.</p>
            <a href="studentviewbooks.php">Explore Libraries <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </section>

    <section id="about" class="about-section">
        <h2>ABOUT US</h2>
        <div class="motto">
            <h3><i>"Mula Sa ‘Yo, Para sa Bayan"</i></h3>
            <p>
                SALIKSIK is a digital research platform designed to enhance the availability of academic sources
                throughout the Polytechnic University of the Philippines (PUP) libraries. Ensuring everyone has
                equal access to educational resources is its aim. By introducing SALIKSIK, PUP aims to improve
                research opportunities, update its library system, and raise educational standards generally.
            </p>
        </div>
        <div class="vision-mission">
            <div class="vision">
                <h3><i>Vision</i></h3>
                <p>A Leading Comprehensive Polytechnic University in Asia</p>
            </div>
            <div class="mission">
                <h3><i>Mission</i></h3>
                <p>Advance an inclusive, equitable, and globally relevant polytechnic education towards national
                    development.</p>
            </div>
        </div>
    </section>

    <section id="announcement" class="announcement-section">
        <h2>ANNOUNCEMENTS</h2>

        <div class="announcement-container">
            <?php if ($announcements && $announcements->num_rows > 0): ?>
                <?php while ($row = $announcements->fetch_assoc()): ?>
                    <div class="announcement-card">
                        <div class="announcement-header">
                            <span class="category"><?= htmlspecialchars($row['type']) ?></span>
                        </div>
                        <div class="announcement-body">
                            <div class="icon-circle"><?= getIcon($row['type']) ?></div>
                            <h3><?= htmlspecialchars($row['title']) ?></h3>
                            <p><?= htmlspecialchars(substr($row['content'], 0, 100)) ?>...</p>
                            <p class="date">📅 <?= date('M d', strtotime($row['start_date'])) ?> – <?= date('M d, Y', strtotime($row['end_date'])) ?></p>

                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%; color: #666; font-size: 1.2rem;">No active announcements at this time.</p>
            <?php endif; ?>
        </div>

        <div class="more-announcement">
            <?php if ($showAllAnn): ?>
                <a href="studenthome.php#announcement">Show Less</a>
            <?php else: ?>
                <a href="?view=all_announcements#announcement">View All Announcements</a>
            <?php endif; ?>
        </div>
    </section>

    <section id="events" class="events-section">
        <h2>UPCOMING EVENTS</h2>

        <div class="events-container">
            <?php if ($events && $events->num_rows > 0): ?>
                <?php while ($row = $events->fetch_assoc()): ?>
                    <div class="event-card">
                        <div class="event-header">
                            <span class="category"><?= htmlspecialchars($row['category']) ?></span>
                        </div>
                        <div class="event-body">
                            <div class="icon-circle"><?= getIcon($row['category']) ?></div>
                            <h3><?= htmlspecialchars($row['title']) ?></h3>
                            <p><?= htmlspecialchars(substr($row['description'], 0, 100)) ?>...</p>
                            <p class="date">📅 <?= date('F d, Y', strtotime($row['event_date'])) ?> • <?= date('h:i A', strtotime($row['event_time'])) ?></p>

                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%; color: #666; font-size: 1.2rem;">No upcoming events scheduled.</p>
            <?php endif; ?>
        </div>

        <div class="more-events">
            <?php if ($showAllEvt): ?>
                <a href="studenthome.php#events">Show Less</a>
            <?php else: ?>
                <a href="?view=all_events#events">View All Events</a>
            <?php endif; ?>
        </div>
    </section>

    <section id="contact" class="contact-section">
        <h2>Get In Touch</h2>
        <p class="subtitle">
            Have questions about our library system or need assistance? Contact us using the details below.
        </p>

        <div class="contact-container">
            <div class="contact-info">
                <h3>Contact Information</h3>
                <ul>
                    <li><span class="icon">📧</span> <a href="mailto:Puplibrary@gmail.com">PupLibrary@gmail.com</a></li>
                    <li><span class="icon">📞</span> <a href="tel:+6391234567890">+6391234567890</a></li>
                    <li><span class="icon">🌐</span> <a href="#">www.puplibrary.facebook.com</a></li>
                    <li><span class="icon">📍</span>
                        Col. E. De Leon St. Wawa, Brgy. Sto. Niño Parañaque City,<br>Philippines 1700
                    </li>
                </ul>
            </div>
        </div>
    </section>

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
        // Reveal Animations Logic
        function reveal() {
            var reveals = document.querySelectorAll(".about-section, .announcement-section, .events-section, .contact-section");

            for (var i = 0; i < reveals.length; i++) {
                var windowHeight = window.innerHeight;
                var elementTop = reveals[i].getBoundingClientRect().top;
                var elementVisible = 150;

                if (elementTop < windowHeight - elementVisible) {
                    reveals[i].classList.add("visible");
                }
            }
        }

        window.addEventListener("scroll", reveal);
        // Trigger once on load
        reveal();

        // --- Mobile Menu Logic ---
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