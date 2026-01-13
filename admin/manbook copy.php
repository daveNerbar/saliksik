<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php"); 

// --- 2. HANDLE FORM SUBMISSION (ADD / EDIT) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // Common Variables
    $title = $conn->real_escape_string($_POST['bookTitle']);
    $authors = $conn->real_escape_string($_POST['authors']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $genre = $conn->real_escape_string($_POST['genre']);
    $language = $conn->real_escape_string($_POST['language']);
    $copies = (int)$_POST['totalCopies'];
    $pages = (int)$_POST['totalPages'];
    $accessor_no = $conn->real_escape_string($_POST['accessorNo']); // New Field
    $callNo = $conn->real_escape_string($_POST['callNo']);
    $copyright = $conn->real_escape_string($_POST['copyright']);
    $volume = $conn->real_escape_string($_POST['volume']);
    $edition = $conn->real_escape_string($_POST['edition']);
    $year = $conn->real_escape_string($_POST['publishYear']);
    $publisher = $conn->real_escape_string($_POST['publisher']);
    $desc = $conn->real_escape_string($_POST['description']);

    // File Upload Logic
    $filePath = $_POST['existing_file']; // Default to existing
    if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $fileName = time() . "_" . basename($_FILES["fileUpload"]["name"]);
        $target_file = $target_dir . $fileName;
        
        if (move_uploaded_file($_FILES["fileUpload"]["tmp_name"], $target_file)) {
            $filePath = $fileName;
        }
    }

    // ACTION: ADD or UPDATE
    if ($_POST['action'] == 'save') {
        $bookId = $_POST['book_id'];

        if (empty($bookId)) {
            // INSERT NEW BOOK
            $sql = "INSERT INTO books (book_title, authors, isbn, genre, language, total_copies, total_pages, accessor_no, call_number, copyright, volume, edition, publish_year, publisher, description, file_path) 
                    VALUES ('$title', '$authors', '$isbn', '$genre', '$language', '$copies', '$pages', '$accessor_no', '$callNo', '$copyright', '$volume', '$edition', '$year', '$publisher', '$desc', '$filePath')";
        } else {
            // UPDATE EXISTING BOOK
            $sql = "UPDATE books SET 
                    book_title='$title', authors='$authors', isbn='$isbn', genre='$genre', language='$language', 
                    total_copies='$copies', total_pages='$pages', accessor_no='$accessor_no', call_number='$callNo', copyright='$copyright', 
                    volume='$volume', edition='$edition', publish_year='$year', publisher='$publisher', 
                    description='$desc', file_path='$filePath' 
                    WHERE id='$bookId'";
        }
        $conn->query($sql);
    }

    // ACTION: DELETE
    if ($_POST['action'] == 'delete') {
        $id = $_POST['delete_id'];
        $conn->query("DELETE FROM books WHERE id=$id");
    }

    // Refresh page to clear post data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- 3. FETCH BOOKS FOR TABLE ---
