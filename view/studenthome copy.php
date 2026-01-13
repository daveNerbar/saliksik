<?php
// --- 1. DATABASE CONNECTION ---
include("conn.php");    


// --- 2. FETCH LATEST 3 ANNOUNCEMENTS ---
// We get the newest created ones first
$ann_sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3";
$announcements = $conn->query($ann_sql);

// --- 3. FETCH UPCOMING 3 EVENTS ---
// We get events that haven't happened yet, ordered by date
$evt_sql = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3";
$events = $conn->query($evt_sql);

// --- HELPER: Get Emoji based on category/type ---
function getIcon($type) {
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
    <title>PUP SALIKSIK</title>

   <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/19d37dc8d9.js" crossorigin="anonymous"></script>
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
            background-image: url(puppq.jpg);
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            overflow-x: hidden;
        }

        /* --- NAVBAR CSS --- */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 34px;
            background: linear-gradient(to right, var(--bg1), var(--bg2));
            color: var(--white);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .nav-left img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            display: block;
        }

        .brand-title {
            display: flex;
            flex-direction: column;
            line-height: 1;
        }

        .brand-title .univ {
            font-family: 'Cinzel', serif;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            margin: 0;
            color: var(--white);
        }

        .brand-title .saliksik {
            font-family: 'Knewave', cursive;
            color: #711E1E;
            text-shadow: -2px -2px 0 #FFE732, 1px -1px 0 #FFE732, -1px 1px 0 #FFE732, 1px 1px 0 #FFE732;
            font-size: 28px;
            margin: 0;
        }

        .nav-right {
            display: flex;
            gap: 35px;
            align-items: center;
        }

        .nav-right>a {
            color: var(--white);
            text-decoration: none;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            transition: color .18s;
        }

        .nav-right>a:hover {
            color: var(--yellow);
        }

        /* Dropdown Styling */
        .nav-item-dropdown {
            position: relative;
            display: flex;
            align-items: center;
            height: 100%;
        }

        .nav-item-dropdown>a {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--white);
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            text-decoration: none;
        }

        .nav-item-dropdown>a:hover {
            color: var(--yellow);
        }

        .nav-item-dropdown .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: var(--white);
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 2000;
            border-radius: 6px;
            padding: 5px 0;
        }

        .nav-item-dropdown:hover .dropdown-content {
            display: block;
        }

        .nav-item-dropdown .dropdown-content a {
            color: var(--bg1);
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            font-size: 13px;
            border-bottom: 1px solid #f0f0f0;
        }

        .nav-item-dropdown .dropdown-content a:hover {
            background-color: #f5f5f5;
        }

        /* Profile & Mobile Menu Icons */
        .profile-menu {
            position: relative;
            cursor: pointer;
        }

        .profile-menu .dropdown {
            display: none;
            position: absolute;
            top: 19px;
            right: 0;
            background: var(--white);
            border-radius: 6px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.3);
            min-width: 120px;
            z-index: 10;
        }

        .profile-menu:hover .dropdown {
            display: block;
        }

        .profile-menu .dropdown a {
            display: block;
            padding: 5px 10px;
            color: var(--bg1);
            text-decoration: none;
            font-size: 13px;
        }

        .profile-menu .dropdown a:hover {
            background: #e0e0e0;
        }

        #menu-icon {
            display: none;
            font-size: 28px;
            color: var(--white);
            cursor: pointer;
        }

        #close-icon {
            display: none;
        }


        /* --- RESPONSIVE SECTIONS --- */

        /* Hero Section */
        .hero {
            padding: 5% 0;
            background: rgba(0, 0, 0, 0.4);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .description {
            margin: 0 50px;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            border-left: 4px solid #FFBF0F;
            padding-left: 20px;
            color: aliceblue;
            max-width: 800px;
        }

        .description p {
            width: 100%;
            max-width: 700px;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .description h1 {
            color: white;
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .description a {
            text-decoration: none;
            background-color: #FFBF0F;
            padding: 12px 25px;
            border-radius: 20px;
            color: #550000;
            font-weight: bold;
            display: inline-block;
        }

        /* About Section */
        .about-section {
            padding: 80px 20px;
            text-align: center;
            opacity: 0;
            transform: translateY(100px);
            transition: all 1s ease;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px);
        }

        .about-section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .motto {
            background: #800000;
            color: white;
            margin: 20px auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 1000px;
        }

        .vision-mission {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .vision,
        .mission {
            background: white;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 10px;
            width: 45%;
            min-width: 300px;
        }

        .about-section h2 {
            color: #800000;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        /* Announcement & Events Sections */
        .announcement-section,
        .events-section {
            text-align: center;
            opacity: 0;
            transform: translateY(100px);
            transition: all 1s ease;
            background: rgba(255, 255, 255, 0.9);
            padding-bottom: 50px;
        }

        .announcement-section.visible,
        .events-section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .titleannouncement,
        .titleevents {
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
        }

        .announcement-section h2,
        .events-section h2 {
            margin-top: 0;
            background: #800000;
            color: white;
            width: 100%;
            padding: 15px;
            font-weight: bold;
            margin-bottom: 40px;
        }

        /* Card Container - Flexbox with Wrap */
        .announcement-container,
        .events-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding: 0 20px;
        }

        /* Cards */
        .announcement-card,
        .event-card {
            width: 300px;
            max-width: 100%;
            border: 2px solid #550000;
            border-radius: 12px;
            overflow: hidden;
            background: #f9f9f9;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .announcement-card:hover,
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        }

        .announcement-header .category,
        .event-header .category {
            display: inline-block;
            background: #f8d7da;
            color: #800000;
            font-size: 14px;
            border-radius: 6px;
            padding: 5px 10px;
            margin-top: 15px;
        }

        .icon-circle {
            font-size: 40px;
            margin: 20px 0;
        }

        .announcement-body,
        .event-body {
            padding: 15px 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .announcement-body h3,
        .event-body h3 {
            color: #000;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .announcement-body p,
        .event-body p {
            color: #444;
            font-size: 14px;
            line-height: 1.5;
        }

        .date {
            color: #800000;
            font-weight: bold;
            margin: 10px 0;
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
            margin-top: auto;
        }

        .learn-btn:hover {
            background: #a00000;
        }

        .more-announcement,
        .more-events {
            text-align: right;
            margin-top: 30px;
            margin-right: 5%;
        }

        .more-announcement a,
        .more-events a {
            color: #800000;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid #d4b4b4;
            padding: 5px 10px;
            border-radius: 5px;
            background: #fbecec;
        }

        .more-announcement a:hover,
        .more-events a:hover {
            background: #f5d4d4;
        }

        /* --- Contact Section --- */
        .contact-section {
            background: #800000;
            color: white;
            text-align: center;
            padding: 60px 20px;
            opacity: 0;
            transform: translateY(100px);
            transition: all 1s ease;
        }

        .contact-section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .contact-section h2 {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .contact-section .subtitle {
            font-size: 1rem;
            margin-bottom: 40px;
            color: #f5cccc;
        }

        .contact-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 40px;
        }

        .contact-form {
            background: #f0f0f0;
            color: #000;
            border-radius: 10px;
            padding: 25px 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .contact-form label {
            display: block;
            text-align: left;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: 2px solid #800000;
        }

        .send-btn {
            background: #800000;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s ease;
        }

        .send-btn:hover {
            background: #a00000;
        }

        .contact-info {
            text-align: left;
            max-width: 400px;
            width: 100%;
        }

        .contact-info h3 {
            font-size: 1.3em;
            margin-bottom: 10px;
        }

        .contact-info p {
            color: #f5cccc;
            font-size: 0.95rem;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .contact-info ul {
            list-style: none;
            padding: 0;
        }

        .contact-info li {
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .contact-info .icon {
            font-size: 1.2rem;
            margin-right: 10px;
        }

        .contact-info a {
            color: white;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        /* =========================================
           MEDIA QUERIES (RESPONSIVENESS)
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
            <h3><i>Mula Sa ‘Yo, Para sa Bayan”</i></h3>
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
        <div class="titleannouncement">
            <h2>ANNOUNCEMENT</h2>
        </div>

        <div class="announcement-container">
            <?php if ($announcements->num_rows > 0): ?>
                <?php while ($row = $announcements->fetch_assoc()): ?>
                    <div class="announcement-card">
                        <div class="announcement-header">
                            <span class="category"><?= htmlspecialchars($row['type']) ?></span>
                        </div>
                        <div class="announcement-body">
                            <div class="icon-circle"><?= getIcon($row['type']) ?></div>
                            <h3><?= htmlspecialchars($row['title']) ?></h3>
                            <p><?= htmlspecialchars($row['content']) ?></p>
                            <p class="date">📅 <?= date('M d', strtotime($row['start_date'])) ?> – <?= date('M d, Y', strtotime($row['end_date'])) ?></p>
                            <button class="learn-btn">Learn More</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%; color: #666;">No active announcements at this time.</p>
            <?php endif; ?>
        </div>

        <div class="more-announcement">
            <a href="#">› More Announcements</a>
        </div>
    </section>

    <section id="events" class="events-section">
        <div class="titleevents">
            <h2>EVENTS</h2>
        </div>

        <div class="events-container">
            <?php if ($events->num_rows > 0): ?>
                <?php while ($row = $events->fetch_assoc()): ?>
                    <div class="event-card">
                        <div class="event-header">
                            <span class="category"><?= htmlspecialchars($row['category']) ?></span>
                        </div>
                        <div class="event-body">
                            <div class="icon-circle"><?= getIcon($row['category']) ?></div>
                            <h3><?= htmlspecialchars($row['title']) ?></h3>
                            <p><?= htmlspecialchars($row['description']) ?></p>
                            <p class="date">📅 <?= date('F d, Y', strtotime($row['event_date'])) ?> • <?= date('h:i A', strtotime($row['event_time'])) ?></p>
                            <button class="learn-btn">Learn More</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%; color: #666;">No upcoming events scheduled.</p>
            <?php endif; ?>
        </div>

        <div class="more-events">
            <a href="#">› More Events</a>
        </div>
    </section>

    <section id="contact" class="contact-section">
        <h2>Get In Touch</h2>
        <p class="subtitle">
            Have questions about our library system or need assistance? Contact us or visit any of our campus libraries.
        </p>

        <div class="contact-container">
            <!-- 🔹 Contact Form -->
            

            <!-- 🔸 Contact Info -->
            <div class="contact-info">
                <h3>Contact Information</h3>
                <p>
                    Feel free to reach out to us with any questions or feedback.<br>
                    We're always looking to improve our framework.
                </p>

                <ul>
                    <li><span class="icon">📧</span> <a href="mailto:Puplibrary@gmail.com">PupLibrary@gmail.com</a></li>
                    <li><span class="icon">📞</span> <a href="tel:+6391234567890">+6391234567890</a></li>
                    <li><span class="icon">🌐</span> <a
                            href="https://www.puplibrary.facebook.com">www.puplibrary.facebook.com</a></li>
                    <li><span class="icon">📍</span>
                        Col. E. De Leon St. Wawa, Brgy. Sto. Niño Parañaque City,<br>Philippines 1700
                    </li>
                </ul>
            </div>
        </div>
    </section>

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
        // Auto-Close Menu when a link is clicked (Optional but recommended)
        navLinks.forEach(link => {
            link.addEventListener("click", () => {
                navMenu.classList.remove("active");
            });
        });
    </script>

</body>
</html>