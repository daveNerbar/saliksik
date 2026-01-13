<?php
session_start();

// --- 1. DATABASE CONNECTION ---
include("connection.php"); 
// --- 2. LOGIN LOGIC ---
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepare SQL Statement
    $stmt = $conn->prepare("SELECT id, firstname, lastname, role, password FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // --- DIRECT COMPARISON (No Hashing) ---
        if ($password === $row['password']) {
            
            // --- LOGIN SUCCESS ---
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['firstname'] . ' ' . $row['lastname'];
            $_SESSION['admin_role'] = $row['role'];

            // Redirect to Dashboard
            header("Location: dashboard.php");
            exit();

        } else {
            $error_message = "Invalid Password. Please try again.";
        }
    } else {
        $error_message = "Username not found.";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SALIKSIK Login</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <script src="https://kit.fontawesome.com/985df08261.js" crossorigin="anonymous"></script>

  <style>
    /* --- GENERAL RESET --- */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      height: 100vh;
      width: 100%;
      background-image: url('schoolpupq.jpg'); 
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      display: flex; 
      justify-content: center;
      align-items: center;
      overflow: hidden; 
    }

    /* --- MAIN CARD CONTAINER --- */
    .main-card {
      display: flex; 
      width: 100%;
      max-width: 850px; 
      height: 550px; 
      background: transparent; 
      border-radius: 20px; 
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
      overflow: hidden; 
    }

    /* --- LEFT SIDE: WELCOME CONTAINER --- */
    .welcome-section {
      flex: 1; 
      background: linear-gradient(135deg, #7b0000 0%, #a31111 100%);
      color: white; 
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 40px;
      position: relative;
    }

    .welcome-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: url('puplogo.png'); 
      background-size: 300px;
      background-position: center;
      background-repeat: no-repeat;
      opacity: 0.1; 
    }

    /* --- RIGHT SIDE: LOGIN FORM --- */
    .login-section {
      flex: 1; 
      background: rgba(255, 255, 255, 0.65); 
      backdrop-filter: blur(50px); 
      -webkit-backdrop-filter: blur(50px); 
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      position: relative;
    }

    h2 {
      font-size: 28px;
      margin-bottom: 5px;
      color: #222;
      font-weight: 700;
    }

    .subtitle {
      font-size: 13px;
      color: #666; 
      margin-bottom: 25px;
    }

    /* --- WELCOME TITLE --- */
    .welcome-title {
      font-family: 'Knewave', cursive;
      font-size: 40px; 
      color: #711E1E; 
      text-shadow: -2px -2px 0 #FFE732, 2px -2px 0 #FFE732, -2px 2px 0 #FFE732, 2px 2px 0 #FFE732;
      margin-bottom: 5px; 
      letter-spacing: 1px;
      z-index: 1;
    }

    .brand-subtitle {
      font-size: 13px;
      font-weight: 700;
      color: white;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 30px; 
      z-index: 1;
      opacity: 0.9;
    }

    .welcome-text {
      font-size: 14px;
      line-height: 1.6;
      max-width: 80%;
      z-index: 1;
      opacity: 0.9;
      color: white; 
    }

    /* --- FORM ELEMENTS --- */
    label {
      display: block;
      text-align: left;
      margin-bottom: 5px;
      font-weight: 700; 
      color: #333;
      font-size: 13px; 
      width: 100%;
    }

    form {
      width: 100%;
      max-width: 300px; 
    }

    .input-group {
      position: relative;
      margin-bottom: 15px; 
    }

    .input-group i {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      color: #555;
      font-size: 14px; 
    }

    /* Icons on the LEFT side */
    .input-group .fa-user,
    .input-group .fa-lock {
      left: 12px;
      z-index: 1;
    }

    /* Hide Default Browser Eye (Edge/IE) */
    .input-group input::-ms-reveal,
    .input-group input::-ms-clear {
      display: none;
    }

    .input-group input {
      width: 100%;
      border: 1px solid #ccc; 
      border-radius: 8px; 
      font-size: 14px; 
      transition: 0.3s;
      background-color: #f9f9f9; 
      padding: 10px 35px 10px 35px; 
    }

    .input-group input:focus {
      border-color: #711E1E; 
      background-color: #fff; 
      outline: none;
      box-shadow: 0 0 0 3px rgba(113, 30, 30, 0.15);
    }

    /* --- CUSTOM EYE ICON (Right Side) --- */
    #togglePassword {
      position: absolute;
      right: 12px; 
      top: 50%;
      transform: translateY(-50%);
      color: #555;
      cursor: pointer;
      display: none;
      font-size: 14px;
      z-index: 2;
      left: auto;
    }

    #togglePassword:hover {
      color: #711E1E;
    }

    .forgot {
      display: block;
      text-align: right;
      font-size: 12px; 
      color: #711E1E;
      text-decoration: none;
      margin-bottom: 25px;
      font-weight: 600;
    }

    .forgot:hover {
      text-decoration: underline;
    }

    .btn {
      width: 100%;
      padding: 12px; 
      background-color: #711E1E; 
      color: #fff;
      border: none; 
      border-radius: 8px; 
      font-size: 15px; 
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s;
      box-shadow: 0 4px 10px rgba(113, 30, 30, 0.2);
    }

    .btn:hover {
      background-color: #5a1818;
      transform: translateY(-2px);
    }

    /* Error Message Style */
    .error-msg {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
        font-size: 13px;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    @media (max-width: 768px) {
      .main-card {
        flex-direction: column-reverse; 
        height: auto;
        max-width: 350px;
      }
      .welcome-section {
        padding: 30px 20px;
      }
      .login-section {
        padding: 40px 20px;
      }
    }
  </style>
</head>
<body>

  <div class="main-card">
    
    <div class="welcome-section">
      <h1 class="welcome-title">Welcome Back!</h1>
      
      <p class="brand-subtitle">SALIKSIK PUP - PARAÑAQUE LIBRARY</p>

      <p class="welcome-text">
        To keep connected with us, please login with your admin information.
        <br><br>
        Access the library system, manage resources, and assist students efficiently.
      </p>
    </div>

    <div class="login-section">
      
      <h2>Login</h2>
      <p class="subtitle">Sign in to access Admin</p>

      <form method="POST" action="" autocomplete="off">
        
        <?php if(!empty($error_message)): ?>
            <div class="error-msg">
                <i class="fa-solid fa-triangle-exclamation"></i> 
                <span><?= $error_message ?></span>
            </div>
        <?php endif; ?>

        <label for="username">Username</label>
        <div class="input-group">
          <i class="fa-solid fa-user"></i>
          <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="off" />
        </div>

        <label for="password">Password</label>
        <div class="input-group">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="new-password" />
          <i class="fa-solid fa-eye-slash" id="togglePassword"></i> 
        </div>

        <a href="admin_forgot_password.php" class="forgot">Forgot Password?</a>
        <button type="submit" class="btn">SIGN IN</button>
      </form>
    </div>

  </div>

  <script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    // Initially hide the eye
    togglePassword.style.display = 'none';

    // Show eye icon only when user starts typing
    password.addEventListener('input', function () {
      if (password.value.length > 0) {
        togglePassword.style.display = 'block';
      } else {
        togglePassword.style.display = 'none';
      }
    });

    // Toggle logic (Clicking the eye)
    togglePassword.addEventListener('click', function () {
      // Toggle the type attribute
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      
      // Toggle the eye / eye-slash icon
      this.classList.toggle('fa-eye-slash');
      this.classList.toggle('fa-eye');
    });
  </script>
</body>
</html>