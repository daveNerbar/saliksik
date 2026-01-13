<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php");

// --- 2. HANDLE FORM SUBMISSIONS (ADD & DELETE) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // SAVE ANNOUNCEMENT
    if (isset($_POST['action']) && $_POST['action'] == 'save_announcement') {
        $title = $conn->real_escape_string($_POST['title']);
        $type = $conn->real_escape_string($_POST['type']);
        $content = $conn->real_escape_string($_POST['content']);
        $start = $_POST['start_date'];
        $end = $_POST['end_date'];
        $featured = isset($_POST['featured']) ? 1 : 0;

        $sql = "INSERT INTO announcements (title, type, content, start_date, end_date, is_featured) 
                VALUES ('$title', '$type', '$content', '$start', '$end', $featured)";
        $conn->query($sql);
        header("Location: annceve.php");
        exit();
    }

    // SAVE EVENT
    if (isset($_POST['action']) && $_POST['action'] == 'save_event') {
        $title = $conn->real_escape_string($_POST['title']);
        $category = $conn->real_escape_string($_POST['category']);
        $desc = $conn->real_escape_string($_POST['description']);
        $date = $_POST['event_date'];
        $time = $_POST['event_time'];
        $loc = $conn->real_escape_string($_POST['location']);
        $cap = (int)$_POST['capacity'];
        $reg = isset($_POST['registration']) ? 1 : 0;

        $sql = "INSERT INTO events (title, category, description, event_date, event_time, location, capacity, is_registration_required) 
                VALUES ('$title', '$category', '$desc', '$date', '$time', '$loc', $cap, $reg)";
        $conn->query($sql);
        header("Location: annceve.php?tab=events");
        exit();
    }

    // DELETE ITEM
    if (isset($_POST['action']) && $_POST['action'] == 'delete_item') {
        $id = (int)$_POST['id'];
        $table = ($_POST['type'] == 'event') ? 'events' : 'announcements';
        $conn->query("DELETE FROM $table WHERE id=$id");
        $redirectTab = ($_POST['type'] == 'event') ? 'events' : 'announcements';
        header("Location: annceve.php?tab=$redirectTab");
        exit();
    }
}

