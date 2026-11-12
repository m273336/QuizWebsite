<?php
// MID Quiz: create a quiz using JSON text-file storage.
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}


// Local development testing only.
// Enable using MID_QUIZ_TEST_MODE=1 on a localhost PHP server.
$testMode = getenv("MID_QUIZ_TEST_MODE") === "1"
    && in_array(
        $_SERVER["REMOTE_ADDR"] ?? "",
        ["127.0.0.1", "::1"],
        true
    );

if ($testMode) {
    $_SESSION["user_id"] = "test-teacher";
    $_SESSION["role"] = "teacher";
}

// Require an authenticated teacher.
if (empty($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "teacher") {
    http_response_code(403);
    exit("Only logged-in teachers can create quizzes.");
}

function invalidQuiz($message) {
    http_response_code(400);
    exit(htmlspecialchars($message, ENT_QUOTES, "UTF-8"));
}

$title = $_POST["quiz_title"] ?? "";
$description = $_POST["quiz_description"] ?? "";
$questions = $_POST["questions"] ?? null;

if (!is_string($title) || trim($title) === "" || strlen($title) > 200) {
    invalidQuiz("Please enter a quiz title (up to 200 characters).");
}
if (!is_string($description) || strlen($description) > 2000) {
    invalidQuiz("Invalid quiz description.");
}
if (!is_array($questions) || count($questions) < 1 || count($questions) > 50) {
    invalidQuiz("A quiz needs between 1 and 50 questions.");
}

$cleanQuestions = [];
foreach ($questions as $question) {
    if (!is_array($question) || !is_string($question["text"] ?? null)
        || !is_array($question["choices"] ?? null)
        || !is_string($question["correct"] ?? null)) {
        invalidQuiz("Invalid question.");
    }

    $text = trim($question["text"]);
    if ($text === "" || strlen($text) > 1000) {
        invalidQuiz("Each question needs text (up to 1000 characters).");
    }
    $choices = [];
    foreach (["A", "B", "C", "D"] as $letter) {
        $choice = $question["choices"][$letter] ?? null;
        if (!is_string($choice) || trim($choice) === "" || strlen($choice) > 500) {
            invalidQuiz("Each question needs all four choices.");
        }
        $choices[$letter] = trim($choice);
    }

    if (!in_array($question["correct"], ["A", "B", "C", "D"], true)) {
        invalidQuiz("Choose a correct answer for each question.");
    }
    $cleanQuestions[] = [
        "text" => $text,
        "choices" => $choices,
        "correct" => $question["correct"]
    ];
}

$quiz = [
    "id" => bin2hex(random_bytes(16)),
    "title" => trim($title),
    "description" => trim($description),
    "created_by" => $_SESSION["user_id"],
    "created_at" => date("c"),
    "questions" => $cleanQuestions
];

// Private storage is a sibling of the Git checkout and must not be web-served.
$path = dirname(__DIR__) . "/quizwebsite-private/quizzes.txt";
$handle = fopen($path, "c+");
if ($handle === false) {
    http_response_code(500);
    exit("Quiz storage is not writable.");
}

if (!flock($handle, LOCK_EX)) {
    fclose($handle);
    http_response_code(500);
    exit("Could not lock quiz storage.");
}

try {
    rewind($handle);
    $raw = stream_get_contents($handle);
    if ($raw === false) {
        throw new RuntimeException("Read failure");
    }
    $saved = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($saved) || !array_is_list($saved)) {
        throw new RuntimeException("Invalid storage format");
    }
    $saved[] = $quiz;
    $json = json_encode($saved, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
    rewind($handle);
    $length = strlen($json);
    $offset = 0;
    while ($offset < $length) {
        $written = fwrite($handle, substr($json, $offset));
        if ($written === false || $written === 0) {
            throw new RuntimeException("Write failure");
        }
        $offset += $written;
    }
    if (!ftruncate($handle, $length) || !fflush($handle)) {
        throw new RuntimeException("Flush failure");
    }
} catch (Throwable $error) {
    error_log("Quiz save failed: " . $error->getMessage());
    http_response_code(500);
    exit("Could not save quiz.");
} finally {
    flock($handle, LOCK_UN);
    fclose($handle);
}

header("Location: teacher_home.html?created=1", true, 303);
exit;
