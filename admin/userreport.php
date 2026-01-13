<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php");

// --- 2. FETCH STUDENTS ---
$students = [];
$sqlStud = "SELECT * FROM studacc";
$resStud = $conn->query($sqlStud);

while ($row = $resStud->fetch_assoc()) {
    $studNum = $row['studentnumber'];

    // Get Borrowing Stats for this student
    $borrowStats = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status='Borrowed' THEN 1 ELSE 0 END) as current,
        SUM(CASE WHEN status='Returned' THEN 1 ELSE 0 END) as returned,
        SUM(CASE WHEN status='Borrowed' AND return_date < CURDATE() THEN 1 ELSE 0 END) as overdue
    FROM borrowing WHERE student_number = '$studNum'")->fetch_assoc();

    // Get Recent History
    $history = [];
    $histQ = $conn->query("SELECT created_at, status, accessor_no FROM borrowing WHERE student_number = '$studNum' ORDER BY created_at DESC LIMIT 3");
    while ($h = $histQ->fetch_assoc()) {
        $history[] = [
            'date' => date('M d', strtotime($h['created_at'])),
            'activity' => ($h['status'] == 'Borrowed' ? 'Book Borrowed' : 'Book Returned'),
            'details' => $h['accessor_no']
        ];
    }

    $students[] = [
        'name' => $row['firstname'] . ' ' . $row['lastname'],
        'id' => $row['studentnumber'],
        'dept' => $row['course'],
        'section' => $row['section'],
        'corStatus' => !empty($row['pdf_file']) ? 'Verified' : 'Missing',
        'stats' => [
            'total' => (int)$borrowStats['total'],
            'current' => (int)$borrowStats['current'],
            'returned' => (int)$borrowStats['returned'],
            'overdue' => (int)$borrowStats['overdue']
        ],
        'history' => $history,
        'alerts' => ((int)$borrowStats['overdue'] > 0) ? [['book' => 'Check Borrowing History', 'days' => 'Overdue', 'date' => 'Now']] : []
    ];
}

// --- 3. FETCH FACULTY ---
$faculty = [];
$sqlFac = "SELECT * FROM facultyacc";
$resFac = $conn->query($sqlFac);

