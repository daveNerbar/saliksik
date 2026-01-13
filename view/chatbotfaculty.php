<?php
// chatbot.php (Unified for Student & Faculty)
session_start();
include 'db_conn.php'; 

header('Content-Type: application/json');

// --- CONFIGURATION ---
$apiKey = 'AIzaSyD-Jk5fhZixV5OHyzIUo0mY4ewCAbphGbc'; 
$model = 'gemini-2.5-flash';

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (!$userMessage) {
    echo json_encode(['reply' => 'Please type a message.']);
    exit;
}

// =================================================================
// 1. IDENTIFY THE USER (Student vs Faculty)
// =================================================================
$userData = "Guest (Not logged in)";
$userRole = "Guest";

// Check if it's a STUDENT
if (isset($_SESSION['studentnumber'])) {
    $id = $_SESSION['studentnumber'];
    $sql = "SELECT firstname, lastname, course, section FROM studacc WHERE studentnumber = '$id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $u = $result->fetch_assoc();
        $userData = "Student Name: {$u['firstname']} {$u['lastname']} | Course: {$u['course']} | Section: {$u['section']}";
        $userRole = "Student";
    }
} 
// Check if it's FACULTY (Assuming you use 'pupid' in session for faculty)
elseif (isset($_SESSION['pupid'])) {
    $id = $_SESSION['pupid'];
    $sql = "SELECT firstname, lastname, department FROM facultyacc WHERE pupid = '$id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $u = $result->fetch_assoc();
        $userData = "Faculty Name: {$u['firstname']} {$u['lastname']} | Department: {$u['department']}";
        $userRole = "Faculty";
    }
}

// =================================================================
// 2. FETCH SYSTEM DATA
// =================================================================

// Get Books
$bookData = "";
$sqlBooks = "SELECT book_title, authors, genre, accessor_no, call_number, total_copies FROM books LIMIT 40";
$resultBooks = $conn->query($sqlBooks);
if ($resultBooks->num_rows > 0) {
    while($row = $resultBooks->fetch_assoc()) {
        $bookData .= "- {$row['book_title']} (Auth: {$row['authors']}) [Acc: {$row['accessor_no']} | Loc: {$row['call_number']}]\n";
    }
}

// Get Events
$eventData = "";
$sqlEvents = "SELECT title, event_date, location FROM events WHERE event_date >= CURDATE()";
$resultEvents = $conn->query($sqlEvents);
if ($resultEvents->num_rows > 0) {
    while($row = $resultEvents->fetch_assoc()) {
        $eventData .= "- {$row['title']} on {$row['event_date']} at {$row['location']}\n";
    }
} else {
    $eventData = "No upcoming events.";
}

// =================================================================
// 3. BUILD THE PROMPT
// =================================================================

$systemPrompt = "You are SalikTech, a helpful AI assistant for the PUP Parañaque Library.
You are currently talking to a $userRole.

--- USER DETAILS ---
$userData

--- LIBRARY DATA ---
BOOKS AVAILABLE:
$bookData

UPCOMING EVENTS:
$eventData

--- INSTRUCTIONS ---
1. Answer based on the data provided above.
2. If the user is Faculty, be more formal and respectful (e.g., address them as Sir/Ma'am or Professor).
3. If they ask about books, give the Accession No and Call Number.
4. If they ask about their profile, repeat the User Details I gave you.
- Library Hours: 8:00 AM to 5:00 PM, Monday to Friday.
";

// =================================================================
// 4. SEND TO GEMINI
// =================================================================
$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => "SYSTEM CONTEXT:\n" . $systemPrompt . "\n\nUSER QUESTION:\n" . $userMessage]
            ]
        ]
    ]
];

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

if ($curlError) {
    echo json_encode(['reply' => "Connection Error."]);
    exit;
}

$response = json_decode($result, true);
if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
    $reply = $response['candidates'][0]['content']['parts'][0]['text'];
    $reply = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $reply); // Bold formatting
    echo json_encode(['reply' => $reply]);
} else {
    echo json_encode(['reply' => "I'm having trouble thinking right now."]);
}
?>