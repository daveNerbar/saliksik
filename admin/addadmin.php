<?php
// --- 1. DATABASE CONNECTION ---
include("connection.php"); 

$message = "";
$messageType = "";

// --- 2. HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // PHP Password Strength Validation
    $passwordRegex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";

    if ($_POST['password'] !== $_POST['confirmPassword']) {
        $message = "Error: Passwords do not match!";
        $messageType = "error";
    } elseif (!preg_match($passwordRegex, $pass)) {
        $message = "Error: Password must be at least 8 characters long and include an uppercase letter, a number, and a special character.";
        $messageType = "error";
    } else {
        // [UPDATED] Check if Username OR Employee ID OR Email already exists
        $check = $conn->prepare("SELECT id FROM admins WHERE username = ? OR employee_id = ? OR email = ?");
        $check->bind_param("sss", $user, $empId, $email); // Added 3rd 's' and $email variable
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            // [UPDATED] Error message now includes Email
            $message = "Error: Username, Employee ID, or Email Address already exists!";
            $messageType = "error";
        } else {
            // Insert New Admin
            $stmt = $conn->prepare("INSERT INTO admins (firstname, lastname, middlename, dob, gender, email, employee_id, username, role, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $fname, $lname, $mname, $dob, $gender, $email, $empId, $user, $role, $pass);

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Administrator | Saliksik</title>

    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include 'header.php'; ?>
    <style>
        /* --- GLOBAL STYLES --- */
        @import url('https://fonts.googleapis.com/css2?family=Knewave&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; margin: 0; color: #374151; height: 100vh; overflow: hidden; }

        /* CONTENT */
        .content-area { flex: 1; padding: 2rem; overflow-y: auto; height: 100%; }

        /* FORM STYLES */
        .admin-form-container { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); max-width: 1200px; width: 100%; margin: 0 auto; }
        .form-title { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 1.5rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        .section-header { font-size: 1.1rem; font-weight: 600; color: #334155; margin-bottom: 15px; margin-top: 20px; border-left: 4px solid #800000; padding-left: 10px; }
        .section-header:first-of-type { margin-top: 0; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .full-width { grid-column: span 2; }
        .form-group { margin-bottom: 5px; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 5px; color: #334155; font-size: 0.9rem; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; outline: none; font-family: 'Poppins', sans-serif; }
        
        /* PASSWORD TOGGLE */
        .password-wrapper { position: relative; width: 100%; }
        .password-wrapper input { padding-right: 40px; }
        .toggle-password-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #64748b; font-size: 1.2rem; display: flex; align-items: center; }
        .toggle-password-icon:hover { color: #800000; }
        .form-group input:focus { border-color: #800000; box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1); }

        .btn-container { display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 20px; }
        .btn { padding: 10px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 0.95rem; font-family: 'Poppins', sans-serif; }
        .btn-cancel { background: #e2e8f0; color: #475569; }
        .btn-submit { background: #800000; color: white; }
        .btn:hover { opacity: 0.9; }

        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
        .alert.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* PASSWORD STRENGTH STYLES */
        .password-requirements { font-size: 12px; margin-top: 5px; color: #666; }
        .strength-weak { color: #dc2626; font-weight: bold; }
        .strength-medium { color: #d97706; font-weight: bold; }
        .strength-strong { color: #16a34a; font-weight: bold; }
        .btn-submit:disabled { background-color: #ccc; cursor: not-allowed; opacity: 0.7; }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <div class="dashboard-container" id="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <a href="dashboard.php">
                <div class="sidebar-logo">
                    <img src="puplogo.png" alt="PUP Logo" class="logo-image">
                    <span class="logo-text knewave-font">SALIKSIK</span>
                </div>
            </a>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-link"><iconify-icon icon="mdi:view-dashboard"></iconify-icon><span class="nav-text">Dashboard</span></a>
                
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

                <div class="nav-dropdown active">
                    <a href="#" class="nav-link nav-dropdown-toggle"><iconify-icon icon="clarity:administrator-solid"></iconify-icon><span class="nav-text">Admin Management</span><span class="nav-arrow">&rsaquo;</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="addadmin.php" class="dropdown-link active-page">Add Administrator</a></li>
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
                        <?php if (isset($notifCount) && $notifCount > 0): ?>
                            <span class="notif-badge"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
                        <?php endif; ?>
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

            <main class="content-area">
                <div class="admin-form-container">
                    <h2 class="form-title">Add New Administrator</h2>

                    <?php if (!empty($message)): ?>
                        <div class="alert <?= $messageType ?>">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <form id="addAdminForm" method="POST" action="">

                        <div class="section-header">Personal Information</div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" id="firstName" name="firstName" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" id="lastName" name="lastName" required>
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" id="middleName" name="middleName">
                            </div>
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" id="dob" name="dob" required>
                            </div>

                            <div class="form-group">
                                <label>Gender</label>
                                <select id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="section-header">Account Information</div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Employee ID</label>
                                <input type="text" id="employeeId" name="employeeId" required>
                            </div>

                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" id="username" name="username" required>
                            </div>

                            <div class="form-group full-width">
                                <label>Role</label>
                                <select id="role" name="role">
                                    <option value="Admin">Administrator</option>
                                    <option value="Librarian">Librarian</option>
                                    <option value="Staff">Staff</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <div class="password-wrapper">
                                    <input type="password" id="password" name="password" required>
                                    <span class="toggle-password-icon" onclick="togglePass('password', this)">
                                        <iconify-icon icon="mdi:eye"></iconify-icon>
                                    </span>
                                </div>
                                <div id="password-strength" class="password-requirements"></div>
                            </div>

                            <div class="form-group">
                                <label>Confirm Password</label>
                                <div class="password-wrapper">
                                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                                    <span class="toggle-password-icon" onclick="togglePass('confirmPassword', this)">
                                        <iconify-icon icon="mdi:eye"></iconify-icon>
                                    </span>
                                </div>
                                <div id="match-message" class="password-requirements"></div>
                            </div>
                        </div>

                        <div class="btn-container">
                            <button type="button" class="btn btn-cancel" id="cancelBtn">Cancel</button>
                            <button type="submit" class="btn btn-submit" id="submitBtn">Create Administrator</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        // --- 1. SHOW/HIDE PASSWORD LOGIC ---
        function togglePass(inputId, iconSpan) {
            const input = document.getElementById(inputId);
            const icon = iconSpan.querySelector('iconify-icon');
            
            if (input.type === "password") {
                input.type = "text";
                icon.setAttribute("icon", "mdi:eye-off"); 
            } else {
                input.type = "password";
                icon.setAttribute("icon", "mdi:eye"); 
            }
        }

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

        // --- 2. PASSWORD STRENGTH VALIDATION ---
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirmPassword');
        const strengthText = document.getElementById('password-strength');
        const matchMessage = document.getElementById('match-message');
        const submitBtn = document.getElementById('submitBtn');

        function checkStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[\W_]/)) strength++; // Special char

            return strength;
        }

        function validateForm() {
            const val = passwordInput.value;
            const strength = checkStrength(val);
            const match = val === confirmInput.value;
            let isValid = true;

            // Update Strength Text
            if (val.length === 0) {
                strengthText.textContent = "";
                strengthText.className = "password-requirements";
                isValid = false;
            } else if (strength < 3) {
                strengthText.textContent = "Weak: Must contain 8+ chars, upper, lower, numbers & special chars.";
                strengthText.className = "password-requirements strength-weak";
                isValid = false;
            } else if (strength < 5) {
                strengthText.textContent = "Medium: Almost there...";
                strengthText.className = "password-requirements strength-medium";
            } else {
                strengthText.textContent = "Strong Password!";
                strengthText.className = "password-requirements strength-strong";
            }

            // Update Match Text
            if (confirmInput.value.length > 0) {
                if (match) {
                    matchMessage.textContent = "Passwords Match";
                    matchMessage.className = "password-requirements strength-strong";
                } else {
                    matchMessage.textContent = "Passwords do not match";
                    matchMessage.className = "password-requirements strength-weak";
                    isValid = false;
                }
            } else {
                matchMessage.textContent = "";
            }

            // Disable Submit if invalid
            submitBtn.disabled = !isValid || !match || strength < 3;
        }

        passwordInput.addEventListener('input', validateForm);
        confirmInput.addEventListener('input', validateForm);

        // Form Cancel Logic
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('addAdminForm');
            const cancelBtn = document.getElementById('cancelBtn');

            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => {
                    if (confirm("Are you sure you want to cancel? All data will be lost.")) {
                        form.reset();
                        strengthText.textContent = "";
                        matchMessage.textContent = "";
                    }
                });
            }
        });

        // Search Logic
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('globalSearchInput');
            const resultsContainer = document.getElementById('globalSearchResults');

            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const query = this.value.trim();

                    if (query.length > 1) { 
                        const formData = new FormData();
                        formData.append('query', query);

                        fetch('search_query.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                resultsContainer.innerHTML = '';
                                if (data.length > 0) {
                                    resultsContainer.style.display = 'block';
                                    data.forEach(item => {
                                        let icon = 'mdi:account';
                                        if (item.type === 'Book') icon = 'mdi:book-open-variant';
                                        if (item.type === 'Admin') icon = 'clarity:administrator-solid';

                                        const link = `${item.link}?search=${encodeURIComponent(item.id_val)}`;
                                        const html = `
                                    <a href="${link}" class="search-result-item">
                                        <div class="result-icon">
                                            <iconify-icon icon="${icon}"></iconify-icon>
                                        </div>
                                        <div class="result-info">
                                            <h4>${item.firstname} ${item.lastname}</h4>
                                            <span>${item.id_val}</span>
                                        </div>
                                        <span class="type-badge badge-${item.type}">${item.type}</span>
                                    </a>
                                `;
                                        resultsContainer.innerHTML += html;
                                    });
                                } else {
                                    resultsContainer.style.display = 'block';
                                    resultsContainer.innerHTML = '<div class="search-result-item" style="cursor:default; color:#888;">No results found</div>';
                                }
                            })
                            .catch(error => console.error('Error:', error));
                    } else {
                        resultsContainer.style.display = 'none';
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                        resultsContainer.style.display = 'none';
                    }
                });
            }
        });
    </script>
    <script src="search.js"></script>
</body>

</html>