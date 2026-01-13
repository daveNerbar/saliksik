<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "saliksik";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php"); // Redirect to login if not logged in
    exit();
}

// 2. Get User ID from Session
$admin_id = $_SESSION['admin_id'];

// 3. Fetch User Data
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Profile | SALIKSIK</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include 'header.php'; ?>

    <style>
        /* ========================================= */
        /* CORE DASHBOARD CSS                        */
        /* ========================================= */
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

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



        .content-area {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            height: 100%;
        }

        /* ========================================= */
        /* PROFILE PAGE CSS                 */
        /* ========================================= */
        .profile-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 30px 40px;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            /* Adjusted max-width */
        }

        .page-title {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 25px 0;
            color: #000;
        }

        .section-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #550000;
            margin: 0;
        }

        .icon-btn {
            background: none;
            border: none;
            font-size: 22px;
            color: #666;
            cursor: pointer;
            transition: color 0.2s;
        }

        .icon-btn:hover {
            color: #333;
        }

        .divider {
            border: 0;
            height: 2px;
            background-color: #eee;
            margin: 8px 0 20px 0;
            opacity: 0.8;
        }

        .mt-30 {
            margin-top: 30px;
        }

        .personal-info-layout {
            display: flex;
            gap: 40px;
        }

        /* Adjusted form column to be full width since photo column is gone */
        .form-column {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            align-items: center;
        }

        .form-row label {
            font-weight: 600;
            font-size: 14px;
            color: #000;
        }

        .form-row input,
        .form-row select {
            padding: 9px 12px;
            border-radius: 6px;
            border: 1px solid transparent;
            background-color: #f2f2f2;
            font-size: 13px;
            color: #333;
            outline: none;
            width: 100%;
        }

        .form-row input:not([readonly]),
        .form-row select:not([disabled]) {
            background-color: #fff;
            border: 1px solid #000000;
        }

        .contact-info-layout {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 100%;
        }

        .footer-actions {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-save {
            background-color: #992222;
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s, opacity 0.2s;
        }

        .btn-save:hover {
            background-color: #7a1515;
        }

        .btn-save:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #999;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 5px;
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
                <a href="dashboard.php" class="nav-link "><iconify-icon icon="mdi:view-dashboard"></iconify-icon><span class="nav-text">Dashboard</span></a>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle"><iconify-icon icon="mdi:account-group"></iconify-icon><span class="nav-text">User Management</span><span class="nav-arrow">&rsaquo;</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="student.php" class="dropdown-link">Student</a></li>
                        <li><a href="faculty.php" class="dropdown-link">Faculty</a></li>
                        <li><a href="userreport.php" class="dropdown-link">User Report</a></li>
                    </ul>
                </div>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle"><iconify-icon icon="mdi:bookshelf"></iconify-icon><span class="nav-text">Book Management</span><span class="nav-arrow">&rsaquo;</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="addbook.php" class="dropdown-link">Add Book</a></li>
                        <li><a href="manbook.php" class="dropdown-link">Manage Book</a></li>
                        <li><a href="bookreport.php" class="dropdown-link">Book Report</a></li>
                        <li><a href="unusedbooks.php" class="dropdown-link">Unused Books</a></li>
                    </ul>
                </div>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle"><iconify-icon icon="mdi:swap-horizontal"></iconify-icon><span class="nav-text">Borrowing Management</span><span class="nav-arrow">&rsaquo;</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="bookborrow.php" class="dropdown-link">Add Borrow</a></li>
                        <li><a href="borrowedlist.php" class="dropdown-link">Return Book</a></li>
                        <li><a href="reservebooks.php" class="dropdown-link">Reserved Book</a></li>
                        <li><a href="borrowedhistory.php" class="dropdown-link">Borrowed History</a></li>
                        <li><a href="borrowedreport.php" class="dropdown-link">Borrowed Report</a></li>
                    </ul>
                </div>

                <a href="annceve.php" class="nav-link"><iconify-icon icon="mdi:bullhorn"></iconify-icon><span class="nav-text">Announcements & Events</span></a>

                <div class="nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle"><iconify-icon icon="clarity:administrator-solid"></iconify-icon><span class="nav-text">Admin Management</span><span class="nav-arrow">&rsaquo;</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="addadmin.php" class="dropdown-link">Add Administrator</a></li>
                        <li><a href="addminlist.php" class="dropdown-link">Administrator List</a></li>
                    </ul>
                </div>
            </nav>
        </aside>

        <div class="main-content">
            <header class="top-header">
                <button class="hamburger-button" id="hamburger-btn"><iconify-icon icon="mdi:menu"></iconify-icon></button>
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
                            <div class="profile-icon-wrapper"><iconify-icon icon="mdi:account-tie" class="profile-icon"></iconify-icon></div>
                        </a>

                        <div class="profile-dropdown">
                            <a href="logout.php">
                                <iconify-icon icon="mdi:logout"></iconify-icon> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <div class="profile-container">
                    <h1 class="page-title">Profile</h1>
                    <div class="content-wrapper">
                        <div class="section-header-row">
                            <h2 class="section-title">Personal Information</h2>
                            <button class="icon-btn" id="editBtn" title="Edit Profile">
                                <span class="iconify" data-icon="mdi:pencil-box-outline"></span>
                            </button>
                        </div>
                        <hr class="divider">
                        <form method="POST" action="profile_update.php">
                            <div class="personal-info-layout">
                                <div class="form-column">
                                    <div class="form-row">
                                        <label>First Name</label>
                                        <input type="text" id="firstName" name="first_name" value="<?= htmlspecialchars($user['firstname']) ?>" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label>Last Name</label>
                                        <input type="text" id="lastName" name="last_name" value="<?= htmlspecialchars($user['lastname']) ?>" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label>Middle Name</label>
                                        <input type="text" id="middleName" name="middle_name" value="<?= htmlspecialchars($user['middlename']) ?>" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label>Username</label>
                                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                                    </div>


                                    <div class="form-row">
                                        <label>Date of Birth</label>
                                        <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($user['dob']) ?>" readonly>
                                    </div>
                                    <div class="form-row">
                                        <label>Gender</label>
                                        <select id="gender" name="gender" disabled>
                                            <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                            <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                                            <option value="Other" <?= $user['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="form-row">
                                        <label>Position</label>
                                        <input type="text" id="position" name="position" value="<?= htmlspecialchars($user['role']) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <h2 class="section-title mt-30">Contact Information</h2>
                            <hr class="divider">
                            <div class="contact-info-layout">
                                <div class="form-row">
                                    <label>Email Address</label>
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                </div>
                            </div>
                            <div class="footer-actions">
                                <button id="saveBtn" class="btn-save" disabled>Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="search.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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

            // PROFILE LOGIC
            const editBtn = document.getElementById('editBtn');
            const saveBtn = document.getElementById('saveBtn');
            const formElements = document.querySelectorAll('.profile-container input, .profile-container select');
            const iconSpan = editBtn.querySelector('.iconify');
            let isEditing = false;

            editBtn.addEventListener('click', (e) => {
                e.preventDefault();
                isEditing = !isEditing;
                if (isEditing) {
                    formElements.forEach(input => {
                        input.removeAttribute('readonly');
                        input.disabled = false;
                    });
                    saveBtn.disabled = false;
                    document.getElementById('firstName').focus();
                    iconSpan.setAttribute('data-icon', 'mdi:close-box-outline');
                } else {
                    disableEditing();
                }
            });

            function disableEditing() {
                isEditing = false;
                formElements.forEach(input => {
                    input.setAttribute('readonly', true);
                    if (input.tagName === 'SELECT') input.disabled = true;
                });
                saveBtn.disabled = true;
                iconSpan.setAttribute('data-icon', 'mdi:pencil-box-outline');
            }
        });
    </script>
</body>

</html>