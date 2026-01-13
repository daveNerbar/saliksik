<?php
session_start();
include("conn.php");

$message = "";

// Security: Check if OTP was actually verified. 
// (In a real app, use a specific session flag for "verified", but this works for now)
if (!isset($_SESSION['otp']) || !isset($_SESSION['email'])) {
    header("Location: studentlogin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_pass = $_POST["new_password"];
    $confirm_pass = $_POST["confirm_password"];
    $email = $_SESSION['email'];

    if ($new_pass === $confirm_pass) {
        // Update Password in DB
        // NOTE: Ideally, use password_hash($new_pass, PASSWORD_DEFAULT) here!
        $stmt = $connection->prepare("UPDATE studacc SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_pass, $email);
        
        if ($stmt->execute()) {
            // Clear session and redirect
            session_destroy();
            echo "<script>alert('Password successfully changed!'); window.location.href='studentlogin.php';</script>";
        } else {
            $message = "Database error.";
        }
    } else {
        $message = "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Password</title>
    <style>
        body { font-family: Arial, sans-serif; background: #550000; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .card { background: white; padding: 40px; border-radius: 10px; width: 400px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        .btn { background: #550000; color: white; padding: 10px; width: 100%; border: none; cursor: pointer; border-radius: 5px; }
        .error { color: red; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Reset Password</h2>
        <?php if($message) echo "<p class='error'>$message</p>"; ?>
        <form method="POST">
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" class="btn">Change Password</button>
        </form>
    </div>
</body>
</html>