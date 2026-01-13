<?php
// --- 1. CONFIGURATION ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("connection.php"); 
$message = "";
$messageType = "";

// --- 2. HANDLE FORM SUBMISSION (ADD ADMIN) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $fname = $_POST['firstName'];
    $lname = $_POST['lastName'];
    $mname = $_POST['middleName'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $empId = $_POST['employeeId'];
    $user = $_POST['username'];
    $role = $_POST['role'];
    $pass = $_POST['password'];
    $confirm = $_POST['confirmPassword'];

    // --- PHP STRONG PASSWORD CHECK ---
    $uppercase = preg_match('@[A-Z]@', $pass);
    $lowercase = preg_match('@[a-z]@', $pass);
    $number    = preg_match('@[0-9]@', $pass);
    $special   = preg_match('@[^\w]@', $pass);

    if ($pass !== $confirm) {
        $message = "Error: Passwords do not match!";
        $messageType = "error";
    } elseif(!$uppercase || !$lowercase || !$number || !$special || strlen($pass) < 8) {
        $message = "Error: Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
        $messageType = "error";
    } else {
        // Check duplicate
        $check = $conn->prepare("SELECT id FROM admins WHERE username = ? OR employee_id = ?");
        $check->bind_param("ss", $user, $empId);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $message = "Error: Username or Employee ID already exists!";
            $messageType = "error";
        } else {
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (firstname, lastname, middlename, dob, gender, email, employee_id, username, role, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $fname, $lname, $mname, $dob, $gender, $email, $empId, $user, $role, $hashed_password);
            
            if ($stmt->execute()) {
                $message = "Administrator created successfully!";
                $messageType = "success";
            } else {
                $message = "Database Error: " . $stmt->error;
                $messageType = "error";
            }
        }
    }
}

// --- 3. HANDLE DELETE ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM admins WHERE id = $id");
    header("Location: addminlist.php");
    exit();
}

