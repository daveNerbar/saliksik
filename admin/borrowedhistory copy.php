<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

// --- 2. DATABASE CONNECTION ---
include("connection.php"); 

// --- 3. HANDLE DELETE ACTION ---
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM borrowing WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: borrowedhistory.php");
    exit();
}

// --- 4. CHECK OVERDUE NOTICES (Optional: Keep your email logic here) ---
$checkSql = "SELECT b.id, b.return_date, b.status, b.notification_sent, 
             f.email as faculty_email, f.firstname, f.lastname, bk.book_title
             FROM borrowing b
             JOIN facultyacc f ON b.student_number = f.pupid
             JOIN books bk ON b.accessor_no = bk.accessor_no
             WHERE b.status = 'Borrowed' AND b.notification_sent = 0";

$checkResult = $conn->query($checkSql);
$today = date('Y-m-d');

if ($checkResult && $checkResult->num_rows > 0) {
    // ... (Your existing PHPMailer logic remains here) ...
}

// --- 5. FETCH HISTORY DATA FOR TABLE ---
$sql = "SELECT b.id, b.accessor_no, b.student_number,
        COALESCE(CONCAT(s.lastname, ', ', s.firstname), CONCAT(f.lastname, ', ', f.firstname)) AS borrower_name,
        COALESCE(s.course, f.department) AS course_dept,
        COALESCE(s.section, 'Faculty') AS section_role,
        bk.book_title, b.created_at, b.return_date, b.status
        FROM borrowing b
        LEFT JOIN studacc s ON b.student_number = s.studentnumber
        LEFT JOIN facultyacc f ON b.student_number = f.pupid
        LEFT JOIN books bk ON b.accessor_no = bk.accessor_no
        ORDER BY b.created_at DESC";

