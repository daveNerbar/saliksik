<?php
// search_query.php
include("connection.php"); 

if (isset($_POST['query'])) {
    $searchText = $conn->real_escape_string($_POST['query']);
    $results = [];

    // 1. Search Students
    $sql = "SELECT 'Student' as type, firstname, lastname, studentnumber as id_val, 'student.php' as link 
            FROM studacc 
            WHERE firstname LIKE '%$searchText%' OR lastname LIKE '%$searchText%' OR studentnumber LIKE '%$searchText%' 
            LIMIT 3";
    $query = $conn->query($sql);
    while($row = $query->fetch_assoc()) { $results[] = $row; }

    // 2. Search Faculty
    $sql = "SELECT 'Faculty' as type, firstname, lastname, pupid as id_val, 'faculty.php' as link 
            FROM facultyacc 
            WHERE firstname LIKE '%$searchText%' OR lastname LIKE '%$searchText%' OR pupid LIKE '%$searchText%' 
            LIMIT 3";
    $query = $conn->query($sql);
    while($row = $query->fetch_assoc()) { $results[] = $row; }

    // 3. Search Admins
    $sql = "SELECT 'Admin' as type, firstname, lastname, employee_id as id_val, 'addminlist.php' as link 
            FROM admins 
            WHERE firstname LIKE '%$searchText%' OR lastname LIKE '%$searchText%' OR username LIKE '%$searchText%' 
            LIMIT 3";
    $query = $conn->query($sql);
    while($row = $query->fetch_assoc()) { $results[] = $row; }

    // 4. Search Books
    $sql = "SELECT 'Book' as type, book_title as firstname, '' as lastname, call_number as id_val, 'manbook.php' as link 
            FROM books 
            WHERE book_title LIKE '%$searchText%' OR isbn LIKE '%$searchText%' 
            LIMIT 3";
    $query = $conn->query($sql);
    while($row = $query->fetch_assoc()) { $results[] = $row; }

    // Return JSON
    echo json_encode($results);
}
?>