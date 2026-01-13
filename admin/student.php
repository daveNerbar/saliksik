<?php

include("connection.php");

// --- HANDLE DELETE REQUEST ---
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['student_id'];
    $deleteSql = "DELETE FROM studacc WHERE studentnumber = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("s", $id);

    if ($stmt->execute()) {
        // Success
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- HANDLE UPDATE REQUEST (New Code) ---
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = $_POST['edit_student_id'];
    $email = $_POST['edit_email'];
    $course = $_POST['edit_course'];
    $section = $_POST['edit_section'];
    $phone = $_POST['edit_phone'];

    $updateSql = "UPDATE studacc SET email = ?, course = ?, section = ?, phonenumber = ? WHERE studentnumber = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("sssss", $email, $course, $section, $phone, $id);

    if ($stmt->execute()) {
        // Success
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- FETCH STUDENTS ---
$sql = "SELECT * FROM studacc ORDER BY id DESC";
$result = $conn->query($sql);

$students_data = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['pdf_file'])) {
            $row['pdf_file'] = basename($row['pdf_file']);
        }
        $students_data[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Management | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <?php include 'header.php'; ?>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

        /* --- GLOBAL RESET & FONT SETTINGS --- */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
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

        :root {
            --color-cyan: #00bcd4;
            --color-cyan-bg: #e0f7fa;
        }

        /* --- STUDENT PAGE CONTENT --- */
        .content-area {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .student-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
            padding: 30px;
            max-width: 1400px;
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
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .local-search-box {
            position: relative;
            flex-grow: 1;
            max-width: 300px;
        }

        .local-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 20px;
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
            overflow-x: auto;
        }

        #studentTable {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        #studentTable th,
        #studentTable td {
            padding: 15px 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
            white-space: nowrap;
            vertical-align: middle;
        }

        #studentTable thead th {
            background-color: #e6e6e6;
            color: #000;
            font-weight: 700;
            cursor: pointer;
            text-align: left;
        }

        #studentTable thead th.text-center {
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        #studentTable tbody tr:hover {
            background-color: #f9f9f9;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-action {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
        }

        .btn-edit {
            color: #2563eb;
        }

        .btn-delete {
            color: #ef4444;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 14px;
        }

        .pagination button {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 6px 14px;
            margin-left: 5px;
            cursor: pointer;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: default;
        }

        /* --- MODAL STYLES (Used for PDF & Edit) --- */
        .modal {
            display: none;
            position: fixed;
            z-index: 3000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 0;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 15px 20px;
            background: #f1f1f1;
            border-bottom: 1px solid #ddd;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .close {
            color: #555;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        /* PDF Specific */
        .pdf-frame {
            width: 100%;
            height: 80vh;
            border: none;
            border-radius: 0 0 8px 8px;
        }

        /* Edit Form Specific */
        .edit-form-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 13px;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #ddd;
            text-align: right;
            border-radius: 0 0 8px 8px;
            background: #fff;
        }

        .btn-save {
            background-color: #2563eb;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-cancel {
            background-color: #ccc;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            font-size: 14px;
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
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:account-group"></iconify-icon>
                        <span class="nav-text">User Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="student.php" class="dropdown-link active-page">Student</a></li>
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
                <div class="student-container">
                    <h1 class="page-title">Students</h1>

                    <div class="filter-section">
                        <div class="local-search-box">
                            <span class="iconify local-search-icon" data-icon="eva:search-outline"></span>
                            <input type="text" id="searchInput" placeholder="Search Students (ID, Name or Email)">
                        </div>

                        <div class="filters">
                            <select id="courseFilter">
                                <option value="">All Course</option>
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
                            <table id="studentTable">
                                <thead>
                                    <tr>
                                        <th class="text-left">Student No.</th>
                                        <th class="text-left">Name</th>
                                        <th class="text-left">Email</th>
                                        <th class="text-center">Course</th>
                                        <th class="text-center">Section</th>
                                        <th class="text-center">Phone No.</th>
                                        <th class="text-center">COR</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="footer">
                        <div id="studentCount">Showing 0 to 0 of 0 users</div>
                        <div class="pagination">
                            <button id="prevBtn">Previous</button>
                            <button id="nextBtn">Next</button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="student_id" id="deleteStudentId">
    </form>

    <div id="pdfModal" class="modal">
        <div class="modal-content" style="height:90%;">
            <div class="modal-header">
                <h3>Certificate of Registration</h3>
                <span class="close" onclick="closePdfModal()">&times;</span>
            </div>
            <iframe id="pdfFrame" class="pdf-frame" src=""></iframe>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content" style="max-width: 500px; height: auto;">
            <div class="modal-header">
                <h3>Edit Student Info</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="edit-form-body">
                    <input type="hidden" name="action" value="update">
                    <div class="form-group">
                        <label>Student Number (Read-only)</label>
                        <input type="text" id="edit_student_id" name="edit_student_id" readonly style="background-color:#eee;">
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="edit_email" name="edit_email" required>
                    </div>

                    <div class="form-group">
                        <label>Course</label>
                        <select id="edit_course" name="edit_course" required>
                            <option value="BSCpE">BSCpE</option>
                            <option value="BSHM">BSHM</option>
                            <option value="BSIT">BSIT</option>
                            <option value="BSOA">BSOA</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Section</label>
                        <input type="text" id="edit_section" name="edit_section" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" id="edit_phone" name="edit_phone" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // --- 1. GET DATA FROM PHP ---
        const dbStudents = <?php echo json_encode($students_data); ?>;

        document.addEventListener('DOMContentLoaded', () => {
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

            document.querySelectorAll('.nav-dropdown-toggle').forEach(toggle => {
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

            // --- 2. TABLE LOGIC ---
            const studentTableBody = document.querySelector('#studentTable tbody');
            const searchInput = document.getElementById('searchInput');
            const courseFilter = document.getElementById('courseFilter');
            const yearFilter = document.getElementById('yearFilter');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const studentCountDisplay = document.getElementById('studentCount');

            const currentYear = new Date().getFullYear();
            yearFilter.innerHTML = '<option value="">All Year</option>';
            for (let year = 2020; year <= currentYear + 1; year++) {
                const option = document.createElement('option');
                option.value = year.toString();
                option.textContent = year.toString();
                yearFilter.appendChild(option);
            }

            const ROWS_PER_PAGE = 10;
            let currentPage = 1;
            let filteredAndSortedData = [...dbStudents];

            function renderTable() {
                studentTableBody.innerHTML = '';
                const totalFilteredRows = filteredAndSortedData.length;
                const totalPages = Math.ceil(totalFilteredRows / ROWS_PER_PAGE);

                if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const start = (currentPage - 1) * ROWS_PER_PAGE;
                const end = Math.min(start + ROWS_PER_PAGE, totalFilteredRows);
                const rowsToDisplay = filteredAndSortedData.slice(start, end);

                if (rowsToDisplay.length === 0) {
                    studentTableBody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 30px;">No matching records found.</td></tr>`;
                } else {
                    rowsToDisplay.forEach(data => {
                        const row = document.createElement('tr');

                        const pdfLink = data.pdf_file ?
                            `<a href="javascript:void(0)" onclick="openPdfModal('../view/uploads/${data.pdf_file}')" title="View COR">
                                <iconify-icon icon="mdi:file-pdf-box" style="color:#d32f2f; font-size:20px;"></iconify-icon>
                             </a>` :
                            `<span style="color:#ccc;">No File</span>`;

                        row.innerHTML = `
                            <td class="text-left">${data.studentnumber}</td>
                            <td class="text-left"><strong>${data.lastname}, ${data.firstname}</strong></td>
                            <td class="text-left" style="color:#555;">${data.email || 'N/A'}</td>
                            <td class="text-center">${data.course}</td> 
                            <td class="text-center">${data.section}</td>
                            <td class="text-center">${data.phonenumber}</td>
                            <td class="text-center">${pdfLink}</td>
                            <td class="text-center">
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" title="Edit" 
                                        onclick="openEditModal('${data.studentnumber}', '${data.email}', '${data.course}', '${data.section}', '${data.phonenumber}')">
                                        <span class="iconify" data-icon="eva:edit-2-fill"></span>
                                    </button>
                                    <button class="btn-action btn-delete" title="Delete" onclick="confirmDelete('${data.studentnumber}')"><span class="iconify" data-icon="eva:trash-2-fill"></span></button>
                                </div>
                            </td>
                        `;
                        studentTableBody.appendChild(row);
                    });
                }

                prevBtn.disabled = currentPage === 1;
                nextBtn.disabled = currentPage >= totalPages || totalPages === 0;
                let displayStart = totalFilteredRows === 0 ? 0 : start + 1;
                studentCountDisplay.textContent = `Showing ${displayStart} to ${end} of ${totalFilteredRows} users`;
            }

            function applyFilters() {
                const searchText = searchInput.value.toLowerCase();
                const selectedCourse = courseFilter.value;
                const selectedYear = yearFilter.value;

                filteredAndSortedData = dbStudents.filter(student => {
                    const fullName = `${student.lastname}, ${student.firstname}`.toLowerCase();
                    const matchesSearch = fullName.includes(searchText) ||
                        student.studentnumber.toLowerCase().includes(searchText) ||
                        (student.email && student.email.toLowerCase().includes(searchText));

                    const matchesCourse = selectedCourse === "" || student.course === selectedCourse;
                    const matchesYear = true;

                    return matchesSearch && matchesCourse && matchesYear;
                });
                currentPage = 1;
                renderTable();
            }

            searchInput.addEventListener('input', applyFilters);
            courseFilter.addEventListener('change', applyFilters);

            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                }
            });
            nextBtn.addEventListener('click', () => {
                const totalPages = Math.ceil(filteredAndSortedData.length / ROWS_PER_PAGE);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            });

            renderTable();
        });

        // --- 3. DELETE FUNCTION ---
        function confirmDelete(studentId) {
            if (confirm("Are you sure you want to delete student ID: " + studentId + "?")) {
                document.getElementById('deleteStudentId').value = studentId;
                document.getElementById('deleteForm').submit();
            }
        }

        // --- 4. PDF MODAL FUNCTIONS ---
        function openPdfModal(pdfPath) {
            const modal = document.getElementById('pdfModal');
            const iframe = document.getElementById('pdfFrame');
            iframe.src = pdfPath;
            modal.style.display = "flex";
        }

        function closePdfModal() {
            const modal = document.getElementById('pdfModal');
            const iframe = document.getElementById('pdfFrame');
            modal.style.display = "none";
            iframe.src = "";
        }

        // --- 5. EDIT MODAL FUNCTIONS ---
        function openEditModal(id, email, course, section, phone) {
            document.getElementById('edit_student_id').value = id;
            document.getElementById('edit_email').value = (email === 'null') ? '' : email;
            document.getElementById('edit_course').value = course;
            document.getElementById('edit_section').value = section;
            document.getElementById('edit_phone').value = phone;

            document.getElementById('editModal').style.display = "flex";
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = "none";
        }

        // Close modal if clicked outside
        window.onclick = function(event) {
            const pdfModal = document.getElementById('pdfModal');
            const editModal = document.getElementById('editModal');
            if (event.target == pdfModal) closePdfModal();
            if (event.target == editModal) closeEditModal();
        }
    </script>
    <script src="search.js"></script>

</body>

</html>