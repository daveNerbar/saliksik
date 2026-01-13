<?php
// --- DATABASE CONNECTION ---
include("conn.php"); 
// GET BOOK ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Secure integer conversion
    $sql = "SELECT * FROM books WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
    } else {
        echo "Book not found.";
        exit;
    }
} else {
    echo "No book selected.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book['book_title']) ?> | SALIKSIK</title>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Knewave&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/19d37dc8d9.js" crossorigin="anonymous"></script>

    <style>
        :root {
            --bg1: #550000;
            --bg2: #2c0000;
            --yellow: #ffd200;
            --white: #ffffff;
            --gray: #f5f5f5;
            --cream: #FAF8F1;
        }


        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--white);
        }

        /* Navbar (Identical to viewbooks) */
        
        /* DETAILS CONTAINER */
        .details-container {
            max-width: 900px;
            margin: 40px auto;
            background-color: var(--cream);
            padding: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            position: relative;
        }

        .back-btn {
            text-decoration: none;
            color: #333;
            font-size: 24px;
            display: inline-block;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .back-btn:hover {
            transform: translateX(-5px);
        }

        .book-header {
            border-bottom: 1px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .book-title {
            font-size: 22px;
            font-weight: 700;
            font-style: italic;
            color: #111;
            margin: 0;
            line-height: 1.4;
        }

        .section-label {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 10px;
            color: #000;
        }

        .abstract-text {
            font-size: 14px;
            line-height: 1.6;
            text-align: justify;
            color: #333;
            margin-bottom: 40px;
        }

        .info-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid #333;
            padding-top: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .meta-table {
            border-collapse: collapse;
            width: 100%;
            max-width: 500px;
            font-size: 13px;
        }

        .meta-table td {
            border: 1px solid #999;
            padding: 8px 12px;
        }

        .meta-table td:first-child {
            background-color: #f0ece1;
            font-weight: 600;
            width: 120px;
        }

        .pdf-btn {
            background-color: white;
            border: 1px solid #333;
            padding: 8px 25px;
            text-decoration: none;
            color: #000;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            box-shadow: 2px 2px 0px #333;
        }

        .pdf-btn:hover {
            background-color: #eee;
            transform: translateY(-1px);
        }

        .pdf-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        
    </style>
</head>

<body>

    <header class="navbar">
        <div class="nav-left">
            <a href="studenthome.php"><img src="puplogo.png" alt="PUP Logo" /></a>
            <div class="brand-title">
                <p class="univ">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</p>
                <div class="saliksik">SALIKSIK</div>
            </div>
        </div>
        <i class="fa-solid fa-bars" id="menu-icon"></i>

        <nav class="nav-right" id="nav-menu">
            <i class="fa-solid fa-xmark" id="close-icon"></i>

            <a href="studenthome.php">Home</a>

            <div class="nav-item-dropdown">
                <a href="studenthome.php#about">About us <i class="fa-solid fa-angle-down"></i></a>
                <div class="dropdown-content">
                    <a href="studentrules.php"><i class="fa-solid fa-scale-balanced"></i> Rules</a>
                    <a href="studentmessages.php"><i class="fa-solid fa-envelope"></i> Messages</a>
                </div>
            </div>

           
            <a href="studentviewbooks.php">Books</a>

            <div class="sign-out">
                <div class="profile-menu">
                    <i class="fa-solid fa-circle-user fa-lg" style="color: #ffffff;"></i>
                    <div class="dropdown">
                        <a href="studentprofile.php"><i class="fa-solid fa-user" style="color: #550000;"></i>Profile</a>
                        <a href="studentlogout.php"><i class="fa-solid fa-right-from-bracket" style="color: #550000;"></i>Sign Out</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="details-container">
            <a href="studentviewbooks.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>

            <div class="book-header">
                <h1 class="book-title"><?= htmlspecialchars($book['book_title']) ?></h1>
            </div>

            <div class="book-body">
                <div class="section-label">Abstract / Description</div>
                <p class="abstract-text">
                    <?= nl2br(htmlspecialchars($book['description'])) ?>
                </p>
            </div>

            <div class="info-footer">
                <table class="meta-table">
                    <tr>
                        <td>Author:</td>
                        <td><?= htmlspecialchars($book['authors']) ?></td>
                    </tr>
                    <tr>
                        <td>Published:</td>
                        <td><?= htmlspecialchars($book['publish_year']) ?></td>
                    </tr>
                    <tr>
                        <td>Publisher:</td>
                        <td><?= htmlspecialchars($book['publisher']) ?></td>
                    </tr>
                    <tr>
                        <td>Genre:</td>
                        <td><?= htmlspecialchars($book['genre']) ?></td>
                    </tr>
                    <tr>
                        <td>Language:</td>
                        <td><?= htmlspecialchars($book['language']) ?></td>
                    </tr>
                    <tr>
                        <td>Call No:</td>
                        <td><?= htmlspecialchars($book['call_number']) ?></td>
                    </tr>
                </table>

                <?php if (!empty($book['file_path'])): ?>
                    
                        <a href="../admin/<?= htmlspecialchars($book['file_path']) ?>" target="_blank" class="pdf-btn">
                        
                        <i class="fa-solid fa-file-pdf"></i> Read PDF
                    </a>
                <?php else: ?>
                    <a href="#" class="pdf-btn disabled">No PDF Available</a>
                <?php endif; ?>


            </div>
        </div>
    </main>

</body>

</html>