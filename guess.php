<?php
// One guess, for example guess.php?letter=e
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include "functions.php";

require_get();
require_login();

if (!isset($_SESSION["round"]) || $_SESSION["round"]["over"]) {
    send_json(400, ["error" => "There is no round running. Press Start."]);
}
$round = $_SESSION["round"];

// Check the letter.
$letter = $_GET["letter"] ?? "";
if (!is_string($letter) || strlen($letter) !== 1 || $letter < "a" || $letter > "z") {
    send_json(400, ["error" => "Please send one letter from a to z."]);
}
if (in_array($letter, $round["guessed"])) {
    send_json(400, ["error" => "You already guessed that letter."]);
}

// Use the guess.
$round["guessed"][] = $letter;
$correct = false;
for ($i = 0; $i < strlen($round["word"]); $i++) {
    if ($round["word"][$i] === $letter) {
        $round["revealed"] = $round["revealed"] + 1;   // a letter that appears twice counts twice
        $correct = true;
    }
}
if (!$correct) {
    $round["wrong"] = $round["wrong"] + 1;
}

// Did the player win or lose?
if (all_letters_found($round)) {
    $round["over"] = true;
    $round["result"] = "won";
} elseif ($round["wrong"] >= 6) {
    $round["over"] = true;
    $round["result"] = "lost";
}

$_SESSION["round"] = $round;

// If the round is over, save the result for the logged in player.
if ($round["over"]) {
    save_result($_SESSION["username"], $round["level"], round_points($round), $round["result"]);
}

$state = make_state($round);
$state["correct"] = $correct;
send_json(200, $state);
?>
