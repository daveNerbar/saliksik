<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php"); 
// --- 2. FETCH KPI COUNTS ---
// Total Students
$res = $conn->query("SELECT COUNT(*) as count FROM studacc");
$totalStudents = $res->fetch_assoc()['count'] ?? 0;

// Total Faculty
$res = $conn->query("SELECT COUNT(*) as count FROM facultyacc");
$totalFaculty = $res->fetch_assoc()['count'] ?? 0;

// Total Books (Sum of all copies)
$res = $conn->query("SELECT SUM(total_copies) as count FROM books");
$totalBooks = $res->fetch_assoc()['count'] ?? 0;

// Borrowed Books (Active)
$res = $conn->query("SELECT COUNT(*) as count FROM borrowing WHERE status = 'Borrowed'");
$totalBorrowed = $res->fetch_assoc()['count'] ?? 0;

// Overdue Books (Active AND Due Date is past)
$res = $conn->query("SELECT COUNT(*) as count FROM borrowing WHERE status = 'Borrowed' AND return_date < CURDATE()");
$totalOverdue = $res->fetch_assoc()['count'] ?? 0;

// --- 3. NOTIFICATION LOGIC ---
$notifications = [];

// A. Check Overdue Books
$overdueQ = $conn->query("
    SELECT b.accessor_no, b.return_date, 
           COALESCE(s.firstname, f.firstname) as name 
    FROM borrowing b
    LEFT JOIN studacc s ON b.student_number = s.studentnumber
    LEFT JOIN facultyacc f ON b.student_number = f.pupid
    WHERE b.status = 'Borrowed' AND b.return_date < CURDATE()
    ORDER BY b.return_date ASC
");

while ($row = $overdueQ->fetch_assoc()) {
    $notifications[] = [
        'type' => 'overdue',
        'message' => "Overdue: {$row['accessor_no']} borrowed by {$row['name']}",
        'time' => $row['return_date'] // Due date
    ];
}

// B. Check New Students (Last 24 Hours)
$newStudQ = $conn->query("SELECT firstname, lastname, created_at FROM studacc WHERE created_at >= NOW() - INTERVAL 1 DAY");
while ($row = $newStudQ->fetch_assoc()) {
    $notifications[] = [
        'type' => 'new_user',
        'message' => "New Student: {$row['firstname']} {$row['lastname']}",
        'time' => $row['created_at']
    ];
}

// C. Check New Faculty (Last 24 Hours)
$newFacQ = $conn->query("SELECT firstname, lastname, created_at FROM facultyacc WHERE created_at >= NOW() - INTERVAL 1 DAY");
while ($row = $newFacQ->fetch_assoc()) {
    $notifications[] = [
        'type' => 'new_user',
        'message' => "New Faculty: {$row['firstname']} {$row['lastname']}",
        'time' => $row['created_at']
    ];
}

$notifCount = count($notifications);

// --- 3. FETCH CHART DATA ---

// A. Course Statistics
$courseLabels = [];
$courseData = [];
$courseQ = $conn->query("SELECT course, COUNT(*) as count FROM studacc GROUP BY course LIMIT 5");
while ($row = $courseQ->fetch_assoc()) {
    $courseLabels[] = $row['course'];
    $courseData[] = $row['count'];
}

// B. Book Trends (Genre)
$genreLabels = [];
$genreData = [];
$genreQ = $conn->query("SELECT genre, COUNT(*) as count FROM books GROUP BY genre LIMIT 5");
while ($row = $genreQ->fetch_assoc()) {
    $genreLabels[] = $row['genre'];
    $genreData[] = $row['count'];
}

// --- 4. FETCH TOP BOOKS ---
$topBooksQ = $conn->query("SELECT book_title, total_copies FROM books ORDER BY total_copies DESC LIMIT 3");

// --- 5. FETCH RECENT ACTIVITIES (Last 5 Borrowings) ---
$activityQ = $conn->query("
    SELECT b.created_at, bk.book_title 
    FROM borrowing b 
    JOIN books bk ON b.accessor_no = bk.accessor_no 
    ORDER BY b.created_at DESC LIMIT 5
");

// Calculate Available Books for Chart
$booksAvailable = $totalBooks; // Logic: Total copies in DB usually represents current stock in a simple system
// If total_copies decreases on borrow, then Available = TotalBooks. 
// If total_copies is static max, then Available = TotalBooks - TotalBorrowed.
// Based on your previous 'bookborrow.php', we deduct copies. So TotalBooks = Available.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Saliksik Admin Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include 'header.php'; ?>


    <style>
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

        /* --- GLOBAL RESET & FONT SETTINGS --- */


        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            color: #374151;
            height: 100vh;
            overflow: hidden;
        }

        :root {
            --color-cyan: #00bcd4;
            --color-cyan-bg: #e0f7fa;
        }

        .welcome-card {
            background-color: white;
            padding: 1.75rem 2rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            border: 1px solid #e2e4e8;
        }

        .content-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .admin-highlight {
            color: #820000;
        }

        .content-subtitle {
            color: #6b7280;
            margin-top: 0.25rem;
            font-size: 1rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1rem 2.5rem;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-card p {
            font-size: 0.9rem;
            color: #555;
            margin: 0;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .stat-icon iconify-icon {
            font-size: 1.5rem;
            color: inherit !important;
        }

        /* Co   lors */
        .color-blue {
            color: #3b82f6;
        }

        .icon-blue {
            background-color: #83b2ffff;
            color: #ffffffff;
        }
        
        .color-cyan {
            color: #06b6d4;
        }

        .icon-cyan-custom {
            background-color: #cffafe;
            color: #06b6d4;
        }

        .color-orange {
            color: #f97316;
        }

        .icon-orange {
            background-color: #ffedd5;
            color: #f97316;
        }

        .color-red {
            color: #ef4444;
        }

        .icon-red {
            background-color: #fee2e2;
            color: #ef4444;
        }

        .color-indigo {
            color: #6366f1;
        }

        .icon-indigo {
            background-color: #e0e7ff;
            color: #6366f1;
        }

        .icon-green {
            background-color: #22c55e;
            color: #22c55e;
        }

        .icon-purple {
            background-color: #a855f7;
            color: #a855f7;
        }

        /* Book Section */
        .book-section {
            margin-top: 32px;
            margin-bottom: 40px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .view-all-pill {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.2rem;
        }

        .book-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s ease;
        }

        .book-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #fff;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .book-details {
            flex-grow: 1;
            margin-left: 1rem;
        }

        .book-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .book-subtitle {
            font-size: 0.85rem;
            color: #6b7280;
        }

        /* Charts Layout */
        .merged-dashboard {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .charts-row-1 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            align-items: stretch;
        }

        .books-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: stretch;
        }

        .books-container .legend {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 15px;
            width: 100%;
        }


        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
            padding: 30px;
            width: 100%;
            min-height: 380px;
            display: flex;
            flex-direction: column;
        }

        .card h2 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1f2937;
        }

        .demographics-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            flex-grow: 1;
        }

        .chart-container {
            position: relative;
            width: 200px !important;
            height: 200px !important;
            margin: 0 auto;
            flex-shrink: 0;
        }

        .chart-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .chart-center h3 {
            font-size: 22px;
            font-weight: bold;
            color: #8B0000;
            margin: 0;
        }

        .info.beside ul {
            list-style: none;
            padding: 0;
        }

        canvas {
            max-height: 280px;
            width: 100% !important;
            height: auto !important;
        }

        /* Recent Activities */
        .recent-activities {
            padding: 30px;
            height: auto;
        }

        .activities-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .activities-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .activities-list li {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .book-added {
            background-color: #3b82f6;
        }

        .book-borrowed {
            background-color: #f97316;
        }

        .activity-info p {
            margin: 0;
            font-size: 15px;
            color: #1e293b;
        }

        .activity-info span {
            font-size: 14px;
            color: #6b7280;
        }

        /* --- GLOBAL SEARCH DROPDOWN STYLES --- */
        .search-container {
            position: relative;
            /* Crucial for positioning the dropdown */
        }

        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: white;
            border: 1px solid #d1d5db;
            border-top: none;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
            /* Hidden by default */
            max-height: 400px;
            overflow-y: auto;
        }

        .search-result-item {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
            text-decoration: none;
            color: #374151;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background-color: #f9fafb;
        }

        .result-icon {
            width: 30px;
            height: 30px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #555;
        }

        .result-info h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .result-info span {
            font-size: 12px;
            color: #6b7280;
        }

        /* Badge colors for types */
        .type-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: auto;
            font-weight: 600;
        }

        .badge-Student {
            background: #e0e7ff;
            color: #4338ca;
        }

        .badge-Faculty {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-Admin {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-Book {
            background: #ffedd5;
            color: #c2410c;
        }
    </style>
</head>

<body>
    <div class="dashboard-container" id="dashboard-container">

        <aside class="sidebar" id="sidebar">
            <a href="dashboard.php">
                <div class="sidebar-logo">
                    <img src="puplogo.png" alt="PUP Logo" class="logo-image">
                    <span class="logo-text knewave-font">SALIKSIK</span>
                </div>
            </a>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-link active"><iconify-icon icon="mdi:view-dashboard"></iconify-icon><span class="nav-text">Dashboard</span></a>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle"><iconify-icon icon="mdi:account-group"></iconify-icon><span class="nav-text">User Management</span><span class="nav-arrow">&rsaquo;</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="student.php" class="dropdown-link">Student</a></li>
                        <li><a href="faculty.php" class="dropdown-link">Faculty</a></li>
                        <li><a href="userreport.php" class="dropdown-link">User Report</a></li>
                    </ul>
                </div>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle"><iconify-icon icon="mdi:bookshelf"></iconify-icon><span class="nav-text">Book Management</span><span class="nav-arrow">&rsaquo;</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="addbook.php" class="dropdown-link">Add Book</a></li>
                        <li><a href="manbook.php" class="dropdown-link">Manage Book</a></li>
                        <li><a href="bookreport.php" class="dropdown-link">Book Report</a></li>
                        <li><a href="unusedbooks.php" class="dropdown-link">Unused Books</a></li>
                    </ul>
                </div>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle"><iconify-icon icon="mdi:swap-horizontal"></iconify-icon><span class="nav-text">Borrowing Management</span><span class="nav-arrow">&rsaquo;</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="bookborrow.php" class="dropdown-link">Add Borrow</a></li>
                        <li><a href="borrowedlist.php" class="dropdown-link">Return Book</a></li>
                        <li><a href="reservebooks.php" class="dropdown-link">Reserved Book</a></li>
                        <li><a href="borrowedhistory.php" class="dropdown-link">Borrowed History</a></li>
                        <li><a href="borrowedreport.php" class="dropdown-link">Borrowed Report</a></li>
                    </ul>
                </div>

                <a href="annceve.php" class="nav-link"><iconify-icon icon="mdi:bullhorn"></iconify-icon><span class="nav-text">Announcements & Events</span></a>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle"><iconify-icon icon="clarity:administrator-solid"></iconify-icon><span class="nav-text">Admin Management</span><span class="nav-arrow">&rsaquo;</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="addadmin.php" class="dropdown-link">Add Administrator</a></li>
                        <li><a href="addminlist.php" class="dropdown-link">Administrator List</a></li>
                    </ul>
                </div>
            </nav>
        </aside>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger-button" id="hamburger-btn"><iconify-icon icon="mdi:menu"></iconify-icon></button>
                <div class="search-container">
                    <iconify-icon icon="mdi:magnify"></iconify-icon>
                    <input type="text" id="globalSearchInput" placeholder="Search....">
                    <div id="globalSearchResults" class="search-results-dropdown"></div>
                </div>
                <div class="header-profile">
                    <div class="notif-wrapper" id="notif-btn">
                        <iconify-icon icon="mdi:bell-outline" class="bell-icon"></iconify-icon>
                        <?php if ($notifCount > 0): ?>
                            <span class="notif-badge"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
                        <?php endif; ?>

                        <div class="notif-dropdown">
                            <div class="notif-header">Notifications</div>
                            <ul class="notif-list">
                                <?php if ($notifCount > 0): ?>
                                    <?php foreach ($notifications as $notif): ?>
                                        <li class="notif-item">
                                            <div class="notif-icon <?= $notif['type'] == 'overdue' ? 'icon-warn' : 'icon-info' ?>">
                                                <iconify-icon icon="<?= $notif['type'] == 'overdue' ? 'mdi:alert-circle' : 'mdi:account-plus' ?>"></iconify-icon>
                                            </div>
                                            <div class="notif-content">
                                                <p><?= htmlspecialchars($notif['message']) ?></p>
                                                <span class="notif-time">
                                                    <?= $notif['type'] == 'overdue' ? 'Due: ' . date('M d', strtotime($notif['time'])) : date('M d, h:i A', strtotime($notif['time'])) ?>
                                                </span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="notif-item" style="justify-content:center; color:#999;">No new notifications</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>


                    <div class="profile-info">
                        <div class="profile-text">
                            <div class="profile-name">Admin</div>
                            <div class="profile-role">Librarian</div>
                        </div>
                        <a href="profile.php">
                            <div class="profile-icon-wrapper"><iconify-icon icon="mdi:account-tie" class="profile-icon"></iconify-icon></div>
                        </a>
                         <div class="profile-dropdown">
                            <a href="logout.php">
                                <iconify-icon icon="mdi:logout"></iconify-icon> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content-area">
                <div id="view-dashboard">
                    <div class="welcome-card">
                        <h1 class="content-title">Hello, <span class="admin-highlight">Admin</span>!</h1>
                        <p id="datetime-stamp" class="content-subtitle">Loading...</p>
                    </div>

                    <div class="stats-grid">
                        <a href="student.php" class="stat-card" style="text-decoration: none; color: inherit;">
                            <div class="stat-icon icon-blue"><iconify-icon icon="mdi:school"></iconify-icon></div>
                            <div class="stat-details">
                                <p>Total Students</p><span class="stat-number color-blue"><?= $totalStudents ?></span>
                            </div>
                            
                        </a>

                        <a href="faculty.php" class="stat-card" style="text-decoration: none; color: inherit;">
                            <div class="stat-icon icon-cyan-custom"><iconify-icon icon="mdi:account-tie"></iconify-icon></div>
                            <div class="stat-details">
                                <p>Total Faculty</p><span class="stat-number color-cyan"><?= $totalFaculty ?></span>
                            </div>
                            
                        </a>

                        <div class="stat-card">
                            <div class="stat-icon icon-orange"><iconify-icon icon="mdi:layers"></iconify-icon></div>
                            <div class="stat-details">
                                <p>Total Books</p><span class="stat-number color-orange"><?= $totalBooks ?></span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon icon-red"><iconify-icon icon="mdi:alert-circle"></iconify-icon></div>
                            <div class="stat-details">
                                <p>Overdue Books</p><span class="stat-number color-red"><?= $totalOverdue ?></span>
                            </div>
                            
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon icon-indigo"><iconify-icon icon="mdi:book-open-page-variant"></iconify-icon></div>
                            <div class="stat-details">
                                <p>Borrowed Books</p><span class="stat-number color-indigo"><?= $totalBorrowed ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="book-section">
                        <div class="section-header">
                            <h2 class="section-title">Top Available Books</h2>
                            <a href="manbook.php" class="view-all-pill">View All <iconify-icon icon="mdi:arrow-right"></iconify-icon></a>
                        </div>
                        <div class="book-grid">
                            <?php
                            $colors = ['icon-blue', 'icon-green', 'icon-purple'];
                            $i = 0;
                            while ($book = $topBooksQ->fetch_assoc()):
                                $color = $colors[$i % 3];
                                $i++;
                            ?>
                                <div class="book-card">
                                    <div class="book-icon <?= $color ?>"><iconify-icon icon="mdi:book-variant"></iconify-icon></div>
                                    <div class="book-details">
                                        <h3 class="book-title"><?= htmlspecialchars($book['book_title']) ?></h3>
                                        <p class="book-subtitle"><?= $book['total_copies'] ?> copies available</p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <section class="merged-dashboard">
                        <div class="charts-row-1">
                            <div class="card demographics-card">
                                <h2>Visitors Demographics</h2>
                                <div class="demographics-wrapper">
                                    <div class="chart-container">
                                        <canvas id="demographicsChart"></canvas>
                                        <div class="chart-center">
                                            <p>Total Users</p>
                                            <h3><?= $totalStudents + $totalFaculty ?></h3>
                                        </div>
                                    </div>
                                    <div class="info beside">
                                        <ul>
                                            <li><span class="dot" style="background:#9B7EBD; width:10px; height:10px; display:inline-block; margin-right:5px;"></span> Student: <?= $totalStudents ?></li>
                                            <li><span class="dot" style="background:#F49BAB; width:10px; height:10px; display:inline-block; margin-right:5px;"></span> Faculty: <?= $totalFaculty ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="header">
                                    <h2>Statistics by Course</h2>
                                </div>
                                <canvas id="courseChart"></canvas>
                            </div>
                        </div>

                        <div class="books-container">
                            <div class="card chart-card">
                                <h2>Book Trends by Genre</h2>
                                <canvas id="bookTrendsChart"></canvas>
                            </div>

                            <div class="card chart-card">
                                <h2>Book Availability</h2>
                                <div class="chart-container">
                                    <canvas id="booksAvailabilityChart"></canvas>
                                </div>
                                <div class="legend">
                                    <div><span class="dot" style="background:#22c55e; width:10px; height:10px; display:inline-block;"></span> Available: <?= $booksAvailable ?></div>
                                    <div><span class="dot" style="background:#CD5656; width:10px; height:10px; display:inline-block;"></span> Borrowed: <?= $totalBorrowed ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="card recent-activities">
                            <div class="activities-header">
                                <h2>Recent Borrowing Activity</h2>
                                <a href="borrowedhistory.php" class="view-all">View History</a>
                            </div>
                            <ul class="activities-list">
                                <?php if ($activityQ->num_rows > 0): ?>
                                    <?php while ($row = $activityQ->fetch_assoc()): ?>
                                        <li>
                                            <div class="icon-circle book-borrowed"><iconify-icon icon="mdi:book-account"></iconify-icon></div>
                                            <div class="activity-info">
                                                <p><strong>Book Borrowed:</strong> “<?= htmlspecialchars($row['book_title']) ?>”</p>
                                                <span><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></span>
                                            </div>
                                        </li>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <li>
                                        <p>No recent activities.</p>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Sidebar Logic
            const hamburgerBtn = document.getElementById('hamburger-btn');
            const dashboardContainer = document.getElementById('dashboard-container');
            const sidebar = document.getElementById('sidebar');

            if (hamburgerBtn && dashboardContainer && sidebar) {
                hamburgerBtn.addEventListener('click', () => {
                    dashboardContainer.classList.toggle('sidebar-collapsed');
                    if (window.innerWidth <= 992) sidebar.classList.toggle('active');
                });
            }

            const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const parent = this.closest('.nav-dropdown');
                    if (parent) {
                        document.querySelectorAll('.nav-dropdown.active').forEach(open => {
                            if (open !== parent) open.classList.remove('active');
                        });
                        parent.classList.toggle('active');
                    }
                });
            });
            const notifBtn = document.getElementById('notif-btn');
            notifBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notifBtn.classList.toggle('active');
            });

            document.addEventListener('click', (e) => {
                if (!notifBtn.contains(e.target)) {
                    notifBtn.classList.remove('active');
                }
            });

            // Date & Time
            function updateDateTime() {
                const dateTimeElement = document.getElementById('datetime-stamp');
                if (dateTimeElement) {
                    const now = new Date();
                    dateTimeElement.textContent = now.toLocaleString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    });
                }
            }
            setInterval(updateDateTime, 1000);
            updateDateTime();

            // --- INJECT PHP DATA INTO JS FOR CHARTS ---
            const studentCount = <?= $totalStudents ?>;
            const facultyCount = <?= $totalFaculty ?>;
            const courseLabels = <?= json_encode($courseLabels) ?>;
            const courseData = <?= json_encode($courseData) ?>;
            const genreLabels = <?= json_encode($genreLabels) ?>;
            const genreData = <?= json_encode($genreData) ?>;
            const booksAvail = <?= $booksAvailable ?>;
            const booksBorrowed = <?= $totalBorrowed ?>;

            // Chart 1: Demographics
            const ctx1 = document.getElementById('demographicsChart');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'doughnut',
                    data: {
                        labels: ['Student', 'Faculty'],
                        datasets: [{
                            data: [studentCount, facultyCount],
                            backgroundColor: ['#9B7EBD', '#F49BAB'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        cutout: '60%',
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            // Chart 2: Course Stats
            const ctx2 = document.getElementById('courseChart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: courseLabels,
                        datasets: [{
                            label: 'Total Users',
                            data: courseData,
                            backgroundColor: ['#339966', '#CC3333', '#3366CC', '#9966CC', '#eab308']
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        }
                    }
                });
            }

            // Chart 3: Book Trends
            const ctx3 = document.getElementById('bookTrendsChart');
            if (ctx3) {
                new Chart(ctx3, {
                    type: 'bar',
                    data: {
                        labels: genreLabels,
                        datasets: [{
                            data: genreData,
                            backgroundColor: ['#b0b0b0', '#c0c0ff', '#ffcc33', '#ff6666', '#a66bff'],
                            borderRadius: 8
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Chart 4: Availability
            const ctx4 = document.getElementById('booksAvailabilityChart');
            if (ctx4) {
                new Chart(ctx4, {
                    type: 'doughnut',
                    data: {
                        labels: ['Available', 'Borrowed'],
                        datasets: [{
                            data: [booksAvail, booksBorrowed],
                            backgroundColor: ['#22c55e', '#CD5656'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        cutout: '60%',
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('globalSearchInput');
            const resultsContainer = document.getElementById('globalSearchResults');

            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const query = this.value.trim();

                    if (query.length > 1) { // Only search if more than 1 character
                        const formData = new FormData();
                        formData.append('query', query);

                        fetch('search_query.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                resultsContainer.innerHTML = '';

                                if (data.length > 0) {
                                    resultsContainer.style.display = 'block';

                                    data.forEach(item => {
                                        // Determine Icon based on type
                                        let icon = 'mdi:account';
                                        if (item.type === 'Book') icon = 'mdi:book-open-variant';
                                        if (item.type === 'Admin') icon = 'clarity:administrator-solid';

                                        const link = `${item.link}?search=${encodeURIComponent(item.id_val)}`; // Pass ID to page

                                        const html = `
                                <a href="${link}" class="search-result-item">
                                    <div class="result-icon">
                                        <iconify-icon icon="${icon}"></iconify-icon>
                                    </div>
                                    <div class="result-info">
                                        <h4>${item.firstname} ${item.lastname}</h4>
                                        <span>${item.id_val}</span>
                                    </div>
                                    <span class="type-badge badge-${item.type}">${item.type}</span>
                                </a>
                            `;
                                        resultsContainer.innerHTML += html;
                                    });
                                } else {
                                    resultsContainer.style.display = 'block';
                                    resultsContainer.innerHTML = '<div class="search-result-item" style="cursor:default; color:#888;">No results found</div>';
                                }
                            })
                            .catch(error => console.error('Error:', error));
                    } else {
                        resultsContainer.style.display = 'none';
                    }
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                        resultsContainer.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>

</html>