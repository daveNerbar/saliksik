<?php
$notifications = [];

// A. Check Overdue Books
$overdueQ = $conn->query("
    SELECT b.accessor_no, b.return_date, 
           COALESCE(s.firstname, f.firstname) as name 
    FROM borrowing b
    LEFT JOIN studacc s ON b.student_number = s.studentnumber
    LEFT JOIN facultyacc f ON b.student_number = f.pupid
    WHERE b.status = 'Borrowed' AND b.return_date < CURDATE()
    ORDER BY b.return_date ASC
");

while ($row = $overdueQ->fetch_assoc()) {
    $notifications[] = [
        'type' => 'overdue',
        'message' => "Overdue: {$row['accessor_no']} borrowed by {$row['name']}",
        'time' => $row['return_date'] // Due date
    ];
}

// B. Check New Students (Last 24 Hours)
$newStudQ = $conn->query("SELECT firstname, lastname, created_at FROM studacc WHERE created_at >= NOW() - INTERVAL 1 DAY");
while ($row = $newStudQ->fetch_assoc()) {
    $notifications[] = [
        'type' => 'new_user',
        'message' => "New Student: {$row['firstname']} {$row['lastname']}",
        'time' => $row['created_at']
    ];
}

// C. Check New Faculty (Last 24 Hours)
$newFacQ = $conn->query("SELECT firstname, lastname, created_at FROM facultyacc WHERE created_at >= NOW() - INTERVAL 1 DAY");
while ($row = $newFacQ->fetch_assoc()) {
    $notifications[] = [
        'type' => 'new_user',
        'message' => "New Faculty: {$row['firstname']} {$row['lastname']}",
        'time' => $row['created_at']
    ];
}

$notifCount = count($notifications);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        .dashboard-container {
            display: flex;
            height: 100vh;
            background-color: #f8f9fa;
            transition: all 0.3s ease-in-out;
            overflow: hidden;
        }

        /* --- SIDEBAR STYLES --- */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 300px;
            height: 100vh;
            background-color: #550000;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding-top: 0;
            overflow-y: auto;
            transition: width 0.3s ease-in-out, left 0.3s ease-in-out;
            flex-shrink: 0;
            z-index: 1000;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 20px;
            min-height: 90px;
            flex-shrink: 0;
            gap: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease-in-out;
            border-bottom: 3px solid rgba(255, 255, 255, 0.2);
        }

        .logo-image {
            height: 60px;
            width: 60px;
            object-fit: contain;
            flex-shrink: 0;
            border-radius: 0;
            transition: all 0.3s ease-in-out;
        }

        .logo-text {
            font-size: 38px;
            font-weight: normal;
            transition: opacity 0.3s ease-in-out, width 0.3s ease-in-out;
            line-height: 0.95;
            white-space: nowrap;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 0 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: white;
            transition: background-color 0.2s ease-in-out;
            position: relative;
        }

        .nav-text {
            transition: opacity 0.3s ease-in-out;
        }

        .nav-arrow {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .nav-link:hover {
            background-color: #920612;
        }

        .nav-link.active {
            background-color: #7f0510;
        }

        .nav-link iconify-icon {
            font-size: 1.80rem;
            width: 2rem;
            height: 2rem;
            text-align: center;
            flex-shrink: 0;
            color: inherit;
        }

        .knewave-font {
            font-family: 'Knewave', cursive;
            color: #711E1E;
            text-shadow: -2px -2px 0 #FFE732, 1px -1px 0 #FFE732, -1px 1px 0 #FFE732, 1px 1px 0 #FFE732;
        }

        .sidebar a {
            text-decoration: none;
        }

        /* --- DROPDOWN STYLES --- */
        .nav-dropdown {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .nav-dropdown-toggle {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: white;
            transition: background-color 0.2s ease-in-out;
            position: relative;
            cursor: pointer;
        }

        .dropdown-menu {
            list-style: none;
            padding: 0;
            margin: 0.25rem 0 0.25rem 2rem;
            display: none;
            flex-direction: column;
            gap: 0.25rem;
            font-size: 0.95rem;
        }

        .nav-dropdown.active .dropdown-menu {
            display: flex;
        }

        .dropdown-menu .dropdown-link {
            display: block;
            padding: 0.5rem 1rem;
            padding-left: 2.25rem;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: background-color 0.2s ease;
            cursor: pointer;
        }

        .dropdown-menu .dropdown-link:hover {
            background-color: #920612;
        }

        .nav-dropdown.active .nav-arrow {
            transform: rotate(90deg) !important;
        }

        .dropdown-link.active-page {
            background-color: #920612;
            color: #ffffff;
            font-weight: 600;
        }

        /* --- MAIN CONTENT & LAYOUT --- */
        .main-content {
            flex: 1;
            margin-left: 300px;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            transition: margin-left 0.3s ease-in-out;
        }

        .dashboard-container.sidebar-collapsed .sidebar {
            width: 90px;
        }

        .dashboard-container.sidebar-collapsed .main-content {
            margin-left: 90px;
        }

        .dashboard-container.sidebar-collapsed .sidebar .sidebar-logo {
            justify-content: center;
            padding: 0;
            gap: 0;
            height: 90px;
            display: flex;
            align-items: center;
        }

        .dashboard-container.sidebar-collapsed .sidebar .logo-image {
            height: 60px;
            width: 60px;
            margin: 0;
        }

        .dashboard-container.sidebar-collapsed .sidebar .logo-text {
            display: none;
            width: 0;
            opacity: 0;
        }

        .dashboard-container.sidebar-collapsed .sidebar .nav-dropdown .dropdown-menu {
            display: none !important;
        }

        .dashboard-container.sidebar-collapsed .sidebar .nav-dropdown .nav-text,
        .dashboard-container.sidebar-collapsed .sidebar .nav-dropdown .nav-arrow {
            display: none;
        }

        .dashboard-container.sidebar-collapsed .sidebar .nav-dropdown-toggle {
            justify-content: center;
            padding: 0.75rem 0;
        }

        .dashboard-container.sidebar-collapsed .sidebar .nav-link {
            justify-content: center;
            padding: 0.75rem 0;
        }

        /* Tooltip for collapsed sidebar */
        .dashboard-container.sidebar-collapsed .sidebar .nav-dropdown-toggle .nav-text,
        .dashboard-container.sidebar-collapsed .sidebar .nav-link .nav-text {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            background-color: #374151;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            z-index: 10;
            left: 105px;
            top: 50%;
            transform: translateY(-50%);
            transition: opacity 0.2s ease-in-out;
            pointer-events: none;
            white-space: nowrap;
            display: block;
        }

        .dashboard-container.sidebar-collapsed .sidebar .nav-dropdown-toggle:hover .nav-text,
        .dashboard-container.sidebar-collapsed .sidebar .nav-link:hover .nav-text {
            visibility: visible;
            opacity: 1;
        }

        /* --- HEADER STYLES --- */
        .top-header { background-color: white; border-bottom: 1px solid #e5e7eb; height: 90px; padding: 0 2rem; display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); z-index: 10; flex-shrink: 0; }
        .hamburger-button { background: none; border: none; cursor: pointer; color: #4b5563; padding: 0.2rem; display: flex; align-items: center; justify-content: center; }
        .hamburger-button iconify-icon { font-size: 1.5rem; }
        .search-container { display: flex; align-items: center; color: #9ca3af; background-color: white; border: 1px solid #d1d5db; border-radius: 10px; padding: 0.5rem 1rem; max-width: 400px; justify-self: start; width: 100%; margin-left: 2rem; }
        .search-container input { border: none; outline: none; font-size: 0.9rem; margin-left: 0.5rem; width: 100%; font-family: 'Poppins', sans-serif; }
        .header-profile { display: flex; align-items: center; gap: 1rem; }
        .bell-icon { font-size: 1.5rem; color: #820000; cursor: pointer; }
        .profile-info { position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding-bottom: 10px; /* Bridge the gap for hover */
            margin-bottom: -10px; }
        .profile-text { display: flex; flex-direction: column; align-items: flex-end; }
        .profile-name { font-weight: 500; color: #820000; line-height: 1.2; }
        .profile-role { font-size: 0.875rem; color: #6b7280; }
        .profile-icon-wrapper { width: 40px; height: 40px; background-color: #f3f4f6; border: 1px solid #d1d5db; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .profile-icon-wrapper:hover { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .profile-icon { font-size: 1.75rem; color: #4b5563; }
        
         /* Dropdown Styling */
        .profile-dropdown {
            display: none; /* Hidden by default */
            position: absolute;
            top: 80%;
            right: 0;
            background-color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            width: 150px;
            z-index: 100;
            overflow: hidden;
            margin-top: 5px;
        }

        /* Show on hover */
        .profile-info:hover .profile-dropdown {
            display: block;
            animation: fadeIn 0.2s ease-in-out;
        }

        .profile-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            text-decoration: none;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }

        .profile-dropdown a:hover {
            background-color: #f8f9fa;
            color: #920612;
        }

        .profile-dropdown iconify-icon {
            font-size: 18px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .notif-wrapper {
            position: relative;
            cursor: pointer;
        }

        .bell-icon {
            font-size: 1.5rem;
            color: #820000;
        }

        .notif-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            font-size: 10px;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid white;
        }

        .notif-dropdown {
            display: none;
            position: absolute;
            top: 40px;
            right: 0;
            width: 300px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            z-index: 2000;
            overflow: hidden;
            border: 1px solid #eee;
        }

        .notif-wrapper.active .notif-dropdown {
            display: block;
        }

        .notif-header {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            font-weight: 700;
            font-size: 14px;
            background: #f9f9f9;
            color: #333;
        }

        .notif-list {
            max-height: 300px;
            overflow-y: auto;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .notif-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            gap: 10px;
            align-items: start;
        }

        .notif-item:last-child {
            border-bottom: none;
        }

        .notif-item:hover {
            background: #fafafa;
        }

        .notif-icon {
            flex-shrink: 0;
            margin-top: 3px;
            font-size: 16px;
        }

        .icon-warn {
            color: #f59e0b;
        }

        .icon-info {
            color: #3b82f6;
        }

        .notif-content p {
            margin: 0;
            font-size: 13px;
            color: #333;
            line-height: 1.4;
        }

        .notif-time {
            font-size: 11px;
            color: #888;
            display: block;
            margin-top: 3px;
        }

        /* CONTENT */
        .content-area { flex: 1; padding: 2rem; overflow-y: auto; height: 100%; }

        /* --- GLOBAL SEARCH DROPDOWN STYLES --- */
.search-container {
    position: relative; /* Crucial for positioning the dropdown */
}

.search-results-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background: white;
    border: 1px solid #d1d5db;
    border-top: none;
    border-radius: 0 0 10px 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    z-index: 1000;
    display: none; /* Hidden by default */
    max-height: 400px;
    overflow-y: auto;
}

.search-result-item {
    padding: 10px 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s;
    text-decoration: none;
    color: #374151;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-item:hover {
    background-color: #f9fafb;
}

.result-icon {
    width: 30px;
    height: 30px;
    background: #f3f4f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #555;
}

.result-info h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}

.result-info span {
    font-size: 12px;
    color: #6b7280;
}

/* Badge colors for types */
.type-badge {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: auto;
    font-weight: 600;
}
.badge-Student { background: #e0e7ff; color: #4338ca; }
.badge-Faculty { background: #dcfce7; color: #15803d; }
.badge-Admin { background: #fee2e2; color: #b91c1c; }
.badge-Book { background: #ffedd5; color: #c2410c; }


    </style>
    
</head>
<body>
    <script>
    
    </script>
</body>
</html>