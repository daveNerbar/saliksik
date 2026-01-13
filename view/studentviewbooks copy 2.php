<?php
session_start();

if (!isset($_SESSION['studentnumber'])) { // Changed from studentnumber to student_id to match login session usually
    header("Location: studentlogin.php");
    exit();
}

include("conn.php");

// Fetch all books
$sql = "SELECT * FROM books ORDER BY created_at DESC";
$result = $conn->query($sql);

// Group books by Genre/Category (Normalized to Title Case)
$booksByGenre = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Normalize: "fiction", "FICTION" -> becomes "Fiction"
        $rawGenre = $row['genre'];
        $genre = ucwords(strtolower($rawGenre)); 

        if (!isset($booksByGenre[$genre])) {
            $booksByGenre[$genre] = [];
        }
        $booksByGenre[$genre][] = $row;
    }
    // Sort genres alphabetically
    ksort($booksByGenre);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books | SALIKSIK</title>
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
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--white);
        }

        /* --- LAYOUT --- */
        .container {
            display: flex;
            min-height: calc(100vh - 80px);
            position: relative;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--gray);
            padding: 25px;
            border-right: 1px solid #ccc;
            position: relative;
            transition: transform 0.3s ease;
        }

        .mobile-nav-group {
            display: none;
            margin-bottom: 20px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 20px;
        }

        .mobile-nav-group a {
            display: block;
            padding: 10px 0;
            color: var(--bg1);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
        }

        .filter-block {
            margin-bottom: 20px;
        }

        .filter-block label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #550000;
            margin-bottom: 8px;
        }

        .filter-block select,
        .filter-block input {
            width: 100%;
            padding: 10px;
            border: 1px solid #bbb;
            border-radius: 6px;
            font-size: 13px;
            outline: none;
        }

        .range-fields {
            display: flex;
            gap: 10px;
        }

        /* Content */
        .content {
            flex: 1;
            padding: 30px 40px;
            width: calc(100% - 260px);
        }

        .page-title {
            font-size: 28px;
            font-weight: 800;
            color: #000000ff;
            margin-bottom: 20px;
        }

        /* Search Bar */
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 40px;
        }

        .search-bar input {
            flex: 1;
            padding: 12px 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .search-bar button {
            background: #5a0c0c;
            border: none;
            width: 50px;
            height: 45px;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
        }

        .search-bar button:hover {
            background: #2c0000;
        }

        /* Genre Sections & Grid */
        .genre-section {
            margin-bottom: 50px;
        }

        .genre-header {
            font-size: 18px;
            font-weight: 800;
            color: #5a0c0c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            text-transform: uppercase;
        }

        .new-books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 25px;
        }

        /* Card */
        .new-book-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f0f0;
            border-left: 6px solid var(--bg1);
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 180px;
            transition: 0.3s;
        }

        .new-book-card:hover {
            transform: translateY(-5px);
            border-left-color: var(--yellow);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .new-book-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: #2c0000;
            margin: 0 0 15px 0;
            line-height: 1.4;
        }

        .meta-year {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
        }

        .view-btn {
            background: transparent;
            border: 1px solid #ddd;
            color: #666;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 8px 18px;
            border-radius: 20px;
            width: fit-content;
            align-self: flex-end;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
            margin-top: 15px;
        }

        .new-book-card:hover .view-btn {
            background: var(--bg1);
            border-color: var(--bg1);
            color: #fff;
        }

        /* Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1900;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* --- NAVBAR CSS (Included Inline for completeness based on context) --- */
        
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="overlay"></div>

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
        <aside class="sidebar" id="sidebar">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="margin:0; font-size:20px; color: #000000ff;">Menu</h2>
                <i class="fa-solid fa-xmark" onclick="toggleSidebar()" style="cursor:pointer; display:none; color: #5a0c0c;" id="close-sidebar"></i>
            </div>

            <div class="mobile-nav-group">
                <a href="home.php">Home</a>
                <a href="home.php#about">About Us</a>
                <a href="viewbooks.php" style="color:#5a0c0c;">Books</a>
            </div>

            <h2 style="font-size:24px; margin-bottom:20px; color:#000; font-weight: 700;">Filters</h2>

            <div class="filter-block">
                <label>Category (Genre)</label>
                <select id="categoryFilter">
                    <option value="All">All</option>
                    <?php 
                    // Get all unique keys from the grouped array and sort them
                    $genres = array_keys($booksByGenre);
                    sort($genres);
                    foreach ($genres as $g): 
                    ?>
                        <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-block">
                <label>Custom Range (Year)</label>
                <div class="range-fields">
                    <input type="number" id="minYear" placeholder="Min (e.g. 2020)">
                    <input type="number" id="maxYear" placeholder="Max (e.g. 2025)">
                </div>
            </div>

            <div class="filter-block">
                <label>Sort By</label>
                <select id="sortFilter">
                    <option value="relevant">Most Relevant</option>
                    <option value="newest">Newest to Oldest</option>
                    <option value="oldest">Oldest to Newest</option>
                    <option value="az">A–Z (Title)</option>
                    <option value="za">Z–A (Title)</option>
                </select>
            </div>
        </aside>

        <main class="content">
            <h1 class="page-title">Books Collection</h1>

            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search book title...">
                <button><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>

            <div id="booksContainer">
                <?php if (!empty($booksByGenre)): ?>
                    <?php foreach ($booksByGenre as $genre => $books): ?>
                        <div class="genre-section" data-category="<?= htmlspecialchars($genre) ?>">
                            <h2 class="genre-header"><?= htmlspecialchars($genre) ?></h2>
                            <div class="new-books-grid">
                                <?php foreach ($books as $book): ?>
                                    <div class="new-book-card"
                                         data-title="<?= strtolower(htmlspecialchars($book['book_title'])) ?>"
                                         data-year="<?= htmlspecialchars($book['publish_year']) ?>"
                                         data-category="<?= htmlspecialchars($genre) ?>">
                                        <div>
                                            <h3><?= htmlspecialchars($book['book_title']) ?></h3>
                                            <div class="meta-year">Year: <?= htmlspecialchars($book['publish_year']) ?></div>
                                        </div>
                                        <a href="studentbookdetails.php?id=<?= $book['id'] ?>" class="view-btn">View Details &rarr;</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center; font-size:18px; color:#666;">No books available in the collection yet.</p>
                <?php endif; ?>
            </div>
        </main>
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
        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const closeBtn = document.getElementById('close-sidebar');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            closeBtn.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
        }
        document.getElementById('overlay').addEventListener('click', toggleSidebar);

        // --- FILTERING LOGIC ---
        const categoryFilter = document.getElementById('categoryFilter');
        const searchInput = document.getElementById('searchInput');
        const minYearInput = document.getElementById('minYear');
        const maxYearInput = document.getElementById('maxYear');
        const sortFilter = document.getElementById('sortFilter');

        function updateBooks() {
            // Get values and normalize to lowercase for case-insensitive comparison
            const category = categoryFilter.value.toLowerCase();
            const search = searchInput.value.toLowerCase();
            const minYear = parseInt(minYearInput.value) || 0;
            const maxYear = parseInt(maxYearInput.value) || 9999;
            const sections = document.querySelectorAll('.genre-section');

            sections.forEach(section => {
                // Get the category from the section data attribute (PHP populated)
                const sectionCategory = section.getAttribute('data-category').toLowerCase();
                const cards = section.querySelectorAll('.new-book-card');
                let hasVisibleCards = false;

                // Check Category Match (All or Specific)
                const isSectionMatch = (category === 'all' || sectionCategory === category);

                if (!isSectionMatch) {
                    section.style.display = 'none';
                    return; 
                }

                // Filter Cards inside the section
                cards.forEach(card => {
                    const title = card.getAttribute('data-title');
                    const year = parseInt(card.getAttribute('data-year'));

                    const isTitleMatch = title.includes(search);
                    const isYearMatch = (year >= minYear && year <= maxYear);

                    if (isTitleMatch && isYearMatch) {
                        card.style.display = 'flex';
                        hasVisibleCards = true;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show section only if it has visible cards
                section.style.display = hasVisibleCards ? 'block' : 'none';
            });

            sortBooks();
        }

        function sortBooks() {
            const sortValue = sortFilter.value;
            const grids = document.querySelectorAll('.new-books-grid');

            grids.forEach(grid => {
                const cards = Array.from(grid.querySelectorAll('.new-book-card'));

                cards.sort((a, b) => {
                    const titleA = a.getAttribute('data-title');
                    const titleB = b.getAttribute('data-title');
                    const yearA = parseInt(a.getAttribute('data-year'));
                    const yearB = parseInt(b.getAttribute('data-year'));

                    if (sortValue === 'az') return titleA.localeCompare(titleB);
                    if (sortValue === 'za') return titleB.localeCompare(titleA);
                    if (sortValue === 'newest') return yearB - yearA;
                    if (sortValue === 'oldest') return yearA - yearB;
                    return 0;
                });

                cards.forEach(card => grid.appendChild(card));
            });
        }

        // Event Listeners
        categoryFilter.addEventListener('change', updateBooks);
        searchInput.addEventListener('input', updateBooks);
        minYearInput.addEventListener('input', updateBooks);
        maxYearInput.addEventListener('input', updateBooks);
        sortFilter.addEventListener('change', sortBooks);

        // Navbar Mobile Logic
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