while ($row = $resFac->fetch_assoc()) {
    $faculty[] = [
        'name' => $row['firstname'] . ' ' . $row['lastname'],
        'id' => $row['pupid'],
        'dept' => $row['department'],
        'section' => 'N/A',
        'corStatus' => 'N/A',
        'stats' => ['total' => 0, 'current' => 0, 'returned' => 0, 'overdue' => 0],
        'history' => [],
        'alerts' => []
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Report | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

    <?php include 'header.php'; ?>

    <style>
        /* =========================================
           1. DASHBOARD LAYOUT STYLES
           ========================================= */
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

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



        /* CONTENT */
        .content-area {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            height: 100%;
        }

        /* =========================================
           2. REPORT GENERATOR STYLES
           ========================================= */
        .report-container {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(30, 41, 59, 0.08);
        }

        /* Control Panel */
        .control-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 25px;
            padding-bottom: 25px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-left h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }

        .header-left p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 2px;
        }

        .search-section {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
        }

        .type-selector {
            display: flex;
            gap: 15px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        .search-input {
            padding: 10px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            width: 280px;
            font-size: 14px;
        }

        .btn-generate,
        .btn-pdf {
            background-color: #800000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-generate:hover,
        .btn-pdf:hover {
            background-color: #600000;
        }

        .action-area {
            text-align: right;
            margin-top: 20px;
        }

        /* Report View */
        .report-view {
            animation: slideUp 0.4s ease-out;
        }

        .hidden {
            display: none;
        }

        .report-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 20px;
        }

        .type-badge {
            background-color: #800000;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .report-card {
            background: #fafcff;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            min-height: 320px;
            display: flex;
            flex-direction: column;
        }

        .report-card h3 {
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .profile-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .profile-label {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        .profile-value {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            text-align: right;
        }

        .simple-table,
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .simple-table td,
        .transaction-table td {
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .transaction-table th {
            text-align: left;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 600;
            padding-bottom: 10px;
        }

        .danger-text {
            color: #ef4444;
            font-weight: 600;
            text-align: right;
        }

        .text-right {
            text-align: right;
        }

        .alert-item {
            background-color: #fff;
            border-left: 4px solid #ef4444;
            padding: 12px;
            margin-bottom: 10px;
            font-size: 0.9rem;
            border-radius: 0 4px 4px 0;
            color: #7f1d1d;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .summary-rectangle {
            grid-column: span 2;
            background: #fafcff;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            border-left: 5px solid #800000;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Icons */
        .icon-lg {
            font-size: 32px;
        }

        .icon-maroon {
            color: #800000;
        }

        .icon-blue {
            color: #2563eb;
        }

        .icon-orange {
            color: #f59e0b;
        }

        .icon-red {
            color: #ef4444;
        }

        .icon-purple {
            color: #8b5cf6;
        }

        /* PRINT STYLES - Hides Sidebar/Header */
        @media print {

            .sidebar,
            .top-header,
            .control-panel,
            .action-area {
                display: none !important;
            }

            .dashboard-container {
                display: block;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }

            .content-area {
                padding: 0;
                overflow: visible;
            }

            .report-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }

            #reportView {
                display: block !important;
            }

            .report-card,
            .summary-rectangle {
                border: 1px solid #ccc;
                box-shadow: none;
                break-inside: avoid;
            }
        }

        /* Mobile */
        @media (max-width: 992px) {
            .sidebar {
                left: -300px;
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar.mobile-active {
                left: 0;
            }

            .control-panel {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-section {
                width: 100%;
                align-items: flex-start;
            }

            .grid-container {
                grid-template-columns: 1fr;
            }

            .summary-rectangle {
                grid-column: span 1;
            }
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

                <div class="nav-dropdown active">
                    <a href="dashboard.php" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:account-group"></iconify-icon>
                        <span class="nav-text">User Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="student.php" class="dropdown-link ">Student</a></li>
                        <li><a href="faculty.php" class="dropdown-link ">Faculty</a></li>
                        <li><a href="userreport.php" class="dropdown-link active-page">User Report</a></li>
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
                        <li><a href="bookreport.php" class="dropdown-link">Book Report</a></li>
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
                        <li><a href="borrowedreport.php" class="dropdown-link">Borrowed Report</a></li>
                    </ul>
                </div>

                <a href="annceve.php" class="nav-link"><iconify-icon icon="mdi:bullhorn"></iconify-icon><span
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
                <div class="report-container">

                    <div class="control-panel">
                        <div class="header-left">
                            <span class="iconify icon-maroon icon-lg" data-icon="mdi:account-search-outline"></span>
                            <div>
                                <h2>User Report Generator</h2>
                                <p>Select user type and enter details.</p>
                            </div>
                        </div>

                        <div class="search-section">
                            <div class="type-selector">
                                <label><input type="radio" name="userType" value="student" checked onchange="updateSearchList()"> Student</label>
                                <label><input type="radio" name="userType" value="faculty" onchange="updateSearchList()"> Faculty</label>
                            </div>
                            <div class="input-group">
                                <input type="text" id="searchInput" class="search-input" list="namesList" placeholder="Search Name or ID..." autocomplete="off">
                                <datalist id="namesList"></datalist>
                                <button class="btn-generate" onclick="generateReport()">Generate</button>
                            </div>
                        </div>
                    </div>

                    <div id="reportView" class="report-view hidden">
                        <div class="report-meta">
                            <span id="reportTypeBadge" class="type-badge">Student Report</span>
                            <span> | <span class="iconify" data-icon="mdi:calendar-range"></span> Period: Current Semester | Generated On: <span id="metaDate"></span></span>
                        </div>

                        <div class="grid-container">
                            <div class="report-card">
                                <h3><span class="iconify icon-blue" data-icon="mdi:account-details"></span> User Profile</h3>
                                <div class="profile-row">
                                    <div class="profile-item"><span class="profile-label">ID No.</span><span class="profile-value" id="pId">--</span></div>
                                    <div class="profile-item"><span class="profile-label">Name</span><span class="profile-value" id="pName">--</span></div>
                                    <div class="profile-item"><span class="profile-label" id="labelDept">Course</span><span class="profile-value" id="pCourse">--</span></div>
                                    <div class="profile-item"><span class="profile-label">Section</span><span class="profile-value" id="pSection">--</span></div>
                                    <div class="profile-item"><span class="profile-label">COR Status</span><span class="profile-value" id="pCOR">--</span></div>
                                    <div class="profile-item"><span class="profile-label">Active Loans</span><span class="profile-value" id="pBorrowedCount">0</span></div>
                                </div>
                            </div>

                            <div class="report-card">
                                <h3><span class="iconify icon-orange" data-icon="mdi:chart-bar"></span> Borrowing Statistics</h3>
                                <table class="simple-table">
                                    <thead>
                                        <tr>
                                            <th>Metric</th>
                                            <th style="text-align: right;">Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Total Borrowed (Lifetime)</td>
                                            <td class="text-right" id="statTotal">0</td>
                                        </tr>
                                        <tr>
                                            <td>Currently Borrowed</td>
                                            <td class="text-right" id="statCurrent">0</td>
                                        </tr>
                                        <tr>
                                            <td>Books Returned</td>
                                            <td class="text-right" id="statReturned">0</td>
                                        </tr>
                                        <tr style="background-color:#fff1f2;">
                                            <td><strong>Overdue Items</strong></td>
                                            <td class="text-right danger-text" id="statOverdue">0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="report-card">
                                <h3><span class="iconify icon-red" data-icon="mdi:alert-circle-outline"></span> Active Alerts</h3>
                                <div id="alertContainer"></div>
                            </div>

                            <div class="report-card">
                                <h3><span class="iconify icon-purple" data-icon="mdi:history"></span> Recent Transactions</h3>
                                <table class="transaction-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Activity</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody id="historyTable"></tbody>
                                </table>
                            </div>

                            <div class="summary-rectangle">
                                <h3><span class="iconify" data-icon="mdi:text-box-check-outline"></span> Report Summary & Insights</h3>
                                <ul id="insightsList"></ul>
                            </div>
                        </div>

                        <div class="action-area">
                            <button class="btn-pdf" onclick="printReport()"><span class="iconify" data-icon="mdi:file-pdf-box"></span> Download as PDF</button>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script>
        // --- INJECT PHP DATA INTO JS ---
        const studentDB = <?php echo json_encode($students); ?>;
        const facultyDB = <?php echo json_encode($faculty); ?>;

        function getUserType() {
            return document.querySelector('input[name="userType"]:checked').value;
        }

        // --- SIDEBAR LOGIC ---
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
        // --- REPORT LOGIC ---
        function updateSearchList() {
            const list = document.getElementById('namesList');
            const type = getUserType();
            const db = type === 'student' ? studentDB : facultyDB;
            document.getElementById('searchInput').value = '';
            list.innerHTML = '';
            db.forEach(user => {
                const optName = document.createElement('option');
                optName.value = user.name;
                list.appendChild(optName);
                const optID = document.createElement('option');
                optID.value = user.id;
                list.appendChild(optID);
            });
        }

        function generateReport() {
            const inputValue = document.getElementById('searchInput').value.trim().toLowerCase();
            const type = getUserType();
            const db = type === 'student' ? studentDB : facultyDB;

            const user = db.find(u => u.name.toLowerCase() === inputValue || u.id.toLowerCase() === inputValue);

            if (!user) {
                alert("User not found!");
                return;
            }

            const labelDept = document.getElementById('labelDept');
            const badge = document.getElementById('reportTypeBadge');

            if (type === 'student') {
                labelDept.textContent = "Course";
                badge.textContent = "Student Report";
                badge.style.backgroundColor = "#800000";
            } else {
                labelDept.textContent = "Department";
                badge.textContent = "Faculty Report";
                badge.style.backgroundColor = "#7c3aed";
            }

            document.getElementById('pId').textContent = user.id;
            document.getElementById('pName').textContent = user.name;
            document.getElementById('pCourse').textContent = user.dept;
            document.getElementById('pSection').textContent = user.section;
            document.getElementById('pBorrowedCount').textContent = user.stats.current;

            const corElement = document.getElementById('pCOR');
            if (user.corStatus === "Verified") corElement.innerHTML = `<span style="color:#10b981;"><iconify-icon icon="mdi:check-circle"></iconify-icon> Verified</span>`;
            else if (user.corStatus === "Missing") corElement.innerHTML = `<span style="color:#ef4444;"><iconify-icon icon="mdi:alert-circle"></iconify-icon> Missing</span>`;
            else corElement.textContent = "N/A";

            document.getElementById('statTotal').textContent = user.stats.total;
            document.getElementById('statCurrent').textContent = user.stats.current;
            document.getElementById('statReturned').textContent = user.stats.returned;
            document.getElementById('statOverdue').textContent = user.stats.overdue;

            const alertBox = document.getElementById('alertContainer');
            alertBox.innerHTML = '';
            if (user.alerts.length > 0) {
                user.alerts.forEach(a => {
                    alertBox.innerHTML += `<div class="alert-item"><strong>Warning:</strong> ${a.book}<br><small>${a.days}</small></div>`;
                });
            } else {
                alertBox.innerHTML = '<div style="color:#10b981; font-weight:500;">No active alerts.</div>';
            }

            const table = document.getElementById('historyTable');
            table.innerHTML = '';
            if (user.history.length > 0) {
                user.history.forEach(h => {
                    table.innerHTML += `<tr><td>${h.date}</td><td>${h.activity}</td><td>${h.details}</td></tr>`;
                });
            } else {
                table.innerHTML += `<tr><td colspan="3" style="text-align:center; color:#999;">No recent transactions</td></tr>`;
            }

            const insights = document.getElementById('insightsList');
            insights.innerHTML = '';
            if (user.stats.overdue > 0) insights.innerHTML += `<li>⚠️ <strong>Action Required:</strong> User has overdue items. Borrowing suspended.</li>`;
            else insights.innerHTML += `<li>✅ <strong>Clearance:</strong> Account is in good standing.</li>`;
            if (user.stats.total > 5) insights.innerHTML += `<li>📚 <strong>Top Reader:</strong> This user is a frequent visitor.</li>`;

            document.getElementById('reportView').classList.remove('hidden');
            document.getElementById('metaDate').textContent = new Date().toLocaleDateString();
        }

        function printReport() {
            window.print();
        }
        updateSearchList();
    </script>
    <script src="search.js"></script>

</body>

</html>