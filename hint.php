<?php
// Gives the hint. It takes off 1 point (one time only).
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include "functions.php";

require_get();
require_login();

if (!isset($_SESSION["round"]) || $_SESSION["round"]["over"]) {
    send_json(400, ["error" => "There is no round running. Press Start."]);
}

$_SESSION["round"]["hintUsed"] = true;

$state = make_state($_SESSION["round"]);
$state["hint"] = $_SESSION["round"]["hint"];
send_json(200, $state);
?>