$result = $conn->query("SELECT * FROM books ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Books | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <?php include 'header.php'; ?>

    <style>
        /* [KEEP EXISTING CSS STYLES HERE - NO CHANGES NEEDED TO CSS] */
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; margin: 0; color: #374151; height: 100vh; overflow: hidden; }
        input, select, textarea, button { font-family: 'Poppins', sans-serif; }

        .content-area { flex: 1; padding: 2rem; overflow-y: auto; height: 100%; }
        .inventory-container { background: #fff; border-radius: 12px; box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05); padding: 30px; width: 100%; max-width: 1600px; margin: 0 auto; }
        .page-title { font-size: 26px; font-weight: 800; margin: 0 0 20px 0; color: #1f2937; padding-bottom: 10px; border-bottom: 2px solid #eee; }
        
        .local-header { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .local-search-bar { display: flex; align-items: center; position: relative; width: 100%; max-width: 400px; }
        .local-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888; font-size: 20px; pointer-events: none; }
        .local-search-bar input { width: 100%; padding: 10px 10px 10px 40px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; height: 45px; }
        
        .header-actions { display: flex; gap: 15px; }
        .btn-export { padding: 0 20px; height: 45px; background-color: #217346; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; white-space: nowrap; }
        .btn-export:hover { background-color: #1a5c38; }
        #openModalBtn { padding: 0 25px; height: 45px; background-color: #8a1515; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 15px; font-weight: 600; white-space: nowrap; }
        #openModalBtn:hover { background-color: #6d1010; }

        .table-container { width: 100%; max-height: 600px; overflow-y: auto; overflow-x: auto; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; min-width: 1600px; }
        th, td { padding: 15px 20px; text-align: left; white-space: nowrap; font-size: 14px; border-bottom: 1px solid #e0e0e0; }
        thead th { background-color: #e6e6e6; font-weight: 700; position: sticky; top: 0; text-align: left; }
        tbody tr:hover { background-color: #fafafa; }

        .footer { display: flex; justify-content: space-between; align-items: center; background-color: #f9fafb; border-radius: 8px; padding: 15px 20px; border: 1px solid #e0e0e0; }
        .total-count { font-weight: 600; font-size: 14px; }

        .action-btn { background: none; border: none; cursor: pointer; font-size: 18px; padding: 5px; }
        .edit-btn { color: #007bff; }
        .delete-btn { color: #dc3545; }

        /* Modal Styles */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 2000; justify-content: center; align-items: center; }
        .modal-content { background: #fff; padding: 30px; border-radius: 10px; width: 90%; max-width: 1000px; max-height: 90vh; overflow-y: auto; }
        .modal-main-title { font-size: 24px; font-weight: 800; margin-bottom: 20px; }
        .form-title { font-size: 18px; font-weight: 700; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; margin-bottom: 25px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .full-width { grid-column: 1 / -1; }
        @media (max-width: 900px) { .form-grid { grid-template-columns: 1fr; } }
        
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; font-size: 14px; margin-bottom: 8px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; height: 45px; padding: 10px 15px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none; }
        .form-group textarea { height: auto; }
        
        .btn-upload { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; height: 45px; background-color: #e0e0e0; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-upload:hover { background-color: #d0d0d0; }
        
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
                    <ul class="dropdown-menu">
                        <li><a href="addbook.php" class="dropdown-link">Add Book</a></li>
                        <li><a href="manbook.php" class="dropdown-link active-page">Manage Book</a></li>
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
                <div class="inventory-container">
                    <h1 class="page-title">Books</h1>

                    <div class="local-header">
                        <div class="local-search-bar">
                            <span class="iconify local-search-icon" data-icon="eva:search-outline"></span>
                            <input type="text" id="searchInput" placeholder="Search Books by Title, Author or Accessor No.">
                        </div>
                        <div class="header-actions">
                            <button id="exportBtn" class="btn-export"><span class="iconify" data-icon="eva:file-text-outline"></span> Export to Excel</button>
                            <button id="openModalBtn">+ Add Books</button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table id="inventoryTable">
                            <thead>
                                <tr>
                                    <th>Accessor No.</th> <th>Call No.</th>
                                    <th>ISBN/ISSN</th>
                                    <th>Author</th>
                                    <th>Title of Book</th>
                                    <th>Volume</th>
                                    <th>Edition</th>
                                    <th>Category</th>
                                    <th>Total Copies</th>
                                    <th>Copyright</th>
                                    <th>Publisher</th>
                                    <th>Publish Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        // Display Accessor No.
                                        echo "<td>" . htmlspecialchars($row['accessor_no']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['call_number']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['isbn']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['authors']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['book_title']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['volume']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['edition']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['genre']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['total_copies']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['copyright']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['publisher']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['publish_year']) . "</td>";
                                        echo "<td>
                                            <button class='action-btn edit-btn' 
                                                data-id='" . $row['id'] . "'
                                                data-title='" . htmlspecialchars($row['book_title'], ENT_QUOTES) . "'
                                                data-author='" . htmlspecialchars($row['authors'], ENT_QUOTES) . "'
                                                data-isbn='" . htmlspecialchars($row['isbn'], ENT_QUOTES) . "'
                                                data-genre='" . htmlspecialchars($row['genre'], ENT_QUOTES) . "'
                                                data-copies='" . htmlspecialchars($row['total_copies'], ENT_QUOTES) . "'
                                                data-pages='" . htmlspecialchars($row['total_pages'], ENT_QUOTES) . "'
                                                data-accessor_no='" . htmlspecialchars($row['accessor_no'], ENT_QUOTES) . "' 
                                                data-call='" . htmlspecialchars($row['call_number'], ENT_QUOTES) . "'
                                                data-copyright='" . htmlspecialchars($row['copyright'], ENT_QUOTES) . "'
                                                data-vol='" . htmlspecialchars($row['volume'], ENT_QUOTES) . "'
                                                data-ed='" . htmlspecialchars($row['edition'], ENT_QUOTES) . "'
                                                data-year='" . htmlspecialchars($row['publish_year'], ENT_QUOTES) . "'
                                                data-pub='" . htmlspecialchars($row['publisher'], ENT_QUOTES) . "'
                                                data-desc='" . htmlspecialchars($row['description'], ENT_QUOTES) . "'
                                                data-file='" . htmlspecialchars($row['file_path'], ENT_QUOTES) . "'
                                                onclick='editBook(this)'>✎</button>
                                            <button class='action-btn delete-btn' onclick='deleteBook(" . $row['id'] . ")'>🗑</button>
                                        </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='13' style='text-align:center;'>No books found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="footer">
                        <div class="total-count">Total Books: <span id="totalBooksCount"><?= $result->num_rows ?></span></div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="addBookModal" class="modal-overlay">
        <div class="modal-content">
            <h1 class="modal-main-title" id="modalTitle">Add Book</h1>
            <h2 class="form-title">Book Information</h2>

            <form id="addBookForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="book_id" id="book_id">
                <input type="hidden" name="existing_file" id="existing_file">

                <div class="form-grid">
                    <div class="form-group"><label>Book Title</label><input type="text" name="bookTitle" id="bookTitle" required></div>
                    <div class="form-group"><label>Author(s)</label><input type="text" name="authors" id="authors" required></div>
                    <div class="form-group"><label>ISBN/ISSN</label><input type="text" name="isbn" id="isbn" required></div>
                    
                    <div class="form-group"><label>Accessor No.</label><input type="text" name="accessorNo" id="accessorNo" required></div>
                    
                    <div class="form-group"><label>Call No.</label><input type="text" name="callNo" id="callNo" required></div>
                    <div class="form-group"><label>Genre/Category</label>
                        <select name="genre" id="genre">
                            <option value="Fiction">Fiction</option>
                            <option value="Non-fiction">Non-fiction</option>
                            <option value="Academic Material">Academic Material</option>
                            <option value="Thesis">Thesis</option>
                            <option value="General Works">General Works</option>
                            <option value="Reference Material">Reference Material</option>
                            <option value="Filipiniana">Filipiniana</option>
                            <option value="Narrative Report">Narrative Report</option>
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
                    <div class="form-group"><label>File Attachment</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="fileUpload" id="fileUpload" hidden>
                            <button type="button" class="btn-upload" onclick="document.getElementById('fileUpload').click()">
                                <span class="iconify" data-icon="eva:cloud-upload-outline"></span>
                                <span id="fileNameDisplay">Upload File</span>
                            </button>
                        </div>
                    </div>
                    <div class="form-group"><label>Copyright</label><input type="text" name="copyright" id="copyright" required></div>
                    <div class="form-group"><label>Volume</label><input type="text" name="volume" id="volume"></div>
                    <div class="form-group"><label>Edition</label><input type="text" name="edition" id="edition"></div>
                    <div class="form-group"><label>Publish Year</label><input type="text" name="publishYear" id="publishYear" required></div>
                    <div class="form-group"><label>Publisher</label><input type="text" name="publisher" id="publisher" required></div>
                    <div class="form-group full-width"><label>Book Description</label><textarea name="description" id="description" rows="5"></textarea></div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="closeModalBtn">Cancel</button>
                    <button type="submit" class="btn-add" id="submitBtn">Add Book</button>
                </div>
            </form>
        </div>
    </div>

    <form id="deleteForm" method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="delete_id" id="delete_id">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Sidebar Logic (Standard)
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

            // Modal Logic
            const modal = document.getElementById('addBookModal');
            const openModalBtn = document.getElementById('openModalBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const addBookForm = document.getElementById('addBookForm');
            const modalTitle = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('submitBtn');

            function openModal() { modal.style.display = 'flex'; }
            
            function closeModal() {
                modal.style.display = 'none';
                addBookForm.reset();
                document.getElementById('book_id').value = '';
                document.getElementById('existing_file').value = '';
                document.getElementById('fileNameDisplay').textContent = "Upload File";
                modalTitle.textContent = "Add Book";
                submitBtn.textContent = "Add Book";
            }

            openModalBtn.addEventListener('click', openModal);
            closeModalBtn.addEventListener('click', closeModal);
            window.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

            // File Upload Display
            const fileInput = document.getElementById('fileUpload');
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) fileNameDisplay.textContent = fileInput.files[0].name;
            });

            // Client-Side Search
            const searchInput = document.getElementById('searchInput');
            const tableRows = document.querySelectorAll('#tableBody tr');
            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                tableRows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        });

        // Edit Function - Populate Fields
        function editBook(btn) {
            document.getElementById('modalTitle').textContent = "Edit Book";
            document.getElementById('submitBtn').textContent = "Update Book";

            document.getElementById('book_id').value = btn.dataset.id;
            document.getElementById('bookTitle').value = btn.dataset.title;
            document.getElementById('authors').value = btn.dataset.author;
            document.getElementById('isbn').value = btn.dataset.isbn;
            document.getElementById('genre').value = btn.dataset.genre;
            document.getElementById('totalCopies').value = btn.dataset.copies;
            document.getElementById('totalPages').value = btn.dataset.pages;
            
            // New Accessor Field
            document.getElementById('accessorNo').value = btn.dataset.accessor_no;
            
            document.getElementById('callNo').value = btn.dataset.call;
            document.getElementById('copyright').value = btn.dataset.copyright;
            document.getElementById('volume').value = btn.dataset.vol;
            document.getElementById('edition').value = btn.dataset.ed;
            document.getElementById('publishYear').value = btn.dataset.year;
            document.getElementById('publisher').value = btn.dataset.pub;
            document.getElementById('description').value = btn.dataset.desc;
            document.getElementById('existing_file').value = btn.dataset.file;

            if (btn.dataset.file) {
                document.getElementById('fileNameDisplay').textContent = "Existing File (Upload new to replace)";
            }

            document.getElementById('addBookModal').style.display = 'flex';
        }

        // Delete Function
        function deleteBook(id) {
            if (confirm("Are you sure you want to delete this book?")) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Excel Export
        document.getElementById('exportBtn').addEventListener('click', () => {
            const table = document.getElementById('inventoryTable');
            const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet JS" });
            XLSX.writeFile(wb, 'Saliksik_Books_Inventory.xlsx');
        });
    </script>
    <script src="search.js"></script>
</body>
</html>