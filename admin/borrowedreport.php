<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php");

// --- 2. FETCH ALL BORROWED HISTORY ---
// We fetch everything, then filter on the client-side (JS) for smoother UX
// Or you can filter via SQL if the dataset is huge (server-side). For now, client-side is faster for <10k records.
$sql = "SELECT 
            b.id,
            b.accessor_no,
            b.student_number,
            COALESCE(CONCAT(s.lastname, ', ', s.firstname), CONCAT(f.lastname, ', ', f.firstname)) AS borrower_name,
            COALESCE(s.course, f.department) AS course_dept,
            bk.book_title,
            b.borrow_date,
            b.return_date,
            b.status
        FROM borrowing b
        LEFT JOIN studacc s ON b.student_number = s.studentnumber
        LEFT JOIN facultyacc f ON b.student_number = f.pupid
        LEFT JOIN books bk ON b.accessor_no = bk.accessor_no
        ORDER BY b.borrow_date DESC";

$result = $conn->query($sql);
$reportData = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reportData[] = [
            'accessor_no' => $row['accessor_no'],
            'student_id' => $row['student_number'],
            'name' => $row['borrower_name'],
            'course' => $row['course_dept'],
            'title' => $row['book_title'],
            'borrow_date' => $row['borrow_date'], // Keep YYYY-MM-DD format for JS filtering
            'return_date' => $row['return_date'],
            'status' => $row['status']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books Report | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>

    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

    <?php include 'header.php'; ?>

    <style>
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

        .content-area {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            height: 100%;
        }

        .report-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            padding: 30px;
            display: flex;
            flex-direction: column;
            border: 1px solid #e0e0e0;
            min-height: 80vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            color: #1f2937;
        }

        /* Controls Section */
        .controls-wrapper {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: end;
            margin-bottom: 20px;
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .control-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .control-group label {
            font-size: 13px;
            font-weight: 600;
            color: #555;
        }

        .control-group input,
        .control-group select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
        }

        .quick-filters {
            display: flex;
            gap: 10px;
        }

        .btn-filter {
            padding: 8px 15px;
            border: 1px solid #d1d5db;
            background: #fff;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: 0.2s;
        }

        .btn-filter:hover,
        .btn-filter.active {
            background: #550000;
            color: white;
            border-color: #550000;
        }

        .btn-export {
            background-color: #16a34a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
            /* Push to right */
        }

        .btn-export:hover {
            background-color: #15803d;
        }

        /* Table */
        .table-container {
            flex: 1;
            overflow: auto;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        th,
        td {
            padding: 12px 15px;
            font-size: 13px;
            border-bottom: 1px solid #eee;
            color: #333;
            text-align: left;
        }

        thead th {
            background-color: #e6e6e6;
            font-weight: 700;
            position: sticky;
            top: 0;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-Borrowed {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-Returned {
            background: #dcfce7;
            color: #16a34a;
        }

        .summary-stats {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            font-weight: 600;
            font-size: 14px;
            color: #555;
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
                        <li><a href="bookreport.php" class="dropdown-link">Book Report</a></li>
                        <li><a href="unusedbooks.php" class="dropdown-link">Unused Books</a></li>
                    </ul>
                </div>

                <div class="nav-dropdown active">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:swap-horizontal"></iconify-icon>
                        <span class="nav-text">Borrowing Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="bookborrow.php" class="dropdown-link ">Add Borrow</a></li>
                        <li><a href="borrowedlist.php" class="dropdown-link">Return Book</a></li>
                        <li><a href="reservebooks.php" class="dropdown-link">Reserved Book</a></li>
                        <li><a href="borrowedhistory.php" class="dropdown-link ">Borrowed History</a></li>
                        <li><a href="borrowedreport.php" class="dropdown-link active-page">Borrowed Report</a></li>

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
                    <div class="page-header">
                        <h1 class="page-title">Generate Report</h1>
                        <button class="btn-export" onclick="exportTableToExcel()">
                            <iconify-icon icon="mdi:microsoft-excel"></iconify-icon> Export to Excel
                        </button>
                    </div>

                    <div class="controls-wrapper">
                        <div class="control-group">
                            <label>Quick Filters:</label>
                            <div class="quick-filters">
                                <button class="btn-filter" onclick="setFilter('today')">Today</button>
                                <button class="btn-filter" onclick="setFilter('week')">This Week</button>
                                <button class="btn-filter" onclick="setFilter('month')">This Month</button>
                                <button class="btn-filter" onclick="setFilter('year')">This Year</button>
                                <button class="btn-filter active" onclick="setFilter('all')">All Time</button>
                            </div>
                        </div>

                        <div class="control-group">
                            <label>From Date:</label>
                            <input type="date" id="startDate">
                        </div>

                        <div class="control-group">
                            <label>To Date:</label>
                            <input type="date" id="endDate">
                        </div>

                        <div class="control-group">
                            <label>Status:</label>
                            <select id="statusFilter">
                                <option value="All">All Status</option>
                                <option value="Borrowed">Borrowed Only</option>
                                <option value="Returned">Returned Only</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-container">
                        <table id="reportTable">
                            <thead>
                                <tr>
                                    <th>Accessor No.</th>
                                    <th>Borrower ID</th>
                                    <th>Borrower Name</th>
                                    <th>Course/Dept</th>
                                    <th>Book Title</th>
                                    <th>Borrow Date</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                            </tbody>
                        </table>
                    </div>

                    <div class="summary-stats">
                        <span>Total Records: <span id="totalCount">0</span></span>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="search.js"></script>

    <script>
        // --- 1. INJECT PHP DATA ---
        const rawData = <?php echo json_encode($reportData); ?>;

        // --- 2. DATE FILTER LOGIC ---
        function setFilter(type) {
            const today = new Date();
            const startInput = document.getElementById('startDate');
            const endInput = document.getElementById('endDate');

            // Reset active buttons
            document.querySelectorAll('.btn-filter').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            let startDate = new Date();

            switch (type) {
                case 'today':
                    // Start date is today
                    break;
                case 'week':
                    // Go back 7 days
                    startDate.setDate(today.getDate() - 7);
                    break;
                case 'month':
                    // Go back 30 days or set to 1st of month
                    startDate.setDate(1);
                    break;
                case 'year':
                    startDate.setMonth(0);
                    startDate.setDate(1);
                    break;
                case 'all':
                    startDate = null;
                    break;
            }

            if (startDate) {
                startInput.value = startDate.toISOString().split('T')[0];
                endInput.value = today.toISOString().split('T')[0];
            } else {
                startInput.value = '';
                endInput.value = '';
            }

            applyFilters();
        }

        // --- 3. MAIN FILTER FUNCTION ---
        function applyFilters() {
            const startDateVal = document.getElementById('startDate').value;
            const endDateVal = document.getElementById('endDate').value;
            const statusVal = document.getElementById('statusFilter').value;
            const tableBody = document.getElementById('tableBody');

            tableBody.innerHTML = '';
            let count = 0;

            const filtered = rawData.filter(row => {
                // 1. Date Check
                // Note: row.borrow_date format is YYYY-MM-DD HH:MM:SS. We only need YYYY-MM-DD
                const borrowDate = row.borrow_date.split(' ')[0];

                let dateMatch = true;
                if (startDateVal && borrowDate < startDateVal) dateMatch = false;
                if (endDateVal && borrowDate > endDateVal) dateMatch = false;

                // 2. Status Check
                let statusMatch = true;
                if (statusVal !== 'All' && row.status !== statusVal) statusMatch = false;

                return dateMatch && statusMatch;
            });

            // Render Rows
            filtered.forEach(row => {
                count++;
                const tr = document.createElement('tr');
                const returnDateDisplay = row.return_date ? row.return_date : '-';

                tr.innerHTML = `
                    <td>${row.accessor_no}</td>
                    <td>${row.student_id}</td>
                    <td>${row.name}</td>
                    <td>${row.course}</td>
                    <td>${row.title}</td>
                    <td>${row.borrow_date}</td>
                    <td>${returnDateDisplay}</td>
                    <td><span class="status-badge status-${row.status}">${row.status}</span></td>
                `;
                tableBody.appendChild(tr);
            });

            document.getElementById('totalCount').textContent = count;
        }

        // Event Listeners for custom inputs
        document.getElementById('startDate').addEventListener('change', applyFilters);
        document.getElementById('endDate').addEventListener('change', applyFilters);
        document.getElementById('statusFilter').addEventListener('change', applyFilters);

        // Initial Load
        applyFilters();

        // --- 4. EXCEL EXPORT FUNCTION ---
        function exportTableToExcel() {
            // Get current filtered data from the table
            const table = document.getElementById("reportTable");

            // Use SheetJS to convert table HTML to Worksheet
            const wb = XLSX.utils.table_to_book(table, {
                sheet: "Report"
            });

            // Generate filename with date
            const dateStr = new Date().toISOString().split('T')[0];
            XLSX.writeFile(wb, `Borrowed_Report_${dateStr}.xlsx`);
        }

        // Sidebar Toggle Logic (Standard)
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
</body>

</html>