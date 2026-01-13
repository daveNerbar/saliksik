<?php
// chatbot.php
session_start(); // Start session to get logged-in user info
include 'db_conn.php'; // Connect to database

header('Content-Type: application/json');

// 1. CONFIGURATION
$apiKey = 'AIzaSyBMyF0jPmSrfwFmRjzForzCDryoTCfQjM4'; 
$model = 'gemini-2.5-flash'; // Good balance of speed and smarts

// 2. GET USER INPUT
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (!$userMessage) {
    echo json_encode(['reply' => 'Please type a message.']);
    exit;
}

// =================================================================
// 3. FETCH LIVE DATA FROM DATABASE (The "Brain" Upgrade)
// =================================================================

// A. GET BOOKS (Limit to 50 to save token space)
$bookData = "";
$sqlBooks = "SELECT book_title, authors, genre, accessor_no, call_number, isbn, total_copies FROM books LIMIT 50";
$resultBooks = $conn->query($sqlBooks);

if ($resultBooks->num_rows > 0) {
    while($row = $resultBooks->fetch_assoc()) {
        $bookData .= "- Title: {$row['book_title']} | Author: {$row['authors']} | Genre: {$row['genre']} | Accession No: {$row['accessor_no']} | Call No: {$row['call_number']} | ISBN: {$row['isbn']} | Copies: {$row['total_copies']}\n";
    }
} else {
    $bookData = "No books currently available.";
}

// B. GET EVENTS
$eventData = "";
$sqlEvents = "SELECT title, event_date, event_time, location, description FROM events WHERE event_date >= CURDATE()";
$resultEvents = $conn->query($sqlEvents);

if ($resultEvents->num_rows > 0) {
    while($row = $resultEvents->fetch_assoc()) {
        $eventData .= "- Event: {$row['title']} | Date: {$row['event_date']} at {$row['event_time']} | Loc: {$row['location']} | Info: {$row['description']}\n";
    }
} else {
    $eventData = "No upcoming events.";
}

// C. GET CURRENT USER (Assuming you store student number in session)
// You must set $_SESSION['student_number'] when the user logs in for this to work.
$userData = "Guest User (Not logged in)";
if (isset($_SESSION['studentnumber'])) { 
    // Adjust 'studentnumber' to match whatever variable you use in your login script
    $sNum = $_SESSION['studentnumber'];
    $sqlUser = "SELECT firstname, lastname, course, section FROM studacc WHERE studentnumber = '$sNum'";
    $resultUser = $conn->query($sqlUser);
    if ($resultUser->num_rows > 0) {
        $u = $resultUser->fetch_assoc();
        $userData = "Name: {$u['firstname']} {$u['lastname']} | Course: {$u['course']} | Section: {$u['section']}";
    }
}

// =================================================================
// 4. BUILD THE SYSTEM PROMPT
// =================================================================

$systemPrompt = "You are SalikTech, the intelligent librarian for the PUP Parañaque Campus Library.
Your goal is to help students find books, check events, and know their account details.

--- SYSTEM INFORMATION ---
1. ABOUT US: SalikTech is a digital research platform designed to enhance academic resource access for PUPPQ.
2. CURRENT USER: $userData
   (If the user asks 'Who am I?' or 'My account', use this info. If it says Guest, ask them to log in).

--- DATABASE CONTENTS (Use this to answer questions) ---

AVAILABLE BOOKS:
$bookData

UPCOMING EVENTS:
$eventData

--- INSTRUCTIONS ---
- If asked about a specific book, provide the Accession No, Call Number, and Location (Shelves).
- If asked about 'Genre', look at the book list provided above.
- If asked about 'Events', list them from the data above.
- Keep answers short, friendly, and helpful.
- If the data is not in the lists above, say 'I couldn't find that in our records'.
- Library Hours: 8:00 AM to 5:00 PM, Monday to Friday.
";

// 5. PREPARE GEMINI PAYLOAD
$finalPrompt = "SYSTEM CONTEXT:\n" . $systemPrompt . "\n\nUSER QUESTION:\n" . $userMessage;

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $finalPrompt]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.4, // Lower temperature for more factual answers
        "maxOutputTokens" => 300
    ]
];

// 6. SEND TO GOOGLE
$url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$result = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

// 7. HANDLE RESPONSE
if ($curlError) {
    echo json_encode(['reply' => "Connection Error: " . $curlError]);
    exit;
}

$response = json_decode($result, true);

if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
    $reply = $response['candidates'][0]['content']['parts'][0]['text'];
    // Formatting: Convert Markdown bold (**text**) to HTML bold (<b>text</b>) for better display
    $reply = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $reply);
    echo json_encode(['reply' => $reply]);
} else {
    $errorMsg = isset($response['error']['message']) ? $response['error']['message'] : "Unknown Error";
    echo json_encode(['reply' => "AI Error: " . $errorMsg]);
}
?>