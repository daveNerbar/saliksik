<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php"); 

// --- AJAX HANDLER: FETCH BORROWER (Student OR Faculty) ---
if (isset($_GET['action']) && $_GET['action'] == 'get_borrower') {
    $id = $_GET['id_number'];

    // 1. Check Student Table
    $stmt = $conn->prepare("SELECT firstname, lastname, course, section FROM studacc WHERE studentnumber = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'type' => 'student',
            'name' => $row['firstname'] . ' ' . $row['lastname'],
            'dept_label' => 'Course',
            'dept_value' => $row['course'],
            'extra_label' => 'Section',
            'extra_value' => $row['section']
        ]);
        exit;
    }

    // 2. Check Faculty Table (if not found in students)
    $stmt = $conn->prepare("SELECT firstname, lastname, department FROM facultyacc WHERE pupid = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'type' => 'faculty',
            'name' => $row['firstname'] . ' ' . $row['lastname'],
            'dept_label' => 'Department',
            'dept_value' => $row['department'],
            'extra_label' => 'Role',
            'extra_value' => 'Faculty' // Static value or fetch position if available
        ]);
        exit;
    }

    // 3. Not Found
    echo json_encode(['success' => false]);
    exit;
}

// --- AJAX HANDLER: FETCH BOOK ---
if (isset($_GET['action']) && $_GET['action'] == 'get_book') {
    $accessor_no = $_GET['accessor_no'];
    $stmt = $conn->prepare("SELECT book_title, total_copies FROM books WHERE accessor_no = ?");
    $stmt->bind_param("s", $accessor_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// --- FORM SUBMISSION: SAVE BORROWING ---
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_borrow'])) {
    $borrowerID = $_POST['borrower_id'];
    $cNum = $_POST['accessor_no'];
    $bDate = $_POST['borrow_date'];
    $rDate = $_POST['return_date'];

    if (empty($borrowerID) || empty($cNum)) {
        $message = "Error: Borrower ID and Accessor No. are required.";
    } else {
        // Check Book Availability
        $checkStmt = $conn->prepare("SELECT total_copies FROM books WHERE accessor_no = ?");
        $checkStmt->bind_param("s", $cNum);
        $checkStmt->execute();
        $bookData = $checkStmt->get_result()->fetch_assoc();

        if (!$bookData) {
            $message = "Error: Book not found.";
        } elseif ($bookData['total_copies'] <= 0) {
            $message = "Error: No copies available.";
        } else {
            // Transaction
            $conn->begin_transaction();
            try {
                // Insert into borrowing table (works for both ID types)
                $stmt = $conn->prepare("INSERT INTO borrowing (student_number, accessor_no, borrow_date, return_date) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $borrowerID, $cNum, $bDate, $rDate);
                $stmt->execute();

                // Deduct Copy
                $updateStmt = $conn->prepare("UPDATE books SET total_copies = total_copies - 1 WHERE accessor_no = ?");
                $updateStmt->bind_param("s", $cNum);
                $updateStmt->execute();

                $conn->commit();
                echo "<script>alert('Book Issued Successfully!'); window.location.href='bookborrow.php';</script>";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "System Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Issue Book | Saliksik</title>
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <?php include 'header.php'; ?>
    <style>
        /* --- COPY YOUR EXISTING CSS HERE (Sidebar, Header, Layout) --- */
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

        

        /* CONTENT */
        .content-area {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            height: 100%;
        }

        .borrow-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 40px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .form-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .content-area label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .content-area input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
            font-family: 'Poppins', sans-serif;
        }

        .content-area input[readonly] {
            background-color: #f9fafb;
            color: #666;
            cursor: not-allowed;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background-color: #992222;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.2s;
        }

        .submit-btn:hover {
            background-color: #7a1515;
        }

        .alert-error {
            color: #991b1b;
            background: #fee2e2;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 5px;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 768px) {
            .form-layout {
                grid-template-columns: 1fr;
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
                        <li><a href="bookborrow.php" class="dropdown-link active-page">Add Borrow</a></li>
                        <li><a href="borrowedlist.php" class="dropdown-link">Return Book</a></li>
                        <li><a href="reservebooks.php" class="dropdown-link">Reserved Book</a></li>
                        <li><a href="borrowedhistory.php" class="dropdown-link">Borrowed History</a></li>
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
                <div class="borrow-container">
                    <h1 class="page-title">Issue Book</h1>

                    <?php if ($message): ?>
                        <div class="alert-error"><?= $message ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="save_borrow" value="1">

                        <div class="form-layout">

                            <div class="column">
                                <h3 class="section-title" style="margin-top:0;">Borrower Details</h3>

                                <div class="form-group">
                                    <label>ID Number (Student No. or PUP ID)</label>
                                    <input type="text" name="borrower_id" id="borrowerId" placeholder="Ex: 2023-00123-MN-0 or F-1234" required onblur="fetchBorrower()">
                                    <div id="borrowerStatus" class="badge"></div>
                                </div>

                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" id="borrowerName" readonly placeholder="Auto-filled">
                                </div>
                                <div class="form-group">
                                    <label id="lblDept">Course / Department</label>
                                    <input type="text" id="dept" readonly placeholder="Auto-filled">
                                </div>
                                <div class="form-group">
                                    <label id="lblExtra">Section / Role</label>
                                    <input type="text" id="extra" readonly placeholder="Auto-filled">
                                </div>
                            </div>

                            <div class="column">
                                <h3 class="section-title" style="margin-top:0;">Book Details</h3>

                                <div class="form-group">
                                    <label>Book Accessor No.</label>
                                    <input type="text" name="accessor_no" id="accessor_no" placeholder="Ex: QA76.73 P20" required onblur="fetchBook()">
                                    <div id="bookStatus" class="badge"></div>
                                </div>

                                <div class="form-group">
                                    <label>Book Title</label>
                                    <input type="text" id="bookTitle" readonly placeholder="Auto-filled">
                                </div>

                                <h3 class="section-title" style="margin-top: 30px;">Dates</h3>
                                <div class="form-group">
                                    <label>Borrow Date</label>
                                    <input type="date" name="borrow_date" id="borrowDate" value="<?= date('Y-m-d'); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Return Due Date</label>
                                    <input type="date" name="return_date" id="returnDueDate" >
                                </div>

                                <button type="submit" class="submit-btn">CONFIRM BORROW</button>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

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
        // 1. Fetch Borrower Data (Student or Faculty)
        function fetchBorrower() {
            const id = document.getElementById('borrowerId').value;
            const statusDiv = document.getElementById('borrowerStatus');

            if (id.length > 3) {
                statusDiv.innerText = "Searching...";
                statusDiv.className = "badge";

                fetch(`bookborrow.php?action=get_borrower&id_number=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('borrowerName').value = data.name;

                            // Update labels dynamically based on user type
                            document.getElementById('lblDept').innerText = data.dept_label;
                            document.getElementById('dept').value = data.dept_value;

                            document.getElementById('lblExtra').innerText = data.extra_label;
                            document.getElementById('extra').value = data.extra_value;

                            const typeLabel = data.type === 'student' ? 'Student' : 'Faculty';
                            statusDiv.innerText = `${typeLabel} Found ✅`;
                            statusDiv.className = "badge badge-success";
                        } else {
                            clearBorrowerFields();
                            statusDiv.innerText = "User Not Found ❌";
                            statusDiv.className = "badge badge-error";
                        }
                    });
            }
        }

        function clearBorrowerFields() {
            document.getElementById('borrowerName').value = "";
            document.getElementById('dept').value = "";
            document.getElementById('extra').value = "";
        }

        // 2. Fetch Book Data
        function fetchBook() {
            const accessor_no = document.getElementById('accessor_no').value;
            const statusDiv = document.getElementById('bookStatus');

            if (accessor_no.length > 2) {
                statusDiv.innerText = "Searching...";

                fetch(`bookborrow.php?action=get_book&accessor_no=${accessor_no}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const copies = data.data.total_copies;
                            document.getElementById('bookTitle').value = data.data.book_title;

                            if (copies > 0) {
                                statusDiv.innerText = `Book Found ✅ (${copies} copies available)`;
                                statusDiv.className = "badge badge-success";
                            } else {
                                statusDiv.innerText = `Book Found ⚠️ (0 copies available)`;
                                statusDiv.className = "badge badge-error";
                            }
                        } else {
                            document.getElementById('bookTitle').value = "";
                            statusDiv.innerText = "Book Not Found ❌";
                            statusDiv.className = "badge badge-error";
                        }
                    });
            }
        }
    </script>
    <script src="search.js"></script>

</body>

</html>