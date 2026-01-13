<?php
// Load PHPMailer classes manually
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

session_start();
include("connection.php"); 

$message = "";
$messageType = ""; 
$step = isset($_SESSION['step']) ? $_SESSION['step'] : 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- STEP 1: CHECK EMAIL & SEND OTP ---
    if (isset($_POST['send_otp'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        // Note: You are querying 'studacc'. Ensure this is the correct table.
        $check = $conn->query("SELECT * FROM admins WHERE email = '$email'");
        
        if ($check->num_rows > 0) {
            $otp = rand(100000, 999999); 
            $_SESSION['otp'] = $otp;
            $_SESSION['reset_email'] = $email;
            
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'davidnerbar@gmail.com'; // Your email
                $mail->Password   = 'pmzt htab pknm eogc';   // Your App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
                $mail->Port       = 587;

                $mail->setFrom('no-reply@saliksik.com', 'Saliksik Admin');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP';
                $mail->Body    = 'Your One-Time Password (OTP) is: <b style="font-size: 20px;">' . $otp . '</b>';

                $mail->send();
                
                $_SESSION['step'] = 2; 
                $step = 2;
                $message = "OTP sent to your email!";
                $messageType = "success";
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                $messageType = "error";
            }
        } else {
            $message = "Email not found in our records.";
            $messageType = "error";
        }
    }

    // --- STEP 2: VERIFY OTP ---
    if (isset($_POST['verify_otp'])) {
        $otp_input = $_POST['otp_code'];
        
        if ($otp_input == $_SESSION['otp']) {
            $_SESSION['step'] = 3; 
            $step = 3;
            $message = "OTP Verified. Set new password.";
            $messageType = "success";
        } else {
            $message = "Invalid OTP. Please try again.";
            $messageType = "error";
        }
    }

    // --- STEP 3: CHANGE PASSWORD ---
    if (isset($_POST['change_password'])) {
        $new_pass = $_POST['new_pass'];
        $confirm_pass = $_POST['confirm_pass'];
        $email = $_SESSION['reset_email'];

        // 1. Regex patterns
        $uppercase    = preg_match('@[A-Z]@', $new_pass);
        $lowercase    = preg_match('@[a-z]@', $new_pass);
        $number       = preg_match('@[0-9]@', $new_pass);
        $specialChars = preg_match('@[^\w]@', $new_pass);

        // --- NEW LOGIC: CHECK PREVIOUS PASSWORD ---
        $checkOld = $conn->query("SELECT password FROM admins WHERE email = '$email'");
        $oldRow = $checkOld->fetch_assoc();
        $currentDatabasePassword = $oldRow['password'];

        // 2. Check Password Strength
        if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($new_pass) < 8) {
            $message = "Password must be at least 8 characters and include at least one uppercase letter, one number, and one special character.";
            $messageType = "error";
        } 
        // 3. Check if passwords match
        elseif ($new_pass !== $confirm_pass) {
            $message = "Passwords do not match.";
            $messageType = "error";
        }
        // 4. Check if new password is same as old password
        elseif ($new_pass === $currentDatabasePassword) {
            $message = "You cannot use your previous password. Please choose a new one.";
            $messageType = "error";
        }
        // 5. Update Database
        else {
            // NOTE: Ideally use password_hash() here, but keeping plain text as per your setup
            $sql = "UPDATE admins SET password = '$new_pass' WHERE email = '$email'";
            
            if ($conn->query($sql)) {
                session_destroy();
                echo "<script>alert('Password changed successfully!'); window.location.href='adminlogin.php';</script>";
            } else {
                $message = "Database error: " . $conn->error;
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
    <title>Forgot Password | SALIKSIK</title>
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/19d37dc8d9.js" crossorigin="anonymous"></script>
    <style>
        :root { --maroon: #550000; --yellow: #FFD200; --white: #ffffff; }
        
        body { 
            margin: 0; padding: 0; font-family: Arial, sans-serif; 
            background: url('puppq.jpg') no-repeat center center fixed; 
            background-size: cover; display: flex; 
            justify-content: center; align-items: center; height: 100vh; 
        }
        
        .login-card { 
            display: flex; background: #fff; border-radius: 12px; 
            box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.2); overflow: hidden; 
            max-width: 850px; width: 90%; 
        }
        
        .login-left { 
            flex: 1; background: #fff; display: flex; 
            align-items: center; justify-content: center; padding: 30px; 
        }
        .login-left img { width: 100%; max-width: 300px; }
        
        .login-right { 
            flex: 1; padding: 40px; display: flex; 
            flex-direction: column; justify-content: center; 
        }
        
        .logo-title { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .logo-title img { width: 50px; }
        .saliksik-text { 
            font-family: 'knewave', cursive; font-size: 28px; 
            font-weight: bold; color: #550000; -webkit-text-stroke: 0.1px var(--yellow); 
        }
        
        h2 { margin: 10px 0 20px; font-size: 22px; font-weight: bold; color: #000; }
        
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group input { 
            width: 100%; padding: 12px 40px 5px 35px; border: none; 
            border-bottom: 2px solid #550000; font-size: 15px; 
            outline: none; box-sizing: border-box; 
        }
        
        /* Left Icons */
        .input-group i.fa-envelope, 
        .input-group i.fa-key,
        .input-group i.fa-lock { 
            position: absolute; left: 8px; top: 50%; 
            transform: translateY(-50%); color: #555; 
        }

        /* --- STYLE FOR SHOW PASSWORD EYE ICON --- */
        .toggle-password {
            position: absolute;
            right: 15px; /* Position on right side */
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #550000;
            z-index: 10;
        }
        
        .btn-signin { 
            width: 100%; background: #550000; color: #fff; 
            padding: 12px; border: none; border-radius: 25px; 
            font-size: 16px; font-weight: bold; cursor: pointer; 
            transition: background 0.3s; margin-top: 10px; 
        }
        .btn-signin:hover { background: #dd0000; }
        
        .back-home { margin-top: 20px; text-align: left; font-size: 14px; }
        .back-home a { text-decoration: none; color: #000; display: flex; align-items: center; gap: 5px; }
        .back-home a:hover { text-decoration: underline; }
        
        .message { 
            padding: 10px; border-radius: 5px; margin-bottom: 15px; 
            font-size: 13px; font-weight: bold; text-align: center; 
        }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        .password-hint {
            font-size: 11px; color: #666; margin-top: -15px; margin-bottom: 15px; line-height: 1.4;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-left">
            <img src="puplogo.png" alt="Illustration">
        </div>

        <div class="login-right">
            <div class="logo-title">
                <img src="puplogo.png" alt="PUP Logo">
                <div class="saliksik-text">SALIKSIK</div>
            </div>

            <?php if ($message != ""): ?>
                <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <h2>RESET PASSWORD</h2>
                <p style="margin-bottom:20px; color:#666; font-size:14px;">Enter your email to receive an OTP.</p>
                <form method="POST">
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Enter Email Address" required>
                    </div>
                    <button type="submit" name="send_otp" class="btn-signin">Send OTP</button>
                </form>
            <?php endif; ?>

            <?php if ($step == 2): ?>
                <h2>VERIFY OTP</h2>
                <p style="margin-bottom:20px; color:#666; font-size:14px;">Code sent to <b><?php echo $_SESSION['reset_email']; ?></b></p>
                <form method="POST">
                    <div class="input-group">
                        <i class="fas fa-key"></i>
                        <input type="number" name="otp_code" placeholder="Enter 6-digit OTP" required>
                    </div>
                    <button type="submit" name="verify_otp" class="btn-signin">Verify Code</button>
                </form>
            <?php endif; ?>

            <?php if ($step == 3): ?>
                <h2>NEW PASSWORD</h2>
                <form method="POST">
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="newPass" name="new_pass" placeholder="New Password" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePass('newPass', this)"></i>
                    </div>
                    <div class="password-hint">
                        Must contain: 8+ chars, 1 Uppercase, 1 Number, 1 Special Char.
                    </div>

                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirmPass" name="confirm_pass" placeholder="Confirm New Password" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePass('confirmPass', this)"></i>
                    </div>
                    <button type="submit" name="change_password" class="btn-signin">Change Password</button>
                </form>
            <?php endif; ?>

            <div class="back-home">
                <a href="adminlogin.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        function togglePass(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>