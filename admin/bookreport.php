<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php"); 

// --- 2. DETERMINE DATE RANGE ---
$type = $_GET['type'] ?? 'weekly'; // Default to weekly
$dateInput = $_GET['date'] ?? date('Y-m-d');

$startDate = date('Y-m-d');
$endDate = date('Y-m-d');
$periodLabel = "";

if ($type == 'daily') {
    $startDate = $dateInput;
    $endDate = $dateInput;
    $periodLabel = date('F d, Y', strtotime($startDate));
} elseif ($type == 'weekly') {
    // Last 7 days
    $startDate = date('Y-m-d', strtotime('-7 days'));
    $periodLabel = "Last 7 Days (" . date('M d', strtotime($startDate)) . " - " . date('M d') . ")";
} elseif ($type == 'monthly') {
    // Current Month
    $startDate = date('Y-m-01');
    $periodLabel = date('F Y');
} elseif ($type == 'yearly') {
    // Current Year
    $startDate = date('Y-01-01');
    $periodLabel = "Year " . date('Y');
}

// --- 3. FETCH METRICS ---

// A. Summary Counts (Filtered by Date Range)
// Note: Total Books is usually a static inventory count, but "New Books" depends on date.
$totalBooks = $conn->query("SELECT SUM(total_copies) as c FROM books")->fetch_assoc()['c'] ?? 0;
$newBooks = $conn->query("SELECT COUNT(*) as c FROM books WHERE created_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'")->fetch_assoc()['c'] ?? 0;

$newStudents = $conn->query("SELECT COUNT(*) as c FROM studacc WHERE created_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'")->fetch_assoc()['c'] ?? 0;

$borrowedCount = $conn->query("SELECT COUNT(*) as c FROM borrowing WHERE borrow_date BETWEEN '$startDate' AND '$endDate'")->fetch_assoc()['c'] ?? 0;
$returnedCount = $conn->query("SELECT COUNT(*) as c FROM borrowing WHERE return_date BETWEEN '$startDate' AND '$endDate' AND status = 'Returned'")->fetch_assoc()['c'] ?? 0;
$overdueCount = $conn->query("SELECT COUNT(*) as c FROM borrowing WHERE status = 'Borrowed' AND return_date < CURDATE()")->fetch_assoc()['c'] ?? 0;

