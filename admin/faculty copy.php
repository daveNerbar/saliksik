<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "saliksik";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch faculty data
$sql = "SELECT * FROM facultyacc ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Faculty Management | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <?php include 'header.php'; ?>


    <style>
        /* [KEEPING YOUR EXACT CSS STYLES UNCHANGED] */
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

        input,
        select,
        textarea,
        button {
            font-family: 'Poppins', sans-serif;
        }

        /* CONTENT */
        .content-area {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            height: 100%;
        }

        .faculty-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
            padding: 30px;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            color: #1f2937;
            border-bottom: 2px solid #eee;
        }

        .filter-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .local-search-box {
            flex-grow: 1;
            max-width: 300px;
            position: relative;
        }

        .local-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 20px;
            pointer-events: none;
        }

        .local-search-box input {
            width: 100%;
            padding: 10px 10px 10px 40px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .filters select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            background: #fff;
            margin-left: 10px;
            cursor: pointer;
        }

        .table-wrapper {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-top: 20px;
            overflow: hidden;
        }

        .table-container {
            position: relative;
            height: auto;
            overflow-x: auto;
        }

        #facultyTable {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        #facultyTable th,
        #facultyTable td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
            white-space: nowrap;
        }

        #facultyTable thead th {
            background-color: #e6e6e6;
            color: #333;
            font-weight: 600;
            cursor: pointer;
        }

        #facultyTable tbody tr:hover {
            background-color: #f9f9f9;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-action {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            padding: 6px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .btn-edit {
            color: #3b82f6;
        }

        .btn-edit:hover {
            background-color: #eff6ff;
        }

        .btn-delete {
            color: #ef4444;
        }

        .btn-delete:hover {
            background-color: #fef2f2;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            font-size: 14px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        #facultyCount {
            color: #666;
        }

        .pagination button {
            background: #f1f1f1;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 6px 14px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
                        <li><a href="faculty.php" class="dropdown-link active-page">Faculty</a></li>
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

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:swap-horizontal"></iconify-icon>
                        <span class="nav-text">Borrowing Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="bookborrow.php" class="dropdown-link">Add Borrow</a></li>
                        <li><a href="borrowedlist.php" class="dropdown-link">Return Book</a></li>
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
                <div class="faculty-container">

                    <h1 class="page-title">Faculty</h1>

                    <div class="filter-section">
                        <div class="local-search-box">
                            <span class="iconify local-search-icon" data-icon="eva:search-outline"></span>
                            <input type="text" id="searchInput" placeholder="Search Faculty">
                        </div>

                        <div class="filters">
                            <select id="courseFilter">
                                <option value="">All Department</option>
                                <option value="BSCpE">BSCpE</option>
                                <option value="BSHM">BSHM</option>
                                <option value="BSIT">BSIT</option>
                                <option value="BSOA">BSOA</option>
                            </select>

                            <select id="yearFilter">
                                <option value="">All Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <div class="table-container">
                            <table id="facultyTable">
                                <thead>
                                    <tr>
                                        <th style="cursor: pointer;">Employee ID</th>
                                        <th style="cursor: pointer;">Name</th>
                                        <th style="cursor: pointer;">Department</th>
                                        <th style="cursor: pointer;">Phone Number</th>
                                        <th style="cursor: pointer;">Teaching Assignment</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <?php
                                    if ($result && $result->num_rows > 0) {
                                        // Reset result pointer to beginning if used before, or just loop
                                        $result->data_seek(0);

                                        while ($row = $result->fetch_assoc()) {
                                            $mid = !empty($row['middlename']) ? $row['middlename'][0] . '.' : '';
                                            $fullname = $row['lastname'] . ", " . $row['firstname'] . " " . $mid . " " . $row['suffix'];

                                            echo "<tr>";
                                            echo "<td>" . $row['pupid'] . "</td>";
                                            echo "<td>" . $fullname . "</td>";
                                            echo "<td>" . $row['department'] . "</td>";
                                            echo "<td>" . $row['phonenumber'] . "</td>";
                                            echo "<td>N/A</td>"; // Placeholder
                                            echo "<td>
                                                    <div class='action-buttons'>
                                                        </button>
                                                        <button type='button' class='btn-action btn-delete' title='Delete'>
                                                            <span class='iconify' data-icon='eva:trash-2-outline'></span>
                                                        </button>
                                                    </div>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>No faculty members found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="footer">
                        <div id="facultyCount">
                            Showing <?php echo $result->num_rows; ?> users
                        </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar Logic
            const hamburgerBtn = document.getElementById('hamburger-btn');
            const dashboardContainer = document.getElementById('dashboard-container');
            const sidebar = document.getElementById('sidebar');

            if (hamburgerBtn) {
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

            // --- Client-Side Search (Filter rendered rows) ---
            const searchInput = document.getElementById('searchInput');
            const courseFilter = document.getElementById('courseFilter');

            function filterRows() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedDept = courseFilter.value.toLowerCase();
                const rows = document.querySelectorAll('#facultyTable tbody tr');

                rows.forEach(row => {
                    const id = row.cells[0].textContent.toLowerCase();
                    const name = row.cells[1].textContent.toLowerCase();
                    const dept = row.cells[2].textContent.toLowerCase();
                    const phone = row.cells[3].textContent.toLowerCase();

                    const matchesSearch = id.includes(searchTerm) || name.includes(searchTerm) || dept.includes(searchTerm) || phone.includes(searchTerm);
                    const matchesDept = selectedDept === "" || dept.includes(selectedDept);

                    if (matchesSearch && matchesDept) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            }

            if (searchInput) searchInput.addEventListener('keyup', filterRows);
            if (courseFilter) courseFilter.addEventListener('change', filterRows);
        });
    </script>
    <script src="search.js"></script>

</body>

</html>