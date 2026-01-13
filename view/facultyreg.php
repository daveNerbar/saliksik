<?php
// --- 1. Load PHPMailer Classes ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

$servername = "localhost";
$username = "root";
$password = "";
$database = "saliksik";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$firstname = "";
$middlename = "";
$lastname = "";
$suffix = "";
$phonenumber = "";
$email = ""; 
$pupid = "";
$department = "";
$password = "";

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $firstname = $_POST["firstname"];
    $middlename = $_POST["middlename"];
    $lastname = $_POST["lastname"];
    $suffix = $_POST["suffix"];
    $phonenumber = $_POST["phonenumber"];
    $email = $_POST["email"];
    $pupid = $_POST["pupid"];
    $department = $_POST["department"];
    $password = $_POST["password"];

    do {
        // Validation
        if (
            empty($firstname) || empty($lastname) || empty($phonenumber) ||
            empty($pupid) || empty($department) || empty($password) || empty($email)
        ) {
            $errorMessage = "All fields are required.";
            break;
        }

        // --- 🔍 CHECK 1: PUP ID EXISTS ---
        $stmt = $conn->prepare("SELECT id FROM facultyacc WHERE pupid = ? LIMIT 1");
        $stmt->bind_param("s", $pupid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = "PUP ID Number is already registered.";
            break;
        }
        $stmt->close();

        // --- 🔍 CHECK 2: EMAIL ALREADY EXISTS ---
        $stmt = $conn->prepare("SELECT id FROM facultyacc WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = "Email address is already registered.";
            break;
        }
        $stmt->close();

        // 2. Insert Data
        // Ideally use password_hash($password, PASSWORD_DEFAULT) here for security
        $sql = "INSERT INTO facultyacc (firstname, middlename, lastname, suffix, phonenumber, email, pupid, department, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $firstname, $middlename, $lastname, $suffix, $phonenumber, $email, $pupid, $department, $password);

        if ($stmt->execute()) {
            
            // --- 3. SEND WELCOME EMAIL ---
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'davidnerbar@gmail.com'; // Your Gmail
                $mail->Password   = 'pmzt htab pknm eogc';   // Your App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('no-reply@saliksik.com', 'SALIKSIK Admin');
                $mail->addAddress($email, "$firstname $lastname"); 

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Welcome to SALIKSIK!';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; color: #333;'>
                        <h2 style='color: #800000;'>Welcome to SALIKSIK, Prof. $lastname!</h2>
                        <p>You have successfully created your faculty account.</p>
                        <p><b>PUP ID:</b> $pupid</p>
                        <p><b>Department:</b> $department</p>
                        <hr>
                        <p>Regards,<br>SALIKSIK Team</p>
                    </div>
                ";

                $mail->send();
            } catch (Exception $e) {
                // Silent fail
            }

            $successMessage = "Faculty registered successfully! Email sent. Redirecting...";
            
            // Redirect after 2 seconds
            header("refresh:1; url=facultylogin.php"); 
            
        } else {
            $errorMessage = "Invalid query: " . $conn->error;
        }
        $stmt->close();

    } while (false);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SALIKSIK | Faculty Register</title>
<link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
<script src="https://kit.fontawesome.com/19d37dc8d9.js" crossorigin="anonymous"></script>

