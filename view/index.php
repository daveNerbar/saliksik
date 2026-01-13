<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SALIKSIK</title>
  <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/19d37dc8d9.js" crossorigin="anonymous"></script>

  <style>
    :root {
      --maroon: #7b0000;
      --gold: #ffd24a;
      --bg-top: #7b0000;
      --bg-mid: #b5401f;
      --bg-bottom: #ffd24a;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html,
    body {
      height: 100%;
    }

    body {
      font-family: Arial, sans-serif;
      background: url('schoolpupq.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden; /* Important for the zoom effect */
    }

    /* ---------- LOADER ---------- */
    #loader {
      position: fixed;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, var(--bg-top), var(--bg-bottom));
      transition: opacity .8s ease, visibility .8s ease;
      z-index: 9999;
      overflow: hidden;
    }

    #loader.hide {
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
    }

    .spinner-wrap {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
    }

    .disc {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* ZOOM & SPIN EFFECT FOR LOADER LOGO */
    .disc img {
      width: 150px;
      height: 150px;
      object-fit: contain;
      /* Animation Logic:
         Total Duration: 1.8s
         - First ~1s: Spins
         - Remaining ~0.8s: Zooms out
      */
      animation: spinAndZoom 1.8s ease-in-out forwards;
      /* Initial state */
      transform: scale(1) rotate(0deg); 
    }

    @keyframes spinAndZoom {
      /* Phase 1: Spin (0% to 55%) */
      0% {
        transform: scale(1) rotate(0deg);
      }
      55% {
        transform: scale(1) rotate(360deg); /* Complete 360 spin */
      }
      
      /* Phase 2: Zoom (55% to 100%) */
      100% {
        transform: scale(50) rotate(360deg); /* Massive zoom, rotation stays same */
      }
    }

    .loading-caption {
      position: absolute;
      bottom: 20%;
      color: white;
      font-size: 20px;
      font-weight: 600;
      text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
      z-index: 1;
    }

    /* ---------- LANDING ---------- */
    #landing {
      position: fixed;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transform: translateY(20px);
      transition: opacity .8s ease, transform .8s ease;
      background: linear-gradient(180deg, var(--bg-top), var(--bg-bottom));
      z-index: 9998;
      padding: 20px;
    }

    #landing.show {
      opacity: 1;
      pointer-events: auto;
      transform: translateY(0);
    }

    .landing-inner {
      display: flex;
      align-items: center;
      gap: 24px;
      max-width: 800px;
    }

    .landing-logo img {
      width: 190px;
      height: 190px;
      border-radius: 50%;
      object-fit: contain;
    }

    /* --- UPDATED LANDING TEXT STYLE --- */
    .landing-text h1 {
      font-family: 'Knewave', cursive;
      font-size: 100px;
      color: #711E1E; /* Updated color */
      text-shadow: -2px -2px 0 #FFE732, 1px -1px 0 #FFE732, -1px 1px 0 #FFE732, 1px 1px 0 #FFE732; /* Updated shadow */
      line-height: 1.1;
    }
    /* ---------------------------------- */

    .landing-text p {
      margin-top: 10px;
      color: white;
      font-size: 18px;
      line-height: 1.4;
    }

    /* ---------- LOGIN ---------- */
    #login {
      display: none;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      width: 100%;
      background: url('background.jpg') no-repeat center center;
      background-size: cover;
      padding: 20px;
    }

    #login.show {
      display: flex;
    }

    .login-container {
      /* Made background semi-transparent white so blur is visible */
      background: rgba(255, 255, 255, 0.65); 
      
      /* Added backdrop blur for the frosted glass effect */
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px); /* For Safari support */
      
      padding: 40px 60px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
      width: 440px;
      max-width: 100%;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .login-container img {
      width: 70px;
      margin-bottom: 10px;
    }

    .saliksik-text {
      font-family: 'Knewave', cursive;
      font-size: 45px; /* Slightly larger to match the impact of the shadow */
      color: #711E1E;
      text-shadow: -2px -2px 0 #FFE732, 1px -1px 0 #FFE732, -1px 1px 0 #FFE732, 1px 1px 0 #FFE732;
      margin-bottom: 10px;
    }

    .title {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 15px; /* Increased gap slightly */
      margin-bottom: 25px;
    }

    .btn {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      background: #550000;
      color: white;
      padding: 15px;
      margin: 15px 0;
      border-radius: 6px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      border: 3px solid #550000;
      text-decoration: none;
    }

    .btn img {
      width: 22px;
      margin-right: 10px;
      margin-bottom: 0;
    }

    .btn:hover {
      background: #dd0000;
      border-color: #ffd200;
    }

    @media (max-width: 768px) {
      .landing-inner {
        flex-direction: column;
        text-align: center;
      }
      .landing-logo img {
        width: 100px;
        height: 100px;
      }
      .landing-text h1 {
        font-size: 48px;
      }
      .landing-text p {
        font-size: 16px;
      }
    }

    @media (max-width: 480px) {
      .login-container {
        width: 100%;
        padding: 30px 20px;
      }
      .title {
        flex-direction: column;
        gap: 5px;
      }
      .saliksik-text {
        font-size: 35px; /* Adjusted for mobile */
      }
      .btn {
        padding: 12px;
        font-size: 14px;
      }
    }
  </style>
</head>

<body>
  <div id="loader">
    <div class="spinner-wrap">
      <div class="disc"><img src="PUPLogo.png" alt="PUP logo"></div>
    </div>
    <div class="loading-caption">Loading...</div>
  </div>

  <section id="landing">
    <div class="landing-inner">
      <div class="landing-logo"><img src="PUPLogo.png" alt="PUP"></div>
      <div class="landing-text">
        <h1>SALIKSIK</h1>
        <p>The Digital Library of PUP-Parañaque.<br>Access your library, Anytime Anywhere</p>
      </div>
    </div>
  </section>

  <section id="login">
    <div class="login-container">
      <div class="title">
        <img src="PUPLogo.png" alt="PUP Logo">
        <div class="saliksik-text">SALIKSIK</div>
      </div>

      <a href="studentlogin.php" class="btn student">
        <img src="https://img.icons8.com/ios-filled/50/ffffff/student-male.png" alt="Student"> STUDENT
      </a>

      <a href="facultylogin.php" class="btn faculty">
        <img src="https://img.icons8.com/ios-filled/50/ffffff/teacher.png" alt="Faculty"> FACULTY
      </a>
    </div>
  </section>

  <script>
    // 1800ms total matches the 1.8s CSS animation
    const LOADING_TIME = 1800; 
    const LANDING_TIME = 3500;

    window.addEventListener("load", () => {
      setTimeout(() => {
        document.getElementById("loader").classList.add("hide");
        document.getElementById("landing").classList.add("show");

        setTimeout(() => {
          document.getElementById("landing").classList.remove("show");
          document.getElementById("landing").style.display = "none";
          document.getElementById("login").classList.add("show");
        }, LANDING_TIME);
      }, LOADING_TIME);
    });
  </script>
</body>

</html>