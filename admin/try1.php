<?php
// --- 1. DATABASE CONNECTION ---
$servername = "localhost";
$username = "root";
$password = "";
$database = "saliksik";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- 2. HANDLE RETURN ACTION ---
if (isset($_POST['action']) && $_POST['action'] == 'return_book') {
    $borrowId = $_POST['borrow_id'];
    $accessor_no = $_POST['accessor_no'];

    $conn->begin_transaction();
    try {
        // Update Status in borrowing table
        $stmt1 = $conn->prepare("UPDATE borrowing SET status = 'Returned', return_date = NOW() WHERE id = ?");
        $stmt1->bind_param("i", $borrowId);
        $stmt1->execute();

        // Return Inventory in books table
        $stmt2 = $conn->prepare("UPDATE books SET total_copies = total_copies + 1 WHERE accessor_no = ?");
        $stmt2->bind_param("s", $accessor_no);
        $stmt2->execute();

        $conn->commit();
        echo "<script>alert('Book Returned Successfully!'); window.location.href = 'borrowedlist.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error returning book.');</script>";
    }
}

// --- 3. FETCH DATA (Handling Students & Faculty) ---
$sql = "SELECT b.id AS borrow_id, b.student_number, b.accessor_no, b.created_at, b.return_date AS due_date,
        bk.book_title,
        COALESCE(CONCAT(s.firstname, ' ', s.lastname), CONCAT(f.firstname, ' ', f.lastname)) AS borrower_name,
        COALESCE(s.course, f.department) AS dept,
        COALESCE(s.section, 'Faculty') AS section_role
        FROM borrowing b
        LEFT JOIN studacc s ON b.student_number = s.studentnumber
        LEFT JOIN facultyacc f ON b.student_number = f.pupid
        JOIN books bk ON b.accessor_no = bk.accessor_no
        WHERE b.status = 'Borrowed' OR b.status = 'Overdue'
        ORDER BY b.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Return Book | Saliksik</title>
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <?php include 'header.php'; ?>

    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; margin: 0; color: #374151; height: 100vh; overflow: hidden; }

        /* Return Container */
        .return-container { background: #fff; border-radius: 10px; box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08); padding: 30px; display: flex; flex-direction: column; height: 100%; border: 1px solid #e0e0e0; }
        .page-title { font-size: 26px; font-weight: 800; margin: 0 0 20px 0; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }

        /* Filters */
        .filter-section { margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .filter-select { padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; outline: none; cursor: pointer; }

        /* Table */
        .table-container { width: 100%; border: 1px solid #eee; border-radius: 8px; flex: 1; overflow: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1200px; }
        th, td { padding: 14px 12px; font-size: 13px; border-bottom: 1px solid #eee; color: #333; }
        thead th { background-color: #e6e6e6; font-weight: 700; position: sticky; top: 0; text-align: left; }
        .text-center { text-align: center; }

        /* Buttons */
        .btn-icon { background: none; border: none; cursor: pointer; font-size: 20px; transition: transform 0.2s; }
        .btn-check { color: #00c853; }
        .btn-icon:hover { transform: scale(1.2); }
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
                        <li><a href="borrowedlist.php" class="dropdown-link active-page">Return Book</a></li>
                        <li><a href="borrowedhistory.php" class="dropdown-link">Borrowed History</a></li>
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
                        <a href="profile.html">
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
                <div class="return-container">
                    <h1 class="page-title">Return Book</h1>

                    <div class="filter-section">
                        <label for="courseFilter">Filter by Dept/Course:</label>
                        <select id="courseFilter" class="filter-select">
                            <option value="All">All</option>
                            <option value="BSIT">BSIT</option>
                            <option value="BSCPE">BSCPE</option>
                            <option value="BSHM">BSHM</option>
                            <option value="BSOA">BSOA</option>
                            <option value="Faculty">Faculty</option>
                        </select>
                    </div>

                    <div class="table-container">
                        <table id="borrowTable">
                            <thead>
                                <tr>
                                    <th>ID Number</th>
                                    <th>Name</th>
                                    <th class="text-center">Dept/Course</th>
                                    <th class="text-center">Section/Role</th>
                                    <th class="text-center">Accessor No.</th>
                                    <th>Book Title</th>
                                    <th>Date Borrowed</th>
                                    <th class="text-center">Due Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['student_number']) ?></td>
                                            <td><?= htmlspecialchars($row['borrower_name']) ?></td>
                                            <td class="text-center course-cell"><?= htmlspecialchars($row['dept']) ?></td>
                                            <td class="text-center role-cell"><?= htmlspecialchars($row['section_role']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['accessor_no']) ?></td>
                                            <td><?= htmlspecialchars($row['book_title']) ?></td>
                                            <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
                                            <td class="text-center" style="color: <?= (strtotime($row['due_date']) < time()) ? 'red' : 'inherit'; ?>">
                                                <?= htmlspecialchars($row['due_date']) ?>
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" onsubmit="return confirm('Confirm return of this book?');">
                                                    <input type="hidden" name="action" value="return_book">
                                                    <input type="hidden" name="borrow_id" value="<?= $row['borrow_id'] ?>">
                                                    <input type="hidden" name="accessor_no" value="<?= $row['accessor_no'] ?>">
                                                    <button type="submit" class="btn-icon btn-check" title="Return Book">
                                                        <iconify-icon icon="eva:checkmark-circle-2-fill"></iconify-icon>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center" style="padding:20px;">No pending borrowed books found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="search.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- FILTER LOGIC ---
            const courseFilter = document.getElementById('courseFilter');
            const tableRows = document.querySelectorAll('#borrowTable tbody tr');

            if (courseFilter) {
                courseFilter.addEventListener('change', function() {
                    const selected = this.value;
                    tableRows.forEach(row => {
                        const courseCell = row.querySelector('.course-cell');
                        const roleCell = row.querySelector('.role-cell');
                        let match = false;

                        if (selected === 'All') {
                            match = true;
                        } else if (selected === 'Faculty') {
                            if (roleCell && roleCell.textContent.trim() === 'Faculty') match = true;
                        } else {
                            if (courseCell && courseCell.textContent.trim() === selected) match = true;
                        }
                        row.style.display = match ? '' : 'none';
                    });
                });
            }

            // --- SIDEBAR & NOTIF LOGIC ---
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
        });
    </script>
</body>
</html>