<style>
    :root {
        --maroon: #550000;
        --yellow: #FFD200;
        --white: #ffffff;
    }

    * { box-sizing: border-box; font-family: Arial, sans-serif; }

    body {
        margin: 0;
        height: 100vh;
        background: url('pupbg.jpg') no-repeat center center/cover;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .form-container {
        width: 350px;
        background-color: var(--white);
        padding: 30px 40px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        text-align: center;
    }

    .form-container img { width: 70px; }

    .saliksik-title {
        font-family: 'Knewave', cursive;
        color: var(--maroon);
        font-size: 36px;
        -webkit-text-stroke: 0.5px var(--yellow);
        margin: 5px 0 15px;
    }

    h2 {
        color: var(--maroon);
        font-weight: 900;
        margin-bottom: 5px;
    }

    input[type="text"],
    input[type="password"],
    input[type="tel"],
    input[type="email"] { 
        width: 100%;
        padding: 8px 10px;
        border-radius: 4px;
        border: 1px solid #ccc;
        margin-bottom: 10px;
        font-size: 14px;
        text-transform: capitalize;
    }

    /* Email specific style */
    input[type="email"] {
        text-transform: none;
    }

    .password-wrapper {
        position: relative;
        width: 100%;
        margin-bottom: 10px;
    }

    .password-wrapper input {
        margin-bottom: 0; 
        padding-right: 35px; 
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #555;
        font-size: 14px;
    }

    .form-row {
        display: flex;
        gap: 10px;
    }

    .form-row input { flex: 1; }

    button {
        background-color: var(--maroon);
        color: white;
        padding: 10px;
        border-radius: 5px;
        border: none;
        width: 100%;
        font-weight: bold;
        cursor: pointer;
        margin-top: 10px;
    }

    button:hover { background-color: #6e0e0e; }

    .alert {
        padding: 8px;
        border-radius: 4px;
        margin-bottom: 10px;
        color: white;
        font-size: 14px;
    }

    .alert-error { background-color: #dc3545; }
    .alert-success { background-color: #198754; }

    .signin {
        margin-top: 10px;
        font-size: 13px;
    }
    .signin a {
        color: var(--maroon);
        font-weight: bold;
        text-decoration: none;
    }

    /* Strength Meter Colors */
    .strength { margin-top: 5px; font-weight: bold; }
    .weak { color: #dc3545; }
    .medium { color: #ffc107; }
    .strong { color: #198754; }
</style>
</head>

<body>
<div class="form-container">
    <img src="puplogo.png">
    <div class="saliksik-title">SALIKSIK</div>

    <h2>FACULTY REGISTER</h2>
    <p>Enter your faculty information.</p>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error"><?= $errorMessage ?></div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?= $successMessage ?></div>
    <?php endif; ?>

    <form action="" method="post">

        <input type="text" name="firstname" placeholder="First Name" value="<?= htmlspecialchars($firstname) ?>" required>
        <input type="text" name="middlename" placeholder="Middle Name" value="<?= htmlspecialchars($middlename) ?>">
        <input type="text" name="lastname" placeholder="Last Name" value="<?= htmlspecialchars($lastname) ?>" required>

        <div class="form-row">
            <input type="text" name="suffix" placeholder="Suffix (Optional)" value="<?= htmlspecialchars($suffix) ?>">
            <input type="tel" name="phonenumber" placeholder="Phone Number" maxlength="11"
                   pattern="^09[0-9]{9}$" value="<?= htmlspecialchars($phonenumber) ?>" required>
        </div>

        <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($email) ?>" required>

        <input type="text" name="pupid" placeholder="PUP ID Number" value="<?= htmlspecialchars($pupid) ?>" required>
        <input type="text" name="department" placeholder="Department" value="<?= htmlspecialchars($department) ?>" required>

        <div class="password-wrapper">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <i class="fa-solid fa-eye toggle-password" id="toggleBtn"></i>
        </div>

        <div id="strength" style="font-size:13px; text-align:left;"></div>
        <div id="feedback" style="font-size:12px; text-align:left; color:#555;"></div>

        <button type="submit">SIGN UP</button>

        <div class="signin">← <a href="facultylogin.php">Sign in</a></div>
    </form>
</div>

<script>
const toggleBtn = document.getElementById('toggleBtn');
const passwordInput = document.getElementById('password');

toggleBtn.addEventListener('click', function() {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});

function validatePasswordStrength(password) {
    const minLength = 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNum = /[0-9]/.test(password);
    const hasSpecial = /[!@#$%^&*()_+\[\]{}:;<>,.?~]/.test(password);

    let score = 0;
    let missing = [];

    if (password.length >= minLength) score++; else missing.push("8+ characters");
    if (hasUpper) score++; else missing.push("uppercase");
    if (hasLower) score++; else missing.push("lowercase");
    if (hasNum) score++; else missing.push("numbers");
    if (hasSpecial) score++; else missing.push("symbols");

    let strength = "Weak";
    if (score >= 4) strength = "Strong";
    else if (score === 3) strength = "Medium";

    return {
        strength,
        summary: missing.length ? "Missing: " + missing.join(", ") : "Perfect password!"
    };
}

const strengthDisplay = document.getElementById("strength");
const feedbackDisplay = document.getElementById("feedback");

passwordInput.addEventListener("input", () => {
    const result = validatePasswordStrength(passwordInput.value);
    strengthDisplay.textContent = "Strength: " + result.strength;
    strengthDisplay.className = "strength " + result.strength.toLowerCase(); // Updated to add class
    feedbackDisplay.textContent = result.summary;
});
</script>

</body>
</html>