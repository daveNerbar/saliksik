<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php");

// --- 2. HANDLE FORM SUBMISSION (ADD ONLY) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save') {

    // Common Variables
    $title = $conn->real_escape_string($_POST['bookTitle']);
    $authors = $conn->real_escape_string($_POST['authors']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $genre = $conn->real_escape_string($_POST['genre']); 
    $language = $conn->real_escape_string($_POST['language']);
    $ejournal = $conn->real_escape_string($_POST['ejournal']); // New E-Journal Field
    $copies = (int)$_POST['totalCopies'];
    $pages = (int)$_POST['totalPages'];
    $accessor_no = $conn->real_escape_string($_POST['accessor_no']);
    $callNo = $conn->real_escape_string($_POST['callNo']);
    $copyright = $conn->real_escape_string($_POST['copyright']);
    $volume = $conn->real_escape_string($_POST['volume']);
    $edition = $conn->real_escape_string($_POST['edition']);
    $year = $conn->real_escape_string($_POST['publishYear']);
    $publisher = $conn->real_escape_string($_POST['publisher']);
    $desc = $conn->real_escape_string($_POST['description']);

    // --- INSERT NEW BOOK ---
    $sql = "INSERT INTO books (book_title, authors, isbn, genre, language, ejournal, total_copies, total_pages, accessor_no, call_number, copyright, volume, edition, publish_year, publisher, description) 
            VALUES ('$title', '$authors', '$isbn', '$genre', '$language', '$ejournal', '$copies', '$pages', '$accessor_no', '$callNo', '$copyright', '$volume', '$edition', '$year', '$publisher', '$desc')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Book Added Successfully!'); 
                window.location.href = '" . $_SERVER['PHP_SELF'] . "';
              </script>";
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Book | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">

    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <?php include 'header.php'; ?>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

        /* --- GLOBAL RESET & FONT SETTINGS --- */
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8f9fa; margin: 0; color: #374151; height: 100vh; overflow: hidden; }
        input, select, textarea, button { font-family: 'Poppins', sans-serif; }

        /* CONTENT */
        .content-area { flex: 1; padding: 2rem; overflow-y: auto; height: 100%; }
        .form-container { background: #fff; padding: 40px; border-radius: 8px; width: 100%; max-width: 1400px; margin: 0 auto; }
        .page-title { font-size: 24px; font-weight: 800; color: #1f2937; margin: 0 0 25px 0; }
        .form-section-header { font-size: 16px; font-weight: 700; color: #1f2937; margin-bottom: 20px; }
        .form-title { font-size: 18px; font-weight: 700; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; margin-bottom: 25px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .full-width { grid-column: 1 / -1; }
        @media (max-width: 900px) { .form-grid { grid-template-columns: 1fr; } }
        
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; font-size: 14px; margin-bottom: 8px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; height: 45px; padding: 10px 15px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none; }
        .form-group textarea { height: auto; }
        
        .form-actions { margin-top: 30px; display: flex; justify-content: center; gap: 20px; }
        .form-actions button { padding: 12px 40px; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; border: none; }
        .btn-cancel { background-color: #d1d5db; color: #333; }
        .btn-add { background-color: #8a1515; color: white; }
        .form-actions button:hover { opacity: 0.9; }
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
                    <ul class="dropdown-menu ">
                        <li><a href="addbook.php" class="dropdown-link active-page">Add Book</a></li>
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
                <div class="form-container">
                    <h1 class="page-title">Add Book</h1>
                    <h2 class="form-section-header">Book Information</h2>

                    <form id="addBookForm" method="POST">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="book_id" id="book_id">

                        <div class="form-grid">
                            <div class="form-group"><label>Book Title</label><input type="text" name="bookTitle" id="bookTitle" required></div>
                            <div class="form-group"><label>Author(s)</label><input type="text" name="authors" id="authors" required></div>
                            <div class="form-group"><label>ISBN/ISSN</label><input type="text" name="isbn" id="isbn" required></div>
                            
                            <div class="form-group"><label>Genre/Category</label>
                                <input type="text" name="genre" id="genre" placeholder="Enter Genre (e.g. Fiction, Thesis)" required>
                            </div>

                            <div class="form-group"><label>Is E-Journal?</label>
                                <select name="ejournal" id="ejournal">
                                    <option value="No">No (Standard)</option>
                                    <option value="Yes">Yes (E-Journal)</option>
                                </select>
                            </div>

                            <div class="form-group"><label>Language</label>
                                <select name="language" id="language">
                                    <option value="English">English</option>
                                    <option value="Filipino">Filipino</option>
                                </select>
                            </div>
                            <div class="form-group"><label>Total Copies</label><input type="number" name="totalCopies" id="totalCopies" min="0" required></div>
                            <div class="form-group"><label>Total No. Pages</label><input type="number" name="totalPages" id="totalPages" min="0"></div>
                            <div class="form-group"><label>Accessor No.</label><input type="text" name="accessor_no" id="accessor_no" required></div>
                            <div class="form-group"><label>Call No.</label><input type="text" name="callNo" id="callNo" required></div>

                            <div class="form-group"><label>Copyright</label><input type="text" name="copyright" id="copyright"></div>
                            <div class="form-group"><label>Volume</label><input type="text" name="volume" id="volume"></div>
                            <div class="form-group"><label>Edition</label><input type="text" name="edition" id="edition"></div>
                            <div class="form-group"><label>Publish Year</label><input type="text" name="publishYear" id="publishYear" required></div>
                            <div class="form-group"><label>Publisher</label><input type="text" name="publisher" id="publisher"></div>

                            <div class="form-group full-width"><label>Book Description</label><textarea name="description" id="description" rows="5"></textarea></div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" id="closeModalBtn" onclick="window.history.back()">Cancel</button>
                            <button type="submit" class="btn-add" id="submitBtn">Add Book</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Sidebar Logic
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
    <script src="search.js"></script>
</body>

</html>