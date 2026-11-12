<?php
// Starts a round: picks a word from data/words.json, keeps the round in the
// session, and sends back only the word with underscores.
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include "functions.php";

require_get();
require_login();

$level = $_GET["level"] ?? "";
if ($level !== "easy" && $level !== "normal") {
    send_json(400, ["error" => "Please choose easy or normal."]);
}

// Read the word list for this level.
$words = read_json(__DIR__ . "/data/words.json");
if (!isset($words[$level]) || count($words[$level]) === 0) {
    send_json(500, ["error" => "Could not load the " . $level . " word list."]);
}

// Pick one random word and its hint.
$list = $words[$level];
$pick = $list[array_rand($list)];

// The round stays on the server, in the session.
$round = [
    "word" => strtolower($pick["word"]),
    "hint" => $pick["hint"],
    "level" => $level,
    "guessed" => [],
    "wrong" => 0,
    "revealed" => 0,
    "hintUsed" => false,
    "over" => false,
    "result" => ""
];
$_SESSION["round"] = $round;

send_json(200, make_state($round));
?>
