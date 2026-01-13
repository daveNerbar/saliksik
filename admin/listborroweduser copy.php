<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php");
// --- 2. SEARCH & FILTER LOGIC ---
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all'; // Get filter value

$bookTitle = "";
$bookAccessor = "";
$borrowers = [];
$error_message = "";

if (!empty($search)) {
    $searchTerm = "%" . $search . "%";

    // Step A: Find the Book Title first
    $stmt = $conn->prepare("SELECT book_title, accessor_no FROM books WHERE book_title LIKE ? OR accessor_no = ? LIMIT 1");
    $stmt->bind_param("ss", $searchTerm, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        $bookTitle = $book['book_title'];
        $bookAccessor = $book['accessor_no'];

        // --- PREPARE DATE FILTER SQL ---
        $dateCondition = "";
        if ($filter == 'today') {
            $dateCondition = " AND DATE(b.borrow_date) = CURDATE() ";
        } elseif ($filter == 'week') {
            $dateCondition = " AND b.borrow_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) ";
        } elseif ($filter == 'month') {
            $dateCondition = " AND b.borrow_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) ";
        }

        // Step B: Get all users who borrowed this specific Book Title
        $sqlHistory = "
            SELECT 
                b.student_number,
                COALESCE(CONCAT(s.firstname, ' ', s.lastname), CONCAT(f.firstname, ' ', f.lastname)) AS borrower_name,
                COALESCE(s.course, f.department) AS dept,
                b.borrow_date,
                b.return_date,
                b.status
            FROM borrowing b
            JOIN books bk ON b.accessor_no = bk.accessor_no
            LEFT JOIN studacc s ON b.student_number = s.studentnumber
            LEFT JOIN facultyacc f ON b.student_number = f.pupid
            WHERE bk.book_title = ? 
            $dateCondition 
            ORDER BY b.created_at DESC
        ";

        $stmt2 = $conn->prepare($sqlHistory);
        $stmt2->bind_param("s", $bookTitle);
        $stmt2->execute();
        $resHistory = $stmt2->get_result();

        while ($row = $resHistory->fetch_assoc()) {
            $borrowers[] = $row;
        }
    } else {
        $error_message = "No book found matching: " . htmlspecialchars($search);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt List | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <?php include 'header.php'; ?>

    <style>
        /* --- GLOBAL STYLES --- */
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

        /* --- LAYOUT --- */
        .content-area {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            height: 100%;
        }

        .page-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
            border: 1px solid #e0e0e0;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            color: #1f2937;
        }

        .pdf-btn {
            background-color: #800000;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pdf-btn:hover {
            background-color: #600000;
        }

        /* --- SEARCH SECTION --- */
        .search-container-local {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
            align-items: center;
            gap: 10px;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            cursor: pointer;
            height: 40px;
            background-color: #fff;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        .search-form {
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 5px;
            width: 350px;
            height: 40px;
            background-color: #fff;
        }

        .search-input {
            border: none;
            outline: none;
            padding: 8px;
            font-size: 14px;
            flex: 1;
            font-family: 'Poppins', sans-serif;
        }

        .search-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #555;
            padding: 0 10px;
            display: flex;
            align-items: center;
        }

        .search-btn:hover {
            color: #000;
        }

        /* --- TABLE --- */
        .table-responsive {
            width: 100%;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .custom-table th,
        .custom-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        .custom-table th {
            background-color: #f1f1f1;
            font-weight: 700;
            color: #333;
        }

        .custom-table tr:hover {
            background-color: #f9f9f9;
        }

        .error-msg {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .book-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #800000;
        }

        .book-info h3 {
            margin: 0;
            font-size: 18px;
            color: #800000;
        }

        .book-info p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #555;
        }

        /* Status Badge */
        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-Returned {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .status-Borrowed {
            background-color: #fff3cd;
            color: #664d03;
        }

        .status-Overdue {
            background-color: #f8d7da;
            color: #842029;
        }

        /* Print Specifics */
        @media print {

            .sidebar,
            .top-header,
            .search-container-local,
            .pdf-btn {
                display: none !important;
            }

            .page-container {
                border: none;
                box-shadow: none;
                padding: 0;
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

                <div class="page-container" id="receipt-content">

                    <div class="page-header">
                        <h1 class="page-title">Book Borrowing History</h1>
                        <?php if (!empty($bookTitle)): ?>
                            <button class="pdf-btn" id="downloadPdfBtn">
                                <iconify-icon icon="mdi:file-pdf-box"></iconify-icon> Download PDF
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($error_message): ?>
                        <div class="error-msg"><?= $error_message ?></div>
                    <?php endif; ?>

                    <div class="search-container-local">
                        <form method="GET" action="" style="display: flex; align-items: center; gap: 10px;">

                            <select name="filter" class="filter-select" onchange="this.form.submit()">
                                <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All Time</option>
                                <option value="today" <?= $filter == 'today' ? 'selected' : '' ?>>This Day</option>
                                <option value="week" <?= $filter == 'week' ? 'selected' : '' ?>>Past 7 Days</option>
                                <option value="month" <?= $filter == 'month' ? 'selected' : '' ?>>Past 30 Days</option>
                            </select>

                            <div class="search-form">
                                <input type="text" name="search" class="search-input"
                                    placeholder="Search Book Title or Accessor No..."
                                    value="<?= htmlspecialchars($search) ?>" required autocomplete="off">
                                <button type="submit" class="search-btn">
                                    <iconify-icon icon="eva:search-fill"></iconify-icon>
                                </button>
                            </div>

                        </form>
                    </div>

                    <?php if (!empty($bookTitle)): ?>
                        <div class="book-info">
                            <h3><?= htmlspecialchars($bookTitle) ?></h3>
                            <p>Accessor Number: <strong><?= htmlspecialchars($bookAccessor) ?></strong></p>
                        </div>

                        <div class="table-responsive">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">ID No.</th>
                                        <th style="width: 30%;">Borrower Name</th>
                                        <th style="width: 20%;">Date Borrowed</th>
                                        <th style="width: 15%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($borrowers) > 0): ?>
                                        <?php foreach ($borrowers as $user): ?>
                                            <?php
                                            // Dynamic Status Color Logic
                                            $statusClass = 'status-' . $user['status'];
                                            if ($user['status'] == 'Borrowed' && strtotime($user['return_date']) < time()) {
                                                $statusClass = 'status-Overdue';
                                                $user['status'] = 'Overdue';
                                            }
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($user['student_number']) ?></td>
                                                <td>
                                                    <?= htmlspecialchars($user['borrower_name']) ?>
                                                    <br><small style="color:#666;"><?= htmlspecialchars($user['dept']) ?></small>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($user['borrow_date'])) ?></td>
                                                <td><span class="status-badge <?= $statusClass ?>"><?= $user['status'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center;">No borrowing history found for this period.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; color: #888; padding: 40px;">
                            <iconify-icon icon="mdi:book-search-outline" style="font-size: 48px; margin-bottom: 10px;"></iconify-icon>
                            <p>Please search for a book to view its history.</p>
                        </div>
                    <?php endif; ?>

                </div>

            </main>
        </div>
    </div>
    <script src="search.js"></script>

    <script>
        // Sidebar Toggle Logic
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

        // PDF Generation Script
        const pdfBtn = document.getElementById('downloadPdfBtn');
        if (pdfBtn) {
            pdfBtn.addEventListener('click', () => {
                const element = document.getElementById('receipt-content');

                // Hide search/filter elements before printing
                const searchContainer = document.querySelector('.search-container-local');
                const btn = document.querySelector('.pdf-btn');

                searchContainer.style.display = 'none';
                btn.style.display = 'none';

                const opt = {
                    margin: 0.5,
                    filename: 'Book_History_Report.pdf',
                    image: {
                        type: 'jpeg',
                        quality: 0.98
                    },
                    html2canvas: {
                        scale: 2
                    },
                    jsPDF: {
                        unit: 'in',
                        format: 'letter',
                        orientation: 'portrait'
                    }
                };

                html2pdf().set(opt).from(element).save().then(() => {
                    // Show elements again after printing
                    searchContainer.style.display = 'flex';
                    btn.style.display = 'flex';
                });
            });
        }
    </script>
</body>

</html>