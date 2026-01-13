<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "saliksik";

$conn = new mysqli($servername, $username, $password, $database);


$pupid = "";
$passwordInput = "";

$errorMessage = "";
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pupid = $_POST["pupid"];
    $passwordInput = $_POST["password"];

    if (empty($pupid) || empty($passwordInput)) {
        $errorMessage = "Please fill out all fields.";
    } else {
        // Check credentials
        $sql = "SELECT * FROM facultyacc WHERE pupid = '$pupid' LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if ($row['password'] === $passwordInput) {

                // START SESSION
                $_SESSION['pupid'] = $row['pupid'];
                $_SESSION['firstname'] = $row['firstname'];
                $_SESSION['middlename'] = $row['middlename'];
                $_SESSION['lastname'] = $row['lastname'];
                $_SESSION['suffix'] = $row['suffix'];
                $_SESSION['phonenumber'] = $row['phonenumber'];
                $_SESSION['department'] = $row['department'];
                $_SESSION['email'] = $row['email'];


                $successMessage = "Login successful! Redirecting...";
                header("refresh:1; url=facultyhome.php");
                exit;

            } else {
                $errorMessage = "Incorrect password.";
            }
        } else {
            $errorMessage = "No account found with that PUP ID.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SALIKSIK Faculty Login</title>
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
            /* UPDATED: Extra bold weight */
            font-weight: 900; 
            color: #000;
            text-align: center; 
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            border-bottom: 2px solid #550000;
            padding-bottom: 5px;
        }

        .input-group input {
            width: 100%;
            padding: 10px 10px 10px 35px;
            border: none;
            background: transparent;
            font-size: 15px;
            outline: none;
            color: #333;
        }

        .input-group i {
            position: absolute;
            left: 5px;
            color: #555;
            font-size: 16px;
        }

        .input-group .toggle-password {
            position: absolute;
            right: 10px;
            left: auto;
            cursor: pointer;
            color: #550000;
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
        
        .createaccount {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }
        
        .createaccount a {
            color: #550000;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-left">
            <img src="image1.png" alt="Faculty Illustration">
        </div>

        <div class="login-right">
            <div class="logo-title">
                <img src="PUPLogo.png" alt="PUP Logo">
                <div class="saliksik-text">SALIKSIK</div>
            </div>

            <h2>FACULTY LOGIN</h2>

            <form method="POST" action="">
                <div class="input-group">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="pupid" placeholder="PUP ID Number" value="<?= $pupid ?>" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
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
                    <a href="faculty_forgot_password.php">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-signin">Sign in</button>
            </form>

            <div class="createaccount">
                <p>Don't have an account? <a href="facultyreg.php">Register now</a></p>
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

        // Toggle icon visual
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>

</html>