$result = $conn->query($sql);
$historyData = [];
$count = 1;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = $row['status'];
        // Auto-detect overdue for display purposes
        if ($status == 'Borrowed' && $row['return_date'] < $today) {
            $status = 'Overdue';
        }

        $historyData[] = [
            'no' => $count++,
            'id' => $row['id'],
            'accessorNo' => $row['accessor_no'],
            'studentNo' => $row['student_number'],
            'name' => $row['borrower_name'],
            'course' => $row['course_dept'], // Stores Course (e.g., BSIT) or Dept (e.g., CITE)
            'year' => $row['section_role'],  // Stores Section (e.g., 4-1) or "Faculty"
            'title' => $row['book_title'],
            'borrowDate' => date('M d, Y h:i A', strtotime($row['created_at'])),
            'returnDate' => date('M d, Y h:i A', strtotime($row['return_date'])),
            'status' => $status
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed History | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <?php include 'header.php'; ?>

    <style>
        /* --- DASHBOARD CSS --- */
        * { box-sizing: border-box; }
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
        .container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .page-title {
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 20px 0;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .search-box {
            position: relative;
            width: 300px;
        }
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }
        .search-box input {
            width: 100%;
            padding: 10px 10px 10px 40px;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
        }
        .filter-box {
            display: flex;
            gap: 10px;
        }
        .filter-box select {
            padding: 0 15px;
            height: 40px;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
            background-color: #fff;
            cursor: pointer;
        }
        .table-container {
            width: 100%;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            flex: 1;
            overflow: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }
        th, td {
            padding: 15px 10px;
            font-size: 13px;
            border-bottom: 1px solid #e0e0e0;
        }
        thead th {
            background-color: #dcdcdc;
            font-weight: 700;
            position: sticky;
            top: 0;
            text-align: left;
        }
        .text-center { text-align: center; }
        .status-returned { color: #16a34a; font-weight: 600; }
        .status-not-returned { color: #f59e0b; font-weight: 600; }
        .status-overdue { color: #dc2626; font-weight: 600; }
        .btn-icon {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #dc2626;
            transition: transform 0.2s;
        }
        .btn-icon:hover { transform: scale(1.2); }
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .pagination button {
            background: #f1f1f1;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 8px 16px;
            cursor: pointer;
            margin-left: 10px;
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
                <a href="dashboard.php" class="nav-link">
                    <iconify-icon icon="mdi:view-dashboard"></iconify-icon>
                    <span class="nav-text">Dashboard</span>
                </a>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:account-group"></iconify-icon>
                        <span class="nav-text">User Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="student.php" class="dropdown-link">Student</a></li>
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
                    </ul>
                </div>

                <div class="nav-dropdown active">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:swap-horizontal"></iconify-icon>
                        <span class="nav-text">Borrowing Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="bookborrow.php" class="dropdown-link">Add Borrow</a></li>
                        <li><a href="borrowedlist.php" class="dropdown-link">Return Book</a></li>
                        <li><a href="borrowedhistory.php" class="dropdown-link active-page">Borrowed History</a></li>
                        <li><a href="borrowedreport.php" class="dropdown-link">Borrowed Report</a></li>
                    </ul>
                </div>

                <a href="annceve.php" class="nav-link">
                    <iconify-icon icon="mdi:bullhorn"></iconify-icon>
                    <span class="nav-text">Announcements & Events</span>
                </a>

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
                        <?php if (isset($notifCount) && $notifCount > 0): ?>
                            <span class="notif-badge"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
                        <?php endif; ?>

                        <div class="notif-dropdown">
                            <div class="notif-header">Notifications</div>
                            <ul class="notif-list">
                                <?php if (isset($notifCount) && $notifCount > 0): ?>
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
                <div class="container">
                    <h1 class="page-title">Borrowed History</h1>

                    <div class="header-controls">
                        <div class="search-box">
                            <span class="iconify search-icon" data-icon="eva:search-outline"></span>
                            <input type="text" id="searchInput" placeholder="Search Name, ID, Title, Course...">
                        </div>

                        <div class="filter-box">
                            <select id="courseFilter">
                                <option value="">All Dept/Course</option>
                                <option value="BSIT">BSIT</option>
                                <option value="BSCPE">BSCPE</option>
                                <option value="BSHM">BSHM</option>
                                <option value="BSOA">BSOA</option>
                                <option value="Faculty">Faculty</option>
                            </select>
                            <select id="yearFilter">
                                <option value="">All Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-container">
                        <table id="borrowedTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">No.</th>
                                    <th class="text-center" style="width: 100px;">Accessor No.</th>
                                    <th>ID Number</th>
                                    <th>Name</th>
                                    <th class="text-center">Dept/Course</th>
                                    <th class="text-center">Section/Role</th>
                                    <th>Title</th>
                                    <th class="text-center">Borrow Date</th>
                                    <th class="text-center">Return Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody"></tbody>
                        </table>
                    </div>

                    <div class="footer">
                        <div id="recordCount">Showing 0 entries</div>
                        <div class="pagination">
                            <button id="prevBtn">Previous</button>
                            <button id="nextBtn">Next</button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // 1. INJECT PHP DATA
        const borrowedData = <?php echo json_encode($historyData); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            // Sidebar Toggle
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

            // 2. TABLE LOGIC
            const ROWS_PER_PAGE = 10;
            let currentPage = 1;
            let filteredData = [...borrowedData];

            const tableBody = document.getElementById('tableBody');
            const searchInput = document.getElementById('searchInput');
            const courseFilter = document.getElementById('courseFilter');
            const yearFilter = document.getElementById('yearFilter');
            const recordCount = document.getElementById('recordCount');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            // Populate Year
            const currentYear = new Date().getFullYear();
            yearFilter.innerHTML = '<option value="">All Year</option>';
            for (let year = 2024; year <= currentYear + 1; year++) {
                const option = document.createElement('option');
                option.value = year.toString();
                option.textContent = year.toString();
                yearFilter.appendChild(option);
            }

            function renderTable() {
                tableBody.innerHTML = '';
                const totalItems = filteredData.length;
                const totalPages = Math.ceil(totalItems / ROWS_PER_PAGE);

                if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const start = (currentPage - 1) * ROWS_PER_PAGE;
                const end = Math.min(start + ROWS_PER_PAGE, totalItems);
                const pageData = filteredData.slice(start, end);

                if (pageData.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="11" style="text-align:center; padding: 20px;">No records found</td></tr>`;
                    recordCount.textContent = `Showing 0 to 0 of 0 entries`;
                    prevBtn.disabled = true;
                    nextBtn.disabled = true;
                    return;
                }

                pageData.forEach(row => {
                    const tr = document.createElement('tr');
                    let statusClass = '';
                    if (row.status === 'Returned') statusClass = 'status-returned';
                    else if (row.status === 'Overdue') statusClass = 'status-overdue';
                    else statusClass = 'status-not-returned';

                    tr.innerHTML = `
                        <td class="text-center">${row.no}</td>
                        <td class="text-center">${row.accessorNo}</td>
                        <td>${row.studentNo}</td>
                        <td>${row.name}</td>
                        <td class="text-center">${row.course}</td>
                        <td class="text-center">${row.year}</td>
                        <td>${row.title}</td>
                        <td class="text-center">${row.borrowDate}</td>
                        <td class="text-center">${row.returnDate}</td>
                        <td class="text-center ${statusClass}">${row.status}</td>
                        <td class="text-center">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this history record?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="${row.id}">
                                <button type="submit" class="btn-icon" title="Delete Record">
                                    <iconify-icon icon="mdi:trash-can"></iconify-icon>
                                </button>
                            </form>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });

                recordCount.textContent = `Showing ${start + 1} to ${end} of ${totalItems} entries`;
                prevBtn.disabled = currentPage === 1;
                nextBtn.disabled = currentPage === totalPages || totalPages === 0;
            }

            function filterData() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const selectedCourse = courseFilter.value;
                const selectedYear = yearFilter.value;

                filteredData = borrowedData.filter(item => {
                    const name = (item.name || "").toLowerCase();
                    const title = (item.title || "").toLowerCase();
                    const studentNo = (item.studentNo || "").toLowerCase();
                    const courseStr = (item.course || "");
                    const roleStr = (item.year || ""); // Maps to section_role (contains "Faculty")

                    // 1. Search Bar Logic (Updated to include Course)
                    const matchesSearch =
                        name.includes(searchTerm) ||
                        title.includes(searchTerm) ||
                        studentNo.includes(searchTerm) ||
                        courseStr.toLowerCase().includes(searchTerm);

                    // 2. Course Filter Logic
                    let matchesCourse = true;
                    if (selectedCourse !== "") {
                        if (selectedCourse === "Faculty") {
                            // If user selected Faculty, check the role string
                            matchesCourse = roleStr === "Faculty";
                        } else {
                            // Otherwise check the course string
                            matchesCourse = courseStr.includes(selectedCourse);
                        }
                    }

                    // 3. Year Filter Logic
                    const matchesYear = selectedYear === "" || item.borrowDate.includes(selectedYear);

                    return matchesSearch && matchesCourse && matchesYear;
                });

                currentPage = 1;
                renderTable();
            }

            searchInput.addEventListener('input', filterData);
            courseFilter.addEventListener('change', filterData);
            yearFilter.addEventListener('change', filterData);

            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                }
            });
            nextBtn.addEventListener('click', () => {
                const totalPages = Math.ceil(filteredData.length / ROWS_PER_PAGE);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            });

            renderTable();
        });
    </script>
    <script src="search.js"></script>
</body>
</html>