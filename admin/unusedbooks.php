<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php"); 

// --- 2. HANDLE ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // ACTION: RETRIEVE (Move back to main 'books' table)
    if ($_POST['action'] == 'retrieve') {
        $id = $_POST['id'];

        // 1. Insert back into books table (Explicitly listing columns to exclude 'reason' and 'archived_at')
        $restoreSql = "INSERT INTO books (book_title, authors, isbn, genre, language, total_copies, total_pages, accessor_no, call_number, copyright, volume, edition, publish_year, publisher, description, file_path)
                       SELECT book_title, authors, isbn, genre, language, total_copies, total_pages, accessor_no, call_number, copyright, volume, edition, publish_year, publisher, description, file_path
                       FROM unused_books WHERE id = ?";
        
        $stmt = $conn->prepare($restoreSql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // 2. Delete from unused_books
            $delStmt = $conn->prepare("DELETE FROM unused_books WHERE id = ?");
            $delStmt->bind_param("i", $id);
            $delStmt->execute();
        }
    }

    // ACTION: DELETE PERMANENTLY
    if ($_POST['action'] == 'delete') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM unused_books WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    // Refresh Page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- 3. FETCH DATA ---
$sql = "SELECT * FROM unused_books ORDER BY archived_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Unused Books | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <?php include 'header.php'; ?>

    <style>
        /* [Standard Dashboard Styles] */
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; margin: 0; color: #374151; height: 100vh; overflow: hidden; }

        .content-area { flex: 1; padding: 2rem; overflow-y: auto; height: 100%; }
        .page-container { background: #fff; border-radius: 12px; box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05); padding: 30px; width: 100%; max-width: 1600px; margin: 0 auto; }
        .page-title { font-size: 26px; font-weight: 800; margin: 0 0 20px 0; color: #1f2937; padding-bottom: 10px; border-bottom: 2px solid #eee; display:flex; align-items:center; gap:10px; }
        
        .header-controls { margin-bottom: 20px; display: flex; justify-content: flex-end; }
        .search-box { position: relative; width: 350px; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888; font-size: 20px; }
        .search-box input { width: 100%; padding: 10px 10px 10px 40px; border: 1px solid #ccc; border-radius: 6px; outline: none; }

        .table-container { width: 100%; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        th, td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #e0e0e0; font-size: 14px; }
        thead th { background-color: #f1f1f1; font-weight: 700; color: #333; }
        tbody tr:hover { background-color: #f9f9f9; }

        .reason-cell { color: #d97706; font-style: italic; max-width: 250px; }
        
        .action-btn { background: none; border: none; cursor: pointer; font-size: 20px; padding: 5px; transition: transform 0.2s; }
        .action-btn:hover { transform: scale(1.1); }
        .retrieve-btn { color: #16a34a; }
        .delete-btn { color: #dc2626; }

        .empty-state { text-align: center; padding: 40px; color: #999; }
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

                <div class="nav-dropdown active">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:bookshelf"></iconify-icon>
                        <span class="nav-text">Book Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="addbook.php" class="dropdown-link">Add Book</a></li>
                        <li><a href="manbook.php" class="dropdown-link ">Manage Book</a></li>
                        <li><a href="bookreport.php" class="dropdown-link">Book Report</a></li>
                        <li><a href="unusedbooks.php" class="dropdown-link active-page">Unused Books</a></li>

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
                <div class="page-container">
                    <h1 class="page-title">
                        <iconify-icon icon="mdi:archive-arrow-down" style="color:#d97706;"></iconify-icon> 
                        Unused / Archived Books
                    </h1>

                    <div class="header-controls">
                        <div class="search-box">
                            <span class="iconify search-icon" data-icon="eva:search-outline"></span>
                            <input type="text" id="searchInput" placeholder="Search Archived Books...">
                        </div>
                    </div>

                    <div class="table-container">
                        <table id="archiveTable">
                            <thead>
                                <tr>
                                    <th>Accessor No.</th>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>Reason for Archiving</th>
                                    <th>Date Archived</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['accessor_no']) ?></td>
                                            <td><strong><?= htmlspecialchars($row['book_title']) ?></strong></td>
                                            <td><?= htmlspecialchars($row['authors']) ?></td>
                                            <td class="reason-cell">"<?= htmlspecialchars($row['reason_for_archiving']) ?>"</td>
                                            <td><?= date('M d, Y', strtotime($row['archived_at'])) ?></td>
                                            <td>
                                                <button class="action-btn retrieve-btn" onclick="confirmAction('retrieve', <?= $row['id'] ?>)" title="Restore to Inventory">
                                                    <iconify-icon icon="mdi:restore"></iconify-icon>
                                                </button>
                                                <button class="action-btn delete-btn" onclick="confirmAction('delete', <?= $row['id'] ?>)" title="Delete Permanently">
                                                    <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <iconify-icon icon="mdi:bookshelf" style="font-size: 48px; display:block; margin:0 auto;"></iconify-icon>
                                            No unused books found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <form id="actionForm" method="POST" style="display:none;">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="id" id="formId">
    </form>

    <script src="search.js"></script>

    <script>
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

        // Action Logic
        function confirmAction(action, id) {
            let message = "";
            if (action === 'retrieve') {
                message = "Are you sure you want to restore this book back to the active inventory?";
            } else {
                message = "WARNING: This will permanently delete the book record. This action cannot be undone. Proceed?";
            }

            if (confirm(message)) {
                document.getElementById('formAction').value = action;
                document.getElementById('formId').value = id;
                document.getElementById('actionForm').submit();
            }
        }

        // Search Logic
        const searchInput = document.getElementById('searchInput');
        const rows = document.querySelectorAll('#archiveTable tbody tr');

        if(searchInput) {
            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                rows.forEach(row => {
                    if (row.querySelector('td')) { // Skip if empty state
                        const text = row.innerText.toLowerCase();
                        row.style.display = text.includes(term) ? '' : 'none';
                    }
                });
            });
        }
    </script>
</body>
</html>