// B. Most Borrowed Books
$topBooks = [];
$topQ = $conn->query("
    SELECT bk.book_title, COUNT(b.id) as count 
    FROM borrowing b
    JOIN books bk ON b.accessor_no = bk.accessor_no
    WHERE b.borrow_date BETWEEN '$startDate' AND '$endDate'
    GROUP BY bk.book_title
    ORDER BY count DESC
    LIMIT 5
");
while ($row = $topQ->fetch_assoc()) {
    $topBooks[] = $row;
}

// C. New Students List
$newStudList = [];
$studQ = $conn->query("SELECT firstname, lastname, course, created_at FROM studacc WHERE created_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59' LIMIT 5");
while ($row = $studQ->fetch_assoc()) {
    $newStudList[] = $row;
}

// D. Recent Transactions
$recentTrans = [];
$transQ = $conn->query("
    SELECT b.created_at, b.status, bk.book_title, s.firstname, s.lastname
    FROM borrowing b
    JOIN books bk ON b.accessor_no = bk.accessor_no
    JOIN studacc s ON b.student_number = s.studentnumber
    WHERE b.created_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
    ORDER BY b.created_at DESC
    LIMIT 5
");
while ($row = $transQ->fetch_assoc()) {
    $recentTrans[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Reports | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <?php include 'header.php'; ?>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            color: #1e293b;
            height: 100vh;
            overflow: hidden;
        }

        /* Layout */
        
        /* CONTENT */
        .content-area {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            height: 100%;
        }

        /* Report Specific Styles */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 6px 18px rgba(30, 41, 59, 0.08);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1e293b;
        }

        h1 .iconify {
            font-size: 32px;
            color: #800000;
        }

        .report-filter {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .report-filter select,
        .report-filter input {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }

        .report-filter button {
            background-color: #800000;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .meta {
            color: #6b7280;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card {
            background: #fafcff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            color: #0f172a;
            font-weight: 600;
        }

        td.small {
            color: #6b7280;
            font-size: 13px;
        }

        .fullwidth {
            grid-column: 1 / -1;
        }

        .actions {
            text-align: right;
            margin-top: 20px;
        }

        .actions button {
            background-color: #800000;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Icons */
        .icon-blue {
            color: #2563eb;
        }

        .icon-orange {
            color: #f59e0b;
        }

        .icon-green {
            color: #10b981;
        }

        .icon-purple {
            color: #8b5cf6;
        }

        .icon-yellow {
            color: #eab308;
        }

        /* Print Logic */
        @media print {

            .sidebar,
            .top-header,
            .report-filter,
            .actions {
                display: none !important;
            }

            .dashboard-container {
                display: block;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }

            .container {
                box-shadow: none;
                border: none;
                padding: 0;
            }

            .card {
                border: 1px solid #ccc;
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">

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

                <div class="nav-dropdown active">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:bookshelf"></iconify-icon>
                        <span class="nav-text">Book Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="addbook.php" class="dropdown-link">Add Book</a></li>
                        <li><a href="manbook.php" class="dropdown-link ">Manage Book</a></li>
                        <li><a href="bookreport.php" class="dropdown-link active-page">Book Report</a></li>
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
                <div class="container" id="report-content">
                    <h1><span class="iconify" data-icon="mdi:chart-box-outline"></span> Library Reports</h1>

                    <form class="report-filter" method="GET">
                        <label><span class="iconify" data-icon="mdi:filter-variant"></span> Type:</label>
                        <select name="type" id="report-type" onchange="this.form.submit()">
                            <option value="daily" <?= $type == 'daily' ? 'selected' : '' ?>>Daily Report</option>
                            <option value="weekly" <?= $type == 'weekly' ? 'selected' : '' ?>>Weekly Report</option>
                            <option value="monthly" <?= $type == 'monthly' ? 'selected' : '' ?>>Monthly Report</option>
                            <option value="yearly" <?= $type == 'yearly' ? 'selected' : '' ?>>Yearly Report</option>
                        </select>

                        <?php if ($type == 'daily'): ?>
                            <input type="date" name="date" value="<?= $dateInput ?>" onchange="this.form.submit()">
                        <?php endif; ?>
                    </form>

                    <div class="meta">
                        <span class="iconify" data-icon="mdi:calendar-range"></span> Period: <strong><?= $periodLabel ?></strong> |
                        Generated On: <strong><?= date('F d, Y') ?></strong>
                    </div>

                    <div class="grid">
                        <div class="card">
                            <div class="section-title"><span class="iconify icon-blue" data-icon="mdi:poll"></span> Summary Overview</div>
                            <table>
                                <tr>
                                    <th>Total Books</th>
                                    <td><?= $totalBooks ?></td>
                                    <td class="small">+<?= $newBooks ?> new</td>
                                </tr>
                                <tr>
                                    <th>New Students</th>
                                    <td><?= $newStudents ?></td>
                                    <td class="small">registered</td>
                                </tr>
                                <tr>
                                    <th>Books Borrowed</th>
                                    <td><?= $borrowedCount ?></td>
                                    <td class="small">transactions</td>
                                </tr>
                                <tr>
                                    <th>Books Returned</th>
                                    <td><?= $returnedCount ?></td>
                                    <td class="small">transactions</td>
                                </tr>
                                <tr>
                                    <th>Overdue Books</th>
                                    <td><?= $overdueCount ?></td>
                                    <td class="small" style="color:red">active</td>
                                </tr>
                            </table>
                        </div>

                        <div class="card">
                            <div class="section-title"><span class="iconify icon-orange" data-icon="mdi:book-open-page-variant"></span> Most Borrowed Books</div>
                            <table>
                                <tr>
                                    <th>Book Title</th>
                                    <th>Count</th>
                                </tr>
                                <?php if (empty($topBooks)): ?>
                                    <tr>
                                        <td colspan="2" class="small">No data for this period</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($topBooks as $b): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($b['book_title']) ?></td>
                                            <td><?= $b['count'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </table>
                        </div>

                        <div class="card">
                            <div class="section-title"><span class="iconify icon-green" data-icon="mdi:account-school"></span> New Registered Students</div>
                            <table>
                                <tr>
                                    <th>Name</th>
                                    <th>Course</th>
                                    <th>Date</th>
                                </tr>
                                <?php if (empty($newStudList)): ?>
                                    <tr>
                                        <td colspan="3" class="small">No new registrations</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($newStudList as $s): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($s['firstname'] . ' ' . $s['lastname']) ?></td>
                                            <td><?= $s['course'] ?></td>
                                            <td class="small"><?= date('M d', strtotime($s['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </table>
                        </div>

                        <div class="card">
                            <div class="section-title"><span class="iconify icon-purple" data-icon="mdi:history"></span> Recent Transactions</div>
                            <table>
                                <tr>
                                    <th>Date</th>
                                    <th>Activity</th>
                                    <th>Details</th>
                                </tr>
                                <?php if (empty($recentTrans)): ?>
                                    <tr>
                                        <td colspan="3" class="small">No recent activity</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentTrans as $t): ?>
                                        <tr>
                                            <td class="small"><?= date('M d', strtotime($t['created_at'])) ?></td>
                                            <td><?= ($t['status'] == 'Borrowed' ? 'Borrowed' : 'Returned') ?></td>
                                            <td class="small"><?= htmlspecialchars($t['book_title']) ?> by <?= htmlspecialchars($t['lastname']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </table>
                        </div>

                        <div class="card fullwidth">
                            <div class="section-title"><span class="iconify icon-yellow" data-icon="mdi:lightbulb-on-outline"></span> System Insights</div>
                            <ul>
                                <?php if ($borrowedCount > $returnedCount): ?>
                                    <li>Borrowing activity is higher than returns. Ensure stock availability for popular items.</li>
                                <?php else: ?>
                                    <li>Return rate is healthy. Most books are back in circulation.</li>
                                <?php endif; ?>

                                <?php if ($overdueCount > 5): ?>
                                    <li>⚠️ <strong>Attention:</strong> High number of overdue books (<?= $overdueCount ?>). Consider sending reminders.</li>
                                <?php endif; ?>

                                <?php if ($newStudents > 0): ?>
                                    <li>User base is growing! <?= $newStudents ?> new students joined this period.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="actions">
                        <button id="download-pdf"><span class="iconify" data-icon="mdi:file-pdf-box"></span> Download as PDF</button>
                    </div>

                    <div class="footer">
                        Prepared by: Library Management System (Automated) <span class="iconify" data-icon="mdi:robot-happy-outline"></span>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Dropdown Logic
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

        // PDF Generation
        document.getElementById("download-pdf").addEventListener("click", () => {
            const element = document.getElementById("report-content");
            // Temporarily hide filter buttons for cleaner PDF
            document.querySelector('.report-filter').style.display = 'none';
            document.querySelector('.actions').style.display = 'none';

            const opt = {
                margin: 0.4,
                filename: `Library_Report_<?= $type ?>.pdf`,
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: "in",
                    format: "letter",
                    orientation: "portrait"
                },
            };

            html2pdf().set(opt).from(element).save().then(() => {
                // Restore buttons after download
                document.querySelector('.report-filter').style.display = 'flex';
                document.querySelector('.actions').style.display = 'block';
            });
        });
    </script>

    <script src="search.js"></script>

</body>

</html>