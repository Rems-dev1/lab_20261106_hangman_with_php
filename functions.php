<?php
// Helper functions used by many pages.
// Every page that uses these must call session_start() first.

// ---------- Files ----------

// Read a JSON file and give back an array.
// If the file is missing or empty, give back an empty array.
function read_json($file) {
    if (!file_exists($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return $data ?? [];
}

// Write an array into a JSON file. LOCK_EX stops two writes at the same moment.
function write_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

// ---------- Answers and checks for the game routes ----------

// Send an answer to the browser as JSON, with a status code, and stop.
function send_json($status, $data) {
    http_response_code($status);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}

function require_get() {
    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        send_json(405, ["error" => "Only GET is allowed."]);
    }
}

function require_post() {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        send_json(405, ["error" => "Only POST is allowed."]);
    }
}

function require_login() {
    if (!isset($_SESSION["username"])) {
        send_json(401, ["error" => "Please log in first."]);
    }
}

// ---------- The round ----------

// Make the word with underscores, like "m _ n _ o ".
function make_shown($round) {
    $shown = "";
    for ($i = 0; $i < strlen($round["word"]); $i++) {
        $letter = $round["word"][$i];
        if (in_array($letter, $round["guessed"])) {
            $shown = $shown . $letter . " ";
        } else {
            $shown = $shown . "_ ";
        }
    }
    return $shown;
}

// 1 point for each letter revealed. The hint takes off 1. Never below 0.
function round_points($round) {
    $points = $round["revealed"];
    if ($round["hintUsed"]) {
        $points = $points - 1;
    }
    if ($points < 0) {
        $points = 0;
    }
    return $points;
}

// True if every letter of the word has been guessed.
function all_letters_found($round) {
    for ($i = 0; $i < strlen($round["word"]); $i++) {
        if (!in_array($round["word"][$i], $round["guessed"])) {
            return false;
        }
    }
    return true;
}

// Build the answer for the browser. It only has what the player may see.
function make_state($round) {
    $state = [
        "shown" => make_shown($round),
        "wrong" => $round["wrong"],
        "points" => round_points($round),
        "over" => $round["over"],
        "result" => $round["result"]
    ];
    if ($round["over"]) {
        $state["word"] = $round["word"];   // safe to show now, the round is finished
    }
    return $state;
}

// Add the result of a finished round to data/scores.json.
function save_result($username, $level, $points, $result) {
    $file = __DIR__ . "/data/scores.json";
    $scores = read_json($file);
    $scores[] = [
        "username" => $username,
        "level" => $level,
        "points" => $points,
        "result" => $result
    ];
    write_json($file, $scores);
}

// ---------- Players ----------

// Find a username in the users array. Capital letters do not matter.
// Gives back the name as it is written in users.json, or null.
function find_user($users, $name) {
    foreach ($users as $key => $user) {
        if (strtolower($key) === strtolower($name)) {
            return $key;
        }
    }
    return null;
}

// The theme class for the <body> tag, for example "theme-dark".
function theme_class() {
    $theme = "light";
    if (isset($_SESSION["username"])) {
        $users = read_json(__DIR__ . "/data/users.json");
        $name = $_SESSION["username"];
        if (isset($users[$name]["theme"])) {
            $theme = $users[$name]["theme"];
        }
    }
    if ($theme !== "light" && $theme !== "dark" && $theme !== "ocean") {
        $theme = "light";
    }
    return "theme-" . $theme;
}
?>
