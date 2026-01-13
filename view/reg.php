<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "saliksik";

$connection = new mysqli($servername, $username, $password, $database);

$firstname = "";
$middlename = "";
$lastname = "";
$suffix = "";
$course = "";
$section = "";
$phonenumber = "";
$studentnumber = "";
$password = "";
$pdf_file = "";

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $firstname = $_POST["firstname"];
    $middlename = $_POST["middlename"];
    $lastname = $_POST["lastname"];
    $suffix = $_POST["suffix"];
    $course = $_POST["course"];
    $section = $_POST["section"];
    $phonenumber = $_POST["phonenumber"];
    $studentnumber = $_POST["studentnumber"];
    $password = $_POST["password"];

    // ✅ Handle PDF upload
    if (isset($_FILES["pdf_file"]) && $_FILES["pdf_file"]["error"] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = basename($_FILES["pdf_file"]["name"]);
        $targetFile = $targetDir . time() . "_" . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if ($fileType != "pdf") {
            $errorMessage = "Only PDF files are allowed.";
        } else {
            move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $targetFile);
            $pdf_file = $targetFile;
        }
    } else {
        $errorMessage = "Please upload your PDF file.";
    }

    if (empty($errorMessage)) {
        do {
            if (
                empty($firstname) ||  empty($lastname) || empty($course) ||
                empty($section) || empty($phonenumber) || empty($studentnumber) || empty($password)
            ) {
                $errorMessage = "All fields are required.";
                break;
            }

            $checkSql = "SELECT * FROM studacc WHERE studentnumber = '$studentnumber' LIMIT 1";
            $checkResult = $connection->query($checkSql);

            if ($checkResult && $checkResult->num_rows > 0) {
                $errorMessage = "Student number is already registered.";
                break;
            }

            // ✅ Save student + PDF file path
            $sql = "INSERT INTO studacc (firstname, middlename, lastname, suffix, course, section, phonenumber, studentnumber, password, pdf_file)
                    VALUES ('$firstname', '$middlename', '$lastname', '$suffix', '$course', '$section', '$phonenumber', '$studentnumber', '$password', '$pdf_file')";
            $result = $connection->query($sql);

            if (!$result) {
                $errorMessage = "Invalid query: " . $connection->error;
                break;
            }

            
            $successMessage = "Student registered successfully!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } while (false);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SALIKSIK | Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet">

    <style>
        :root {
            --maroon: #550000;
            --yellow: #FFD200;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            margin: 0;
            height: 100vh;
            background: url('pupbg.jpg') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            width: 350px;
            background-color: var(--white);
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .form-container img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        .saliksik-title {
            font-family: 'knewave', cursive;
            color: var(--maroon);
            font-size: 36px;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            -webkit-text-stroke: 0.5px var(--yellow);
            margin: 5px 0 15px;
        }

        h2 {
            color: var(--maroon);
            font-weight: 900;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        input[type="text"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 13px;
            text-transform: capitalize;
        }

        .form-row {
            display: flex;
            gap: 10px;
        }

        .form-row input {
            flex: 1;
        }

        button {
            background-color: var(--maroon);
            color: var(--white);
            border: none;
            padding: 10px;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 10px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #5a0c0c;
        }

        .alert {
            padding: 8px;
            border-radius: 4px;
            color: #fff;
            margin-bottom: 10px;
        }

        .alert-error {
            background-color: #dc3545;
        }

        .alert-success {
            background-color: #198754;
        }

        .signin {
            margin-top: 10px;
            font-size: 13px;
        }

        .signin a {
            color: var(--maroon);
            text-decoration: none;
            font-weight: bold;
        }

        .signin a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <img src="puplogo.png" alt="PUP Logo">
        <div class="saliksik-title">SALIKSIK</div>
        <h2>CREATE ACCOUNT</h2>
        <p>Enter your Personal Data.</p>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error"><?= $errorMessage ?></div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>

        <form id="signupForm" action="#" method="post" enctype="multipart/form-data">
            <input type="text" id="firstName" placeholder="First Name" name="firstname" value="<?= $firstname ?>" required>
            <input type="text" id="middleName" placeholder="Middle Name" name="middlename" value="<?= $middlename ?>">
            <input type="text" id="lastName" placeholder="Last Name" name="lastname" value="<?= $lastname ?>" required>

            <div class="form-row">
                <input type="text" placeholder="Suffix" name="suffix" value="<?= $suffix ?>">
                <input type="tel" placeholder="Phone Number" name="phonenumber" value="<?= $phonenumber ?>"
                    pattern="^09[0-9]{9}$" maxlength="11" oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                    title="Enter an 11-digit number starting with 09" required>
            </div>

            <div class="form-row">
                <input type="text" placeholder="Course" name="course" value="<?= $course ?>" required>
                <input type="text" placeholder="Section" name="section" value="<?= $section ?>" required>
            </div>

            <input type="text" id="studentNumber" placeholder="Student Number" name="studentnumber" value="<?= $studentnumber ?>" required>

            <div style="position: relative;">
                <input type="password" id="password" placeholder="Password" name="password" value="<?= $password ?>" required>
                <i class="fa fa-eye" id="togglePassword" style="position:absolute; right:10px; top:10px; cursor:pointer; color:#550000;"></i>
            </div>

            <div id="strength" style="font-size:12px; text-align:left;"></div>
            <div id="feedback" style="font-size:12px; text-align:left; color:#555;"></div>

            <!-- PDF upload -->
            <div class="upload-row">
                <label style="font-weight: bold; display:block; text-align:left;">Upload COR (PDF Only):</label>
                <input type="file" id="pdfFile" name="pdf_file" accept="application/pdf" required>
            </div>

            <div id="message" class="message" style="font-size:13px; text-align:left; color:#550000; margin-top:5px;"></div>

            <button type="submit">SIGN UP</button>

            <div class="signin">
                ← <a href="studentlogin.php">Sign in</a>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js";

        const form = document.getElementById("signupForm");
        const message = document.getElementById("message");
        const studentInput = document.getElementById("studentNumber");
        const passwordInput = document.getElementById("password");
        const togglePassword = document.getElementById("togglePassword");

        // 🧿 Toggle password visibility
        togglePassword.addEventListener("click", () => {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            togglePassword.classList.toggle("fa-eye");
            togglePassword.classList.toggle("fa-eye-slash");
        });

        // Automatically uppercase student number
        studentInput.addEventListener("input", () => {
            studentInput.value = studentInput.value.toUpperCase();
        });

        // 🧩 Password Strength Validator
        function validatePasswordStrength(password) {
            const minLength = 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecialChar = /[!@#$%^&*()_+\[\]{}|;:,.<>?]/.test(password);

            let strength = 0;
            let missing = [];

            if (password.length >= minLength) strength++;
            else missing.push("8+ characters");

            if (hasUppercase) strength++;
            else missing.push("uppercase");
            if (hasLowercase) strength++;
            else missing.push("lowercase");
            if (hasNumber) strength++;
            else missing.push("numbers");
            if (hasSpecialChar) strength++;
            else missing.push("symbols");

            let strengthMessage = "";
            if (strength <= 2) strengthMessage = "Weak";
            else if (strength <= 4) strengthMessage = "Medium";
            else strengthMessage = "Strong";

            return {
                strength: strengthMessage,
                summary: missing.length ? `Create a password with ${missing.join(", ")}.` : "Perfect password!"
            };
        }

        const strengthDisplay = document.getElementById("strength");
        const feedbackDisplay = document.getElementById("feedback");

        passwordInput.addEventListener("input", () => {
            const result = validatePasswordStrength(passwordInput.value);
            strengthDisplay.textContent = `Strength: ${result.strength}`;
            strengthDisplay.className = `strength ${result.strength.toLowerCase()}`;
            feedbackDisplay.textContent = result.summary;
        });

        // 🧩 PDF Validation
        form.addEventListener("submit", async function(e) {
            e.preventDefault();
            message.textContent = "";
            message.className = "message";

            const firstName = document.getElementById("firstName").value.trim().toUpperCase();
            const middleName = document.getElementById("middleName").value.trim().toUpperCase();
            const lastName = document.getElementById("lastName").value.trim().toUpperCase();
            const enteredStudentNum = document.getElementById("studentNumber").value.trim().toUpperCase();
            const pdfFile = document.getElementById("pdfFile").files[0];
            const password = passwordInput.value;

            if (!pdfFile) {
                message.textContent = "Please upload your Certificate of Registration.";
                message.classList.add("error");
                return;
            }

            const pwCheck = validatePasswordStrength(password);
            if (pwCheck.strength === "Weak") {
                message.textContent = "❌ Weak password. " + pwCheck.summary;
                message.classList.add("error");
                return;
            }

            const enteredFullName = `${lastName}, ${firstName} ${middleName}`.replace(/\s+/g, " ").trim();

            const reader = new FileReader();
            reader.onload = async function() {
                const typedarray = new Uint8Array(this.result);
                const pdf = await pdfjsLib.getDocument(typedarray).promise;
                let fullText = "";

                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const textContent = await page.getTextContent();
                    fullText += textContent.items.map(item => item.str).join(" ") + " ";
                }

                fullText = fullText
                    .replace(/POLYTECHNIC UNIVERSITY OF THE PHILIPPINES/gi, "")
                    .replace(/CERTIFICATE OF REGISTRATION/gi, "")
                    .replace(/Republic of the Philippines/gi, "")
                    .replace(/\s+/g, " ")
                    .trim();

                const studentNumMatch = fullText.match(/\b\d{4}-\d{5}-[A-Z]{2}-\d\b/i);
                const pdfStudentNum = studentNumMatch ? studentNumMatch[0].toUpperCase() : null;

                const ayMatch = fullText.match(/A\.?Y\.?:?\s*(\d{4,})/i);
                const pdfAY = ayMatch ? parseInt(ayMatch[1]) : null;

                const nameMatch = fullText.match(/[A-Z ,.'-]+ [A-Z ,.'-]+ [A-Z ,.'-]+/);
                const pdfName = nameMatch ? nameMatch[0].trim() : null;

                if (!pdfStudentNum) {
                    message.textContent = "Could not detect a student number in the PDF.";
                    message.classList.add("error");
                    return;
                }

                if (pdfStudentNum !== enteredStudentNum) {
                    message.textContent = `❌ Student number mismatch! (PDF: ${pdfStudentNum})`;
                    message.classList.add("error");
                    return;
                }

                if (!pdfName) {
                    message.textContent = "Could not detect a student name in the PDF.";
                    message.classList.add("error");
                    return;
                }

                const normalize = s => s.replace(/\s+/g, " ").trim();
                if (normalize(pdfName) !== normalize(enteredFullName)) {
                    message.textContent = `❌ Name mismatch! (PDF: ${pdfName})`;
                    message.classList.add("error");
                    return;
                }

                if (!pdfAY) {
                    message.textContent = "Could not detect an Academic Year in the PDF.";
                    message.classList.add("error");
                    return;
                }

                const currentYear = new Date().getFullYear() % 100;
                const nextYear = currentYear + 1;
                const currentAY = parseInt(`${currentYear}${nextYear}`);

                if (pdfAY < currentAY) {
                    message.textContent = `❌ COR is outdated (PDF A.Y.: ${pdfAY}, Current A.Y.: ${currentAY}). Please upload a current COR.`;
                    message.classList.add("error");
                    return;
                }

                message.textContent = `✅ Signup successful! Verified for A.Y. ${pdfAY}.`;
                message.classList.add("success");

                // ✅ Proceed with normal PHP form submission
                form.submit();
            };

            reader.readAsArrayBuffer(pdfFile);
        });
    </script>

</body>



</html>