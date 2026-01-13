<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php"); 

// --- 2. HANDLE ACTIONS (DELETE / RELEASE) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // ACTION: DELETE RESERVATION (Release Book)
    if ($_POST['action'] == 'delete') {
        $reservationId = intval($_POST['id']);

        // A. First, get the Book ID from the reservation record to know which book to update
        $getSql = "SELECT book_id FROM reservebook WHERE id = ?";
        $stmt = $conn->prepare($getSql);
        $stmt->bind_param("i", $reservationId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $bookId = $row['book_id'];

            // B. Return the copy to the inventory (+1)
            $updateSql = "UPDATE books SET total_copies = total_copies + 1 WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $bookId);
            
            if ($updateStmt->execute()) {
                // C. Delete the reservation record
                $delSql = "DELETE FROM reservebook WHERE id = ?";
                $delStmt = $conn->prepare($delSql);
                $delStmt->bind_param("i", $reservationId);
                $delStmt->execute();
                
                echo "<script>alert('Reservation cancelled and book copy released back to inventory.'); window.location.href='reservebooks.php';</script>";
            } else {
                echo "<script>alert('Error updating inventory.');</script>";
            }
        }
        $stmt->close();
    }
}

// --- 3. FETCH RESERVATIONS ---
$sql = "SELECT * FROM reservebook ORDER BY reservation_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reserved Books | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>

    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <?php include 'header.php'; ?>

    <style>
        /* [STANDARD DASHBOARD STYLES] */
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; margin: 0; color: #374151; height: 100vh; overflow: hidden; }

        .content-area { flex: 1; padding: 2rem; overflow-y: auto; height: 100%; }
        .page-container { background: #fff; border-radius: 12px; box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05); padding: 30px; width: 100%; max-width: 1600px; margin: 0 auto; }
        .page-title { font-size: 26px; font-weight: 800; margin: 0 0 20px 0; color: #1f2937; padding-bottom: 10px; border-bottom: 2px solid #eee; display: flex; align-items: center; gap: 10px; }
        
        .header-controls { margin-bottom: 20px; display: flex; justify-content: flex-end; }
        .search-box { position: relative; width: 350px; display: flex; align-items: center; }
        .search-icon { position: absolute; left: 12px; font-size: 20px; color: #888; pointer-events: none; }
        .search-box input { width: 100%; padding: 10px 10px 10px 40px; border: 1px solid #ccc; border-radius: 6px; outline: none; height: 45px; }

        .table-container { width: 100%; border: 1px solid #e0e0e0; border-radius: 8px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        th, td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #e0e0e0; font-size: 14px; white-space: nowrap; }
        thead th { background-color: #f1f1f1; font-weight: 700; color: #333; position: sticky; top: 0; }
        tbody tr:hover { background-color: #fafafa; }

        /* Status Badge */
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .status-Pending { background-color: #fff3cd; color: #854d0e; border: 1px solid #fde047; }

        .action-btn { background: none; border: none; cursor: pointer; font-size: 20px; padding: 5px; transition: transform 0.2s; color: #dc2626; }
        .action-btn:hover { transform: scale(1.1); color: #b91c1c; }

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
                        <li><a href="reservebooks.php" class="dropdown-link active-page">Reserved Book</a></li>
                        <li><a href="borrowedhistory.php" class="dropdown-link ">Borrowed History</a></li>
                        <li><a href="borrowedreport.php" class="dropdown-link ">Borrowed Report</a></li>

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
                        <iconify-icon icon="mdi:calendar-clock" style="color:#d97706;"></iconify-icon> 
                        Pending Reservations
                    </h1>

                    <div class="header-controls">
                        <div class="search-box">
                            <span class="iconify search-icon" data-icon="eva:search-outline"></span>
                            <input type="text" id="searchInput" placeholder="Search Borrower, Title or Accessor...">
                        </div>
                    </div>

                    <div class="table-container">
                        <table id="reserveTable">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Borrower Name</th>
                                    <th>ID Number</th>
                                    <th>Book Title</th>
                                    <th>Accessor No.</th>
                                    <th>Date Reserved</th>
                                    <th>Status</th>
                                    <th style="text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><strong><?= htmlspecialchars($row['borrower_name']) ?></strong></td>
                                            <td><?= htmlspecialchars($row['borrower_id']) ?></td>
                                            <td><?= htmlspecialchars($row['book_title']) ?></td>
                                            <td><?= htmlspecialchars($row['accessor_no']) ?></td>
                                            <td><?= date('M d, Y h:i A', strtotime($row['reservation_date'])) ?></td>
                                            <td><span class="status-badge status-<?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                                            <td style="text-align:center;">
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to CANCEL this reservation? This will return the book copy to the inventory.');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <button type="submit" class="action-btn" title="Cancel Reservation & Release Book">
                                                        <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="empty-state">
                                            <iconify-icon icon="mdi:clipboard-text-off-outline" style="font-size: 48px; display:block; margin:0 auto;"></iconify-icon>
                                            No pending reservations found.
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
    <script src="search.js"></script>

    <script>
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

        // Dropdown Logic
        

        // Search Logic
        const searchInput = document.getElementById('searchInput');
        const rows = document.querySelectorAll('#reserveTable tbody tr');

        if(searchInput) {
            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                rows.forEach(row => {
                    if (row.querySelector('td')) { 
                        const text = row.innerText.toLowerCase();
                        row.style.display = text.includes(term) ? '' : 'none';
                    }
                });
            });
        }
    </script>

    
</body>
</html>