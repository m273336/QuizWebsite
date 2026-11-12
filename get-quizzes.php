<?php
// Read quiz listings or a specific quiz from private text-file storage.
session_start();
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

function jsonError(int $status, string $message): void {
    http_response_code($status);
    echo json_encode(["error" => $message]);
    exit;
}

if (($_SERVER["REQUEST_METHOD"] ?? "") !== "GET") {
    jsonError(405, "Only GET requests are supported.");
}

// Only authenticated users can browse quizzes.
// Temporary local testing is enabled only with an explicit server setting.
$testMode = getenv("MID_QUIZ_TEST_MODE") === "1"
    && in_array($_SERVER["REMOTE_ADDR"] ?? "", ["127.0.0.1", "::1"], true);
if (!$testMode && (empty($_SESSION["user_id"])
    || !in_array($_SESSION["role"] ?? "", ["student", "teacher"], true))) {
    jsonError(403, "Please log in to view quizzes.");
}

if (isset($_GET["id"]) && (!is_string($_GET["id"]) || $_GET["id"] === ""
    || strlen($_GET["id"]) > 100)) {
    jsonError(400, "Invalid quiz ID.");
}

$file = dirname(__DIR__) . "/quizwebsite-private/quizzes.txt";
$handle = @fopen($file, "r");
if ($handle === false) {
    jsonError(500, "Quiz storage is not available.");
}
if (!flock($handle, LOCK_SH)) {
    fclose($handle);
    jsonError(500, "Quiz storage is busy.");
}
try {
    $raw = stream_get_contents($handle);
} finally {
    flock($handle, LOCK_UN);
    fclose($handle);
}
if ($raw === false) {
    jsonError(500, "Could not read quizzes.");
}
try {
    $quizzes = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $error) {
    error_log("Invalid quiz storage JSON: " . $error->getMessage());
    jsonError(500, "Quiz storage is invalid.");
}
if (!is_array($quizzes) || !array_is_list($quizzes)) {
    jsonError(500, "Quiz storage has an unexpected format.");
}

// Build explicit safe responses. Never expose correct answers or creator IDs.
function quizSummary(array $quiz): array {
    return [
        "id" => $quiz["id"],
        "title" => $quiz["title"],
        "description" => $quiz["description"] ?? "",
        "question_count" => count($quiz["questions"])
    ];
}

$id = $_GET["id"] ?? null;
if ($id !== null) {
    foreach ($quizzes as $quiz) {
        if (!is_array($quiz) || ($quiz["id"] ?? null) !== $id) {
            continue;
        }
        if (!isset($quiz["questions"]) || !is_array($quiz["questions"])) {
            jsonError(500, "Quiz has invalid questions.");
        }

        $questions = [];
        foreach ($quiz["questions"] as $question) {
            if (!is_array($question) || !isset($question["text"], $question["choices"])
                || !is_array($question["choices"])) {
                jsonError(500, "Quiz question is invalid.");
            }
            $questions[] = [
                "text" => $question["text"],
                "choices" => array_intersect_key(
                    $question["choices"], array_flip(["A", "B", "C", "D"])
                )
            ];
        }

        echo json_encode(array_merge(quizSummary($quiz), ["questions" => $questions]));
        exit;
    }
    jsonError(404, "Quiz not found.");
}

$result = [];
foreach ($quizzes as $quiz) {
    if (!is_array($quiz) || !isset($quiz["id"], $quiz["title"], $quiz["questions"])
        || !is_array($quiz["questions"])) {
        jsonError(500, "Quiz storage contains invalid entries.");
    }
    $result[] = quizSummary($quiz);
}
echo json_encode($result);