// --- 3. FETCH DATA ---
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
$events = $conn->query("SELECT * FROM events ORDER BY event_date ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Announcements | Saliksik</title>
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://kit.fontawesome.com/19d37dc8d9.js" crossorigin="anonymous"></script>
    <?php include 'header.php'; ?>

    <style>
        /* [KEEP YOUR EXISTING CSS EXACTLY AS IT IS] */
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            color: #374151;
            height: 100vh;
            overflow: hidden;
        }

        :root {
            --primary-red: #992222;
            --border-color: #ddd;
        }



        /* CONTENT */
        .content-area {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            height: 100%;
        }

        /* Announcement Specific Styles */
        .announcement-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            padding: 30px;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid #e0e0e0;
        }

        .page-top-controls {
            flex-shrink: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .tabs {
            background: #f0f0f0;
            display: inline-flex;
            border-radius: 8px;
            padding: 4px;
            margin-bottom: 15px;
        }

        .tab-btn {
            border: none;
            background: transparent;
            padding: 8px 20px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 6px;
            color: #555;
            transition: 0.2s;
        }

        .tab-btn:hover {
            background: #e0e0e0;
        }

        .tab-btn.active {
            background: #fff;
            color: var(--primary-red);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .controls-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            color: #1f2937;
        }

        .btn-primary {
            background-color: var(--primary-red);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background-color: #7a1515;
        }

        .content-list {
            flex: 1;
            overflow-y: auto;
            padding-right: 5px;
            margin-top: 10px;
        }

        .card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 5px solid var(--primary-red);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 20px;
            background: #fff;
        }

        .card-content {
            flex: 1;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 10px 0;
            display: flex;
            justify-content: space-between;
            color: #333;
        }

        .card-actions i {
            color: #aaa;
            margin-left: 10px;
            cursor: pointer;
            transition: 0.2s;
        }

        .card-actions i:hover {
            color: var(--primary-red);
        }

        .tag {
            background: #ffebeb;
            color: var(--primary-red);
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .card-text {
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .card-meta {
            color: #888;
            font-size: 0.8rem;
            display: flex;
            gap: 20px;
            align-items: center;
            font-weight: 500;
            flex-wrap: wrap;
        }

        .date-box {
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 65px;
            height: 65px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #fafafa;
        }

        .date-month {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary-red);
            text-transform: uppercase;
        }

        .date-day {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
            line-height: 1;
        }

        /* Modals & Forms */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5vh auto;
            padding: 30px;
            border-radius: 12px;
            width: 600px;
            max-width: 90%;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 13px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
        }

        .row-group {
            display: flex;
            gap: 20px;
        }

        .half {
            flex: 1;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .btn-cancel {
            background: white;
            border: 1px solid #ddd;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            color: #555;
        }

        .btn-save {
            background: var(--primary-red);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        /* Header Stuff */
        .hamburger-button {
            background: none;
            border: none;
            cursor: pointer;
            color: #4b5563;
            padding: 0.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hamburger-button iconify-icon {
            font-size: 1.5rem;
        }

        .search-container {
            display: flex;
            align-items: center;
            color: #9ca3af;
            background-color: white;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            width: 100%;
            max-width: 400px;
        }

        .search-container input {
            border: none;
            outline: none;
            font-size: 0.9rem;
            margin-left: 0.5rem;
            width: 100%;
        }

        .header-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
        }

        .profile-text {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .profile-name {
            font-weight: 500;
            color: #820000;
        }

        .profile-role {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .profile-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 50%;
        }

        .profile-icon {
            font-size: 1.75rem;
            color: #4b5563;
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
                <a href="dashboard.php" class="nav-link ">
                    <iconify-icon icon="mdi:view-dashboard"></iconify-icon>
                    <span class="nav-text">Dashboard</span>
                </a>

                <div class="nav-dropdown ">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:account-group"></iconify-icon>
                        <span class="nav-text">User Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="student.php" class="dropdown-link ">Student</a></li>
                        <li><a href="faculty.php" class="dropdown-link">Faculty</a></li>
                        <li><a href="userreport.php" class="dropdown-link">User Report</a></li>
                    </ul>
                </div>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:bookshelf"></iconify-icon>
                        <span class="nav-text">Book Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="addbook.php" class="dropdown-link">Add Book</a></li>
                        <li><a href="manbook.php" class="dropdown-link">Manage Book</a></li>
                        <li><a href="bookreportt.php" class="dropdown-link">Book Report</a></li>
                        <li><a href="unusedbooks.php" class="dropdown-link">Unused Books</a></li>
                    </ul>
                </div>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:swap-horizontal"></iconify-icon>
                        <span class="nav-text">Borrowing Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="bookborrow.php" class="dropdown-link">Add Borrow</a></li>
                        <li><a href="borrowedlist.php" class="dropdown-link">Return Book</a></li>
                        <li><a href="reservebooks.php" class="dropdown-link">Reserved Book</a></li>
                        <li><a href="borrowedhistory.php" class="dropdown-link">Borrowed History</a></li>
                        <li><a href="borrowedreport.php" class="dropdown-link ">Borrowed Report</a></li>
                    </ul>
                </div>

                <a href="annceve.php" class="nav-link active"><iconify-icon icon="mdi:bullhorn"></iconify-icon><span
                        class="nav-text">Announcements & Events</span></a>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="clarity:administrator-solid"></iconify-icon>
                        <span class="nav-text">Admin Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="addadmin.php" class="dropdown-link">Add Administrator</a></li>
                        <li><a href="addminlist.php" class="dropdown-link">Administrator List</a></li>
                    </ul>
                </div>
            </nav>
        </aside>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger-button" id="hamburger-btn">
                    <iconify-icon icon="mdi:menu"></iconify-icon>
                </button>

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
                            <div class="profile-icon-wrapper">
                                <iconify-icon icon="mdi:account-tie" class="profile-icon"></iconify-icon>
                            </div>
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
                <div class="announcement-container">

                    <div class="page-top-controls">
                        <div class="tabs">
                            <button class="tab-btn active" id="tab-ann" onclick="switchTab('announcements')">Announcements</button>
                            <button class="tab-btn" id="tab-evt" onclick="switchTab('events')">Events</button>
                        </div>

                        <div class="controls-header">
                            <h2 id="header-title" class="page-title">Manage Announcements</h2>
                            <button class="btn-primary" id="addBtn" onclick="openModal()">+ New Announcement</button>
                        </div>
                    </div>

                    <div id="announcement-list" class="content-list">
                        <?php while ($row = $announcements->fetch_assoc()): ?>
                            <div class="card">
                                <div class="card-content">
                                    <div class="card-title">
                                        <?= htmlspecialchars($row['title']) ?>
                                        <div class="card-actions">
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this announcement?');">
                                                <input type="hidden" name="action" value="delete_item">
                                                <input type="hidden" name="type" value="announcement">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" style="background:none;border:none;cursor:pointer;"><i class="fa-regular fa-trash-can" style="color:#888;"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                    <span class="tag"><?= htmlspecialchars($row['type']) ?></span>
                                    <p class="card-text"><?= htmlspecialchars($row['content']) ?></p>
                                    <div class="card-meta">
                                        <span>Active: <?= date('M d', strtotime($row['start_date'])) ?> - <?= date('M d, Y', strtotime($row['end_date'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div id="event-list" class="content-list" style="display:none;">
                        <?php while ($row = $events->fetch_assoc()):
                            $dateObj = strtotime($row['event_date']);
                            $month = date('M', $dateObj);
                            $day = date('d', $dateObj);
                        ?>
                            <div class="card">
                                <div class="date-box">
                                    <span class="date-month"><?= $month ?></span>
                                    <span class="date-day"><?= $day ?></span>
                                </div>
                                <div class="card-content">
                                    <div class="card-title">
                                        <?= htmlspecialchars($row['title']) ?>
                                        <div class="card-actions">
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this event?');">
                                                <input type="hidden" name="action" value="delete_item">
                                                <input type="hidden" name="type" value="event">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" style="background:none;border:none;cursor:pointer;"><i class="fa-regular fa-trash-can" style="color:#888;"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                    <span class="tag"><?= htmlspecialchars($row['category']) ?></span>
                                    <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                                    <div class="card-meta">
                                        <span><i class="fa-regular fa-clock"></i> <?= date('h:i A', strtotime($row['event_time'])) ?></span>
                                        <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($row['location']) ?></span>
                                        <span><i class="fa-solid fa-user-group"></i> Cap: <?= $row['capacity'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <div id="announcementModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New Announcement</h3>
                <span class="close" onclick="closeModals()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="save_announcement">
                <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
                <div class="form-group"><label>Type</label><input type="text" name="type" placeholder="General, Urgent..."></div>
                <div class="form-group"><label>Content</label><textarea name="content" rows="4" required></textarea></div>
                <div class="row-group">
                    <div class="form-group half"><label>Start Date</label><input type="date" name="start_date" required></div>
                    <div class="form-group half"><label>End Date</label><input type="date" name="end_date" required></div>
                </div>
                <div class="form-group"><label><input type="checkbox" name="featured"> Feature this announcement</label></div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModals()">Cancel</button>
                    <button type="submit" class="btn-save">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="eventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New Event</h3>
                <span class="close" onclick="closeModals()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="save_event">
                <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
                <div class="form-group"><label>Category</label><input type="text" name="category"></div>
                <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
                <div class="row-group">
                    <div class="form-group half"><label>Date</label><input type="date" name="event_date" required></div>
                    <div class="form-group half"><label>Time</label><input type="time" name="event_time" required></div>
                </div>
                <div class="form-group"><label>Location</label><input type="text" name="location" required></div>
                <div class="form-group"><label>audience or participants</label><input type="number" name="capacity"></div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModals()">Cancel</button>
                    <button type="submit" class="btn-save">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentTab = 'announcements';

        // Check URL for tab parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'events') {
            switchTab('events');
        }

        function switchTab(tab) {
            currentTab = tab;
            const annList = document.getElementById('announcement-list');
            const evtList = document.getElementById('event-list');
            const addBtn = document.getElementById('addBtn');
            const headerTitle = document.getElementById('header-title');
            const tabAnn = document.getElementById('tab-ann');
            const tabEvt = document.getElementById('tab-evt');

            if (tab === 'announcements') {
                annList.style.display = 'block';
                evtList.style.display = 'none';
                addBtn.innerText = "+ New Announcement";
                headerTitle.innerText = "Manage Announcements";
                tabAnn.classList.add('active');
                tabEvt.classList.remove('active');
            } else {
                annList.style.display = 'none';
                evtList.style.display = 'block';
                addBtn.innerText = "+ New Event";
                headerTitle.innerText = "Manage Events";
                tabAnn.classList.remove('active');
                tabEvt.classList.add('active');
            }
        }

        function openModal() {
            if (currentTab === 'announcements') {
                document.getElementById('announcementModal').style.display = 'block';
            } else {
                document.getElementById('eventModal').style.display = 'block';
            }
        }

        function closeModals() {
            document.getElementById('announcementModal').style.display = 'none';
            document.getElementById('eventModal').style.display = 'none';
        }

        // Sidebar Toggles
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
    </script>

    <script src="search.js"></script>

</body>

</html>