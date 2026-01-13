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
    header("Location: index.php");
    exit();
}

// 2. Get User ID from Session
$admin_id = $_SESSION['admin_id'];

// 3. Process Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and sanitize inputs
    $fname = trim($_POST['first_name']);
    $lname = trim($_POST['last_name']);
    $mname = trim($_POST['middle_name']);
    $uname = trim($_POST['username']);
    $dob   = $_POST['dob'];
    $gender = $_POST['gender'];
    $email = trim($_POST['email']);

    // Basic Validation
    if (empty($fname) || empty($lname) || empty($email)) {
        echo "<script>
                alert('Error: First Name, Last Name, and Email are required.');
                window.location.href='profile.php';
              </script>";
        exit();
    }

    // 4. Update Database
    // We update First Name, Last Name, Middle Name, DOB, Gender, and Email
    $sql = "UPDATE admins SET 
            firstname = ?, 
            lastname = ?, 
            middlename = ?, 
            username = ?,
            dob = ?, 
            gender = ?, 
            email = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    
    // Bind parameters: s=string, i=integer
    $stmt->bind_param("sssssssi", $fname, $lname, $mname, $uname, $dob, $gender, $email, $admin_id);

    if ($stmt->execute()) {
        // 5. Update Session Data (Optional but recommended so header name updates immediately)
        $_SESSION['admin_name'] = $fname . ' ' . $lname;

        // Success Message
        echo "<script>
                alert('Profile updated successfully!');
                window.location.href='profile.php';
              </script>";
    } else {
        // Error Message
        echo "<script>
                alert('Database Error: Unable to update profile.');
                window.location.href='profile.php';
              </script>";
    }

    $stmt->close();
    $conn->close();

} else {
    // If someone tries to access this file directly without submitting form
    header("Location: profile.php");
    exit();
}
?>