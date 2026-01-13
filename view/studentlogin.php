<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "saliksik";

$connection = new mysqli($servername, $username, $password, $database);

$studentnumber = "";
$passwordInput = "";

$errorMessage = "";
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentnumber = $_POST["studentnumber"];
    $passwordInput = $_POST["password"];

    if (empty($studentnumber) || empty($passwordInput)) {
        $errorMessage = "Please fill out all fields.";
    } else {
        // Check credentials
        $sql = "SELECT * FROM studacc WHERE studentnumber = '$studentnumber' LIMIT 1";
        $result = $connection->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if ($row['password'] === $passwordInput) {
                // Start session and store user info
                $_SESSION['studentnumber'] = $row['studentnumber'];
                $_SESSION['firstname'] = $row['firstname'];
                $_SESSION['middlename'] = $row['middlename'];
                $_SESSION['lastname'] = $row['lastname'];
                $_SESSION['suffix'] = $row['suffix'];
                $_SESSION['course'] = $row['course'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['section'] = $row['section'];
                $_SESSION['phonenumber'] = $row['phonenumber'];

                $successMessage = "Login successful! Redirecting...";
                header("refresh:1; url=studenthome.php");
                exit;
            } else {
                $errorMessage = "Incorrect password.";
            }
        } else {
            $errorMessage = "No account found with that student number.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SALIKSIK Student Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/19d37dc8d9.js" crossorigin="anonymous"></script>

     <style>
        :root {
            --maroon: #550000;
            --yellow: #FFD200;
            --white: #ffffff;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: url('schoolpupq.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* --- Glassmorphism / Blur Effect --- */
        .login-card {
            display: flex;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 850px;
            width: 90%;
        }

        .login-left {
            flex: 1;
            background: transparent; 
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .login-left img {
            width: 100%;
            max-width: 300px;
        }

        .login-right {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: transparent; 
        }

        .logo-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .logo-title img {
            width: 50px;
        }

        .saliksik-text {
            font-family: 'knewave', cursive;
            font-size: 28px;
            font-weight: bold;
            color: #550000;
            -webkit-text-stroke: 0.1px var(--yellow);

        }

        h2 {
            margin: 10px 0 30px;
            font-size: 22px;
            /* UPDATED: Extra Bold and Centered */
            font-weight: 900;
            text-align: center;
            color: #000;
        }

        .input-group {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #5a1a1a;
            padding-bottom: 8px;
            margin-bottom: 30px;
        }

        /* Input Styling */
        .input-group input {
            border: none;
            outline: none;
            flex-grow: 1;
            font-size: 16px;
            color: #333; 
            padding-left: 15px;
            background: transparent;
        }

        .input-group input::placeholder {
            color: #555;
        }

        .input-group i {
            font-size: 18px;
            color: #666;
            min-width: 20px;
            text-align: center;
        }

        .input-group .toggle-password {
            cursor: pointer;
            color: #4a0505;
            margin-left: 10px;
            font-size: 18px;
        }

        .input-group .toggle-password:hover {
            opacity: 0.8;
        }

        .forgot {
            text-align: right;
            margin-bottom: 20px;
        }

        .forgot a {
            color: #550000;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-signin {
            width: 100%;
            background: #550000;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-signin:hover {
            background: #dd0000;
        }

        .back-home {
            margin-top: 20px;
            text-align: left;
            font-size: 14px;
        }

        .back-home a {
            text-decoration: none;
            color: #000;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .back-home a:hover {
            text-decoration: underline;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }

        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
            }

            .login-left {
                padding: 20px;
            }

            .login-right {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-left">
            <img src="image.png" alt="Student Illustration">
        </div>

        <div class="login-right">
            <div class="logo-title">
                <img src="PUPLogo.png" alt="PUP Logo">
                <div class="saliksik-text">SALIKSIK</div>
            </div>

            <h2>WELCOME BACK</h2>

            <!-- ✅ PHP Form -->
            <form method="POST" action="">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="studentnumber" placeholder="Student Number" value="<?= $studentnumber ?>" required>
                </div>


                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="loginPassword" name="password" placeholder="Password" required>
                    <i class="fa-solid fa-eye toggle-password" id="toggleBtn"></i>
                </div>


                <?php if (!empty($errorMessage)): ?>
                    <div class="message error"><?php echo $errorMessage; ?></div>
                <?php endif; ?>

                <?php if (!empty($successMessage)): ?>
                    <div class="message success"><?php echo $successMessage; ?></div>
                <?php endif; ?>

                <div class="forgot">
                    <a href="student_forgot_password.php">Forgot Password?</a>
                </div>
                <button type="submit" class="btn-signin">Sign in</button>
            </form>

            <div class="createaccount">
                <p>Don't have an account? <a href="studentreg.php">sign up</a></p>
            </div>

            <div class="back-home">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Back to home</a>
            </div>
        </div>
    </div>
</body>
<script>
    const toggleBtn = document.querySelector('#toggleBtn');
    const loginPassword = document.querySelector('#loginPassword');

    toggleBtn.addEventListener('click', function() {
        // Toggle input type
        const type = loginPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        loginPassword.setAttribute('type', type);

        // Toggle icon visual (Optional: switches to slash icon)
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>


</html>