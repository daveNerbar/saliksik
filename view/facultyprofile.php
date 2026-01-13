<?php
session_start();
if (!isset($_SESSION['pupid'])) {
    header("Location: facultyogin.php");
    exit;
}

$firstname = $_SESSION['firstname'];
$middlename = $_SESSION["middlename"];
$lastname = $_SESSION['lastname'];
$suffix = $_SESSION['suffix'];
$phonenumber = $_SESSION['phonenumber'];
$pupid = $_SESSION['pupid'];
$department = $_SESSION['department'];
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="headerstyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/19d37dc8d9.js" crossorigin="anonymous"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>


    <title>SALIKSIK | Faculty Profile</title>

    <style>
        :root {
            --maroon: #550000;
            --light-bg: #f2f4f7;
            --text-dark: #333;
            --border: #d9d9d9;
            --bg1: #5a0c0c;
            --bg2: #2c0000;
            --yellow: #ffd200;
            --white: #ffffff;
            --gray: #f5f5f5;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--white);
        }



        .container {
            max-width: 1100px;
            margin: 40px auto;
            background: white;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2.section-title {
            font-size: 26px;
            border-bottom: 2px solid var(--maroon);
            padding-bottom: 5px;
        }

        .student-name {
            font-size: 22px;
            font-weight: bold;
            color: var(--maroon);
            margin-top: 15px;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px 40px;
            margin-top: 25px;
        }

        .field-group label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .value-box {
            background: #fafafa;
            border: 1px solid var(--border);
            padding: 10px 12px;
            border-radius: 5px;
            font-size: 15px;
        }

        .logout-btn {
            margin-top: 30px;
            padding: 12px 18px;
            background: var(--maroon);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 15px;
            cursor: pointer;
            font-weight: bold;
        }

        .logout-btn:hover {
            background: #7b0000;
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

    <!-- CONTENT -->
    <div class="container">
        <h2 class="section-title">Faculty Information</h2>

        <div class="student-name">
            <?php echo strtoupper("$lastname, $firstname $middlename $suffix"); ?>
            (<?php echo $pupid; ?>)
        </div>

        <div class="row">
            <div class="field-group">
                <label>PUP ID Number</label>
                <div class="value-box"><?php echo $pupid; ?></div>
            </div>

            <div class="field-group">
                <label>Full Name</label>
                <div class="value-box"><?php echo strtoupper("$lastname, $firstname $middlename $suffix"); ?></div>
            </div>

            <div class="field-group">
                <label>Department</label>
                <div class="value-box"><?php echo $department; ?></div>
            </div>

            <div class="field-group">
                <label>Phone Number</label>
                <div class="value-box"><?php echo $phonenumber; ?></div>
            </div>

            <div class="field-group">
                <label>Email Address</label>
                <div class="value-box"><?php echo $email; ?></div>
            </div>

            <div class="field-group">
                <label>Suffix</label>
                <div class="value-box"><?php echo $suffix ?: 'N/A'; ?></div>
            </div>
        </div>

        <button class="logout-btn" onclick="window.location.href='facultylogout.php'">
            Logout
        </button>

    </div>

</body>

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

</html>