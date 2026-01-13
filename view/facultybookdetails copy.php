<?php

session_start();
if (!isset($_SESSION['pupid'])) {
    header("Location: facultyogin.php");
    exit;
}
// --- DATABASE CONNECTION ---
include("conn.php");
// GET BOOK ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Secure integer conversion
    $sql = "SELECT * FROM books WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
    } else {
        echo "Book not found.";
        exit;
    }
} else {
    echo "No book selected.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book['book_title']) ?> | SALIKSIK</title>
    <link rel="stylesheet" href="headerstyle.css">
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
            --cream: #FAF8F1;
        }


        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--white);
        }

        /* Navbar (Identical to viewbooks) */


        /* DETAILS CONTAINER */
        .details-container {
            max-width: 900px;
            margin: 40px auto;
            background-color: var(--cream);
            padding: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            position: relative;
        }

        .back-btn {
            text-decoration: none;
            color: #333;
            font-size: 24px;
            display: inline-block;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .back-btn:hover {
            transform: translateX(-5px);
        }

        .book-header {
            border-bottom: 1px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .book-title {
            font-size: 22px;
            font-weight: 700;
            font-style: italic;
            color: #111;
            margin: 0;
            line-height: 1.4;
        }

        .section-label {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 10px;
            color: #000;
        }

        .abstract-text {
            font-size: 14px;
            line-height: 1.6;
            text-align: justify;
            color: #333;
            margin-bottom: 40px;
        }

        .info-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid #333;
            padding-top: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .meta-table {
            border-collapse: collapse;
            width: 100%;
            max-width: 500px;
            font-size: 13px;
        }

        .meta-table td {
            border: 1px solid #999;
            padding: 8px 12px;
        }

        .meta-table td:first-child {
            background-color: #f0ece1;
            font-weight: 600;
            width: 120px;
        }

        .room-use-badge {
            background-color: #550000;
            color: #ffd200;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
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

            <a href="facultyhome.php">Home</a>

            <div class="nav-item-dropdown">
                <a href="facultyhome.php#about">About us <i class="fa-solid fa-angle-down"></i></a>
                <div class="dropdown-content">
                    <a href="facultyrules.php"><i class="fa-solid fa-scale-balanced"></i> Rules</a>
                    <a href="facultyviewmessages.php"><i class="fa-solid fa-envelope"></i> Messages</a>
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

    <main>
        <div class="details-container">
            <a href="facultyviewbooks.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>

            <div class="book-header">
                <h1 class="book-title"><?= htmlspecialchars($book['book_title']) ?></h1>
            </div>

            <div class="book-body">
                <div class="section-label">Abstract / Description</div>
                <p class="abstract-text">
                    <?= nl2br(htmlspecialchars($book['description'])) ?>
                </p>
            </div>

            <div class="info-footer">
                <table class="meta-table">
                    <tr>
                        <td>Author:</td>
                        <td><?= htmlspecialchars($book['authors']) ?></td>
                    </tr>
                    <tr>
                        <td>Published:</td>
                        <td><?= htmlspecialchars($book['publish_year']) ?></td>
                    </tr>
                    <tr>
                        <td>Publisher:</td>
                        <td><?= htmlspecialchars($book['publisher']) ?></td>
                    </tr>
                    <tr>
                        <td>Genre:</td>
                        <td><?= htmlspecialchars($book['genre']) ?></td>
                    </tr>
                    <tr>
                        <td>Language:</td>
                        <td><?= htmlspecialchars($book['language']) ?></td>
                    </tr>
                    <tr>
                        <td>Call No:</td>
                        <td><?= htmlspecialchars($book['call_number']) ?></td>
                    </tr>
                </table>

                <div class="room-use-badge">
                    <i class="fa-solid fa-building-columns"></i>
                    Full paper Available is for library room only
                </div>


            </div>
        </div>
    </main>
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