// --- 4. FETCH DATA ---
$admins = [];
$result = $conn->query("SELECT * FROM admins ORDER BY created_at DESC");
if ($result) { while($row = $result->fetch_assoc()) { $admins[] = $row; } }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Administrators | Saliksik</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

    <?php include 'header.php'; ?>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
        
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; margin: 0; color: #374151; height: 100vh; overflow: hidden; }
        
        

        /* CONTENT */
        .content-area { flex: 1; padding: 2rem; overflow-y: auto; height: 100%; }
        
        /* TABLE & CONTROLS */
        .container { background: #fff; border-radius: 10px; box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08); padding: 30px; width: 100%; min-height: 600px; display: flex; flex-direction: column; }
        .page-title { font-size: 24px; font-weight: 800; margin: 0 0 25px 0; color: #000; }
        .header-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-shrink: 0; }
        .search-box { position: relative; width: 300px; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; font-size: 20px; pointer-events: none; }
        .search-box input { width: 100%; padding: 10px 10px 10px 40px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; outline: none; height: 42px; font-family: 'Poppins', sans-serif; }
        .btn-add { background-color: #800000; color: #fff; border: none; border-radius: 6px; padding: 10px 20px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: background 0.2s; font-family: 'Poppins', sans-serif; text-decoration: none; }
        .btn-add:hover { background-color: #600000; }
        .table-container { width: 100%; border: 1px solid #eee; border-radius: 8px; flex: 1; overflow-y: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { padding: 14px 15px; font-size: 13px; border: 1px solid #eee; color: #333; }
        thead th { background-color: #dcdcdc; color: #000; font-weight: 700; white-space: nowrap; position: sticky; top: 0; z-index: 10; text-align: left; }
        tbody tr:hover { background-color: #f9f9f9; }
        .text-center { text-align: center; }
        .action-cell { display: flex; justify-content: center; gap: 15px; }
        .btn-icon { background: none; border: none; cursor: pointer; font-size: 22px; display: flex; align-items: center; justify-content: center; padding: 0; transition: transform 0.2s; text-decoration: none; }
        .btn-edit { color: #007bff; }
        .btn-delete { color: #992222; }
        .btn-icon:hover { transform: scale(1.1); }
        .footer { margin-top: 15px; font-size: 13px; color: #666; }

        /* --- MODAL STYLES --- */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1100; justify-content: center; align-items: center; }
        .modal-overlay.active { display: flex; }
        .modal-content { background-color: #fff; width: 800px; max-width: 95%; max-height: 90vh; border-radius: 10px; padding: 30px; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h2 { font-size: 20px; font-weight: 700; margin: 0; }
        .close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #666; }
        .section-header { font-size: 15px; color: #992222; margin: 20px 0 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; font-weight: 600; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-size: 13px; font-weight: 500; margin-bottom: 5px; }
        .form-group input, .form-group select { padding: 8px 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 13px; outline: none; font-family: 'Poppins', sans-serif; }
        .password-hint { font-size: 11px; color: #666; margin-top: 4px; } /* Added Hint Style */
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 30px; }
        .btn-cancel { background: #eee; border: none; padding: 10px 20px; border-radius: 5px; font-weight: 600; cursor: pointer; }
        .btn-save { background: #992222; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; font-weight: 600; cursor: pointer; }
        .btn-save:hover { background: #7a1515; }

        @media (max-width: 992px) {
            .sidebar { left: -300px; }
            .sidebar.mobile-active { left: 0; }
            .main-content { margin-left: 0; }
            .dashboard-container.sidebar-collapsed .sidebar { width: 80px; left: 0; }
        }
        @media (max-width: 768px) {
            .header-search-container { display: none; }
            .top-header { padding: 0 1rem; }
            .content-area { padding: 1rem; }
            .header-controls { flex-direction: column; align-items: flex-start; gap: 10px; }
            .search-box { width: 100%; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

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
                    <a href="dashboard.php" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="mdi:account-group"></iconify-icon>
                        <span class="nav-text">User Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="student.php" class="dropdown-link ">Student</a></li>
                        <li><a href="faculty.php" class="dropdown-link ">Faculty</a></li>
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

                <div class="nav-dropdown active">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <iconify-icon icon="clarity:administrator-solid"></iconify-icon>
                        <span class="nav-text">Admin Management</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="addadmin.php" class="dropdown-link">Add Administrator</a></li>
                        <li><a href="addminlist.php" class="dropdown-link active-page">Administrator List</a></li>
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
                <div class="container">
                    <h1 class="page-title">Administrator List</h1>

                    <?php if(!empty($message)): ?>
                        <div class="alert <?= $messageType ?>" style="padding: 10px; margin-bottom: 20px; border-radius: 5px; background: <?= $messageType=='success'?'#d4edda':'#f8d7da' ?>; color: <?= $messageType=='success'?'#155724':'#721c24' ?>;">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <div class="header-controls">
                        <div class="search-box">
                            <span class="iconify search-icon" data-icon="eva:search-outline"></span>
                            <input type="text" id="searchInput" placeholder="Search Administrator">
                        </div>
                        <button class="btn-add" id="openModalBtn">
                            <span class="iconify" data-icon="mdi:plus" style="font-size: 20px;"></span> Add Administrator
                        </button>
                    </div>

                    <div class="table-container">
                        <table id="adminTable">
                            <thead>
                                <tr>
                                    <th class="text-center">Employee ID</th>
                                    <th>Name</th>
                                    <th class="text-center">Role</th>
                                    <th class="text-center">Email</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php if (!empty($admins)): ?>
                                    <?php foreach ($admins as $admin): ?>
                                        <tr>
                                            <td class="text-center"><?= htmlspecialchars($admin['employee_id']) ?></td>
                                            <td><?= htmlspecialchars($admin['firstname'] . ' ' . $admin['lastname']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($admin['role']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($admin['email']) ?></td>
                                            <td class="text-center">
                                                <div class="action-cell">
                                                    <a href="editadmin.php?id=<?= $admin['id'] ?>" class="btn-icon btn-edit" title="Edit"><span class="iconify" data-icon="mdi:pencil-outline"></span></a>
                                                    <a href="addminlist.php?action=delete&id=<?= $admin['id'] ?>" class="btn-icon btn-delete" onclick="return confirm('Delete this admin?');" title="Delete"><span class="iconify" data-icon="mdi:trash-can-outline"></span></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">No records found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="footer"><div id="recordCount">Showing <?= count($admins) ?> entries</div></div>
                </div>
            </main>
        </div>
    </div>

    <div id="adminModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Administrator</h2>
                <button class="close-btn" id="closeModalX"><span class="iconify" data-icon="mdi:close"></span></button>
            </div>
            
            <form id="addAdminForm" method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <h3 class="section-header">Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group"><label>First Name</label><input type="text" name="firstName" required></div>
                    <div class="form-group"><label>Last Name</label><input type="text" name="lastName" required></div>
                    <div class="form-group"><label>Middle Name</label><input type="text" name="middleName"></div>
                    <div class="form-group"><label>Date of Birth</label><input type="date" name="dob" required></div>
                    <div class="form-group"><label>Gender</label>
                        <select name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Email Address</label><input type="email" name="email" required></div>
                </div>

                <h3 class="section-header">Account Information</h3>
                <div class="form-grid">
                    <div class="form-group"><label>Employee ID</label><input type="text" name="employeeId" required></div>
                    <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                    <div class="form-group"><label>Role</label>
                        <select name="role">
                            <option value="Admin">Administrator</option>
                            <option value="Librarian">Librarian</option>
                            <option value="Staff">Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" id="password" required>
                        <small class="password-hint">Min 8 chars, 1 uppercase, 1 lowercase, 1 digit, 1 symbol.</small>
                    </div>
                    <div class="form-group"><label>Confirm Password</label><input type="password" name="confirmPassword" id="confirmPassword" required></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn-save">Create Administrator</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // SIDEBAR
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

            // MODAL
            const modal = document.getElementById('adminModal');
            const openBtn = document.getElementById('openModalBtn');
            const closeBtn = document.getElementById('closeModalX');
            const cancelBtn = document.getElementById('cancelBtn');

            function openModal() { modal.classList.add('active'); }
            function closeModal() { modal.classList.remove('active'); }

            if(openBtn) openBtn.addEventListener('click', openModal);
            if(closeBtn) closeBtn.addEventListener('click', closeModal);
            if(cancelBtn) cancelBtn.addEventListener('click', closeModal);
            
            // STRONG PASSWORD VALIDATION (JAVASCRIPT)
            document.getElementById('addAdminForm').addEventListener('submit', (e) => {
                const pass = document.getElementById('password').value;
                const confirm = document.getElementById('confirmPassword').value;
                
                // Regex for Strong Password:
                // (?=.*[a-z]) : At least 1 lowercase
                // (?=.*[A-Z]) : At least 1 uppercase
                // (?=.*\d)    : At least 1 digit
                // (?=.*[\W_]) : At least 1 special char
                // .{8,}       : Min 8 characters total
                const strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

                if(pass !== confirm) {
                    e.preventDefault();
                    alert("Passwords do not match!");
                } else if (!strongRegex.test(pass)) {
                    e.preventDefault();
                    alert("Password is too weak! It must contain at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character.");
                }
            });

            // SEARCH
            const searchInput = document.getElementById('searchInput');
            const rows = document.querySelectorAll('#tableBody tr');
            searchInput.addEventListener('keyup', (e) => {
                const term = e.target.value.toLowerCase();
                rows.forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
                });
            });
        });
    </script>
        <script src="search.js"></script>

</body